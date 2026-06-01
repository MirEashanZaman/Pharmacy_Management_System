<?php
require_once __DIR__ . '/../models/MedicineModel.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class MedicineController {
    private $db;
    private $medicineModel;
    private $feedbackModel;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->medicineModel = new MedicineModel($db);
        $this->feedbackModel = new FeedbackModel($db);
        $this->userModel = new UserModel($db);
    }

    public function listMedicines() {
        $user = isset($_SESSION['user_id']) ? $this->userModel->getUserById($_SESSION['user_id']) : null;

        $search = trim($_GET['search'] ?? '');
        $cat = trim($_GET['cat'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page-1)*$perPage;

        $where = "m.is_active=1";
        $params = [];
        $types = "";
        if ($search) { 
            $where .= " AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand LIKE ?)"; 
            $s="%$search%"; $params[]=$s; $params[]=$s; $params[]=$s; $types.="sss"; 
        }
        if ($cat) { 
            $where .= " AND m.category=?"; 
            $params[]=$cat; $types.="s"; 
        }

        $total = $this->medicineModel->getMedicinesCount($where, $types, $params);
        $totalPages = ceil($total / $perPage);
        $meds = $this->medicineModel->getMedicinesPaged($where, $perPage, $offset, $types, $params);
        $cats = $this->db->query("SELECT DISTINCT category FROM medicines WHERE is_active=1 ORDER BY category");

        if (isset($_GET['ajax'])) {
            $partialFile = __DIR__ . '/../views/medicines_partial.php';
            if (file_exists($partialFile)) {
                require $partialFile;
            } else {
                echo "Medicines Partial not found.";
            }
            exit;
        }

        $viewFile = __DIR__ . '/../views/medicines.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Medicines View not found.";
        }
    }

    public function viewMedicineDetail() {
        $user = isset($_SESSION['user_id']) ? $this->userModel->getUserById($_SESSION['user_id']) : null;
        $id = intval($_GET['id'] ?? 0);
        if (!$id) { header("Location: medicines.php"); exit; }

        $med = $this->medicineModel->getMedicineById($id);
        if (!$med) { header("Location: medicines.php"); exit; }

        $reviews = $this->feedbackModel->getReviewsForMedicine($id);
        $avgRating = $this->feedbackModel->getAverageRating($id);

        $canReview = false;
        $alreadyReviewed = false;
        $eligibleOrderId = null;
        if ($user && $user['role']==='customer') {
            $orderId = $this->feedbackModel->hasPurchasedMedicine($user['id'], $id);
            if ($orderId) {
                $eligibleOrderId = $orderId;
                $canReview = true;
                if ($this->feedbackModel->hasReviewedMedicine($user['id'], $id)) {
                    $alreadyReviewed = true;
                }
            }
        }

        $success = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_review'])) {
            if (!$user || $user['role']!=='customer') { $error = 'Only customers can submit reviews.'; }
            elseif (!$canReview) { $error = 'You can only review medicines you have purchased and received.'; }
            elseif ($alreadyReviewed) { $error = 'You have already reviewed this medicine.'; }
            else {
                $rating = intval($_POST['rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');
                if ($rating<1||$rating>5) { $error = 'Please select a valid rating.'; }
                else {
                    if ($this->feedbackModel->insertReview($user['id'], $id, $eligibleOrderId, $rating, $comment)) {
                        $success = 'Review submitted successfully!';
                        $alreadyReviewed = true;
                        header("Refresh:2; url=medicine_detail.php?id=$id");
                    } else {
                        $error = 'Failed to submit review.';
                    }
                }
            }
        }

        $viewFile = __DIR__ . '/../views/medicine_detail.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Medicine Detail View not found.";
        }
    }

    public function manageMedicinesAdmin() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

        $success = '';
        $error = '';

        if (isset($_GET['toggle'])) {
            $mid = intval($_GET['toggle']);
            if ($this->medicineModel->toggleMedicineStatus($mid)) {
                $success = 'Medicine status updated.';
            } else {
                $error = 'Failed to update status.';
            }
        }

        $meds = $this->medicineModel->getAllMedicinesAdmin();

        $viewFile = __DIR__ . '/../views/admin/medicines.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Admin Medicines View not found.";
        }
    }

    public function manageMedicinesSeller() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $success = '';
        if (isset($_GET['delete'])) {
            $mid = intval($_GET['delete']);
            
            $stmt = $this->db->prepare("UPDATE medicines SET is_active=0 WHERE id=? AND salesperson_id=?");
            $stmt->bind_param("ii", $mid, $user['id']);
            if ($stmt->execute()) {
                $success = 'Medicine removed from listing.';
            }
        }

        $meds = $this->medicineModel->getSellerMedicines($user['id']);

        $viewFile = __DIR__ . '/../views/seller/my_medicines.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Seller My Medicines View not found.";
        }
    }

    public function addMedicine() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $categories = ['Antibiotic','Painkiller','Gastric','Cardiac','Diabetes','Antihistamine','Vitamin','Supplement','Antifungal','Antiviral','Neurological','Respiratory','Dermatology','Ophthalmic','Other'];
        $units = ['tablet','capsule','syrup','injection','cream','ointment','drops','inhaler','sachet','suppository','pcs'];
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name']??'');
            $generic = trim($_POST['generic_name']??'');
            $brand = trim($_POST['brand']??'');
            $cat = $_POST['category']??'';
            $desc = trim($_POST['description']??'');
            $price = floatval($_POST['price']??0);
            $stock = intval($_POST['stock']??0);
            $unit = $_POST['unit']??'tablet';
            $rx = isset($_POST['requires_prescription'])?1:0;

            if (empty($name)||empty($brand)||$price<=0||$stock<0) {
                $error = 'Please fill all required fields with valid values.';
            } else {
                $img = 'default_medicine.png';
                if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) { 
                        $error = 'Invalid image format. Use JPG, PNG, or GIF.'; 
                    } elseif ($_FILES['image']['size']>5242880) { 
                        $error = 'Image must be under 5MB.'; 
                    } else {
                        if (!is_dir('../uploads/medicines')) mkdir('../uploads/medicines', 0755, true);
                        $img = uniqid('med_').'.'.$ext;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/medicines/'.$img)) {
                            $error = 'Failed to upload image.';
                            $img = 'default_medicine.png';
                        }
                    }
                }

                if (!$error) {
                    if ($this->medicineModel->insertMedicine($name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $user['id'])) {
                        $success = "Medicine '$name' added successfully!";
                        header("Refresh:2; url=my_medicines.php");
                    } else {
                        $error = 'Failed to add medicine.';
                    }
                }
            }
        }

        $viewFile = __DIR__ . '/../views/seller/add_medicine.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Seller Add Medicine View not found.";
        }
    }

    public function editMedicine() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) { header("Location: my_medicines.php"); exit; }

        $stmt = $this->db->prepare("SELECT * FROM medicines WHERE id=? AND salesperson_id=?");
        $stmt->bind_param("ii", $id, $user['id']);
        $stmt->execute();
        $med = $stmt->get_result()->fetch_assoc();
        if (!$med) { header("Location: my_medicines.php"); exit; }

        $categories = ['Antibiotic','Painkiller','Gastric','Cardiac','Diabetes','Antihistamine','Vitamin','Supplement','Antifungal','Antiviral','Neurological','Respiratory','Dermatology','Ophthalmic','Other'];
        $units = ['tablet','capsule','syrup','injection','cream','ointment','drops','inhaler','sachet','suppository','pcs'];
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name']??'');
            $generic = trim($_POST['generic_name']??'');
            $brand = trim($_POST['brand']??'');
            $cat = $_POST['category']??'';
            $desc = trim($_POST['description']??'');
            $price = floatval($_POST['price']??0);
            $stock = intval($_POST['stock']??0);
            $unit = $_POST['unit']??'tablet';
            $rx = isset($_POST['requires_prescription'])?1:0;

            if (empty($name)||empty($brand)||$price<=0||$stock<0) {
                $error = 'Please fill all required fields with valid values.';
            } else {
                $img = $med['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error']===0) {
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) { 
                        $error = 'Invalid image format.'; 
                    } elseif ($_FILES['image']['size']>5242880) { 
                        $error = 'Image must be under 5MB.'; 
                    } else {
                        if (!is_dir('../uploads/medicines')) mkdir('../uploads/medicines', 0755, true);
                        $img = uniqid('med_').'.'.$ext;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/medicines/'.$img)) {
                            $error = 'Failed to upload image.'; 
                            $img = $med['image'];
                        }
                    }
                }
                if (!$error) {
                    if ($this->medicineModel->updateMedicine($id, $name, $generic, $brand, $cat, $desc, $price, $stock, $unit, $img, $rx, $user['id'])) {
                        $success = 'Medicine updated successfully!';
                        $med = array_merge($med, ['name'=>$name,'generic_name'=>$generic,'brand'=>$brand,'category'=>$cat,'description'=>$desc,'price'=>$price,'stock'=>$stock,'unit'=>$unit,'image'=>$img,'requires_prescription'=>$rx]);
                    } else { 
                        $error = 'Update failed.'; 
                    }
                }
            }
        }

        $viewFile = __DIR__ . '/../views/seller/edit_medicine.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Seller Edit Medicine View not found.";
        }
    }
}
