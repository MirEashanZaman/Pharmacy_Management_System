<?php
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class OrderController {
    private $db;
    private $orderModel;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->orderModel = new OrderModel($db);
        $this->userModel = new UserModel($db);
    }

    public function viewCart() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='customer') { header("Location: index.php"); exit; }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_qty'])) {
            $cartId = intval($_POST['cart_id']);
            $qty = intval($_POST['qty']);
            $this->orderModel->updateCartQty($cartId, $user['id'], $qty);
            header("Location: cart.php");
            exit;
        }

        if (isset($_GET['remove'])) {
            $cartId = intval($_GET['remove']);
            $this->orderModel->removeCartItem($cartId, $user['id']);
            header("Location: cart.php");
            exit;
        }

        $items = $this->orderModel->getCartItems($user['id']);
        $total = 0;
        $itemsList = [];
        while($row=$items->fetch_assoc()) { 
            $itemsList[]=$row; 
            $total+=$row['price']*$row['quantity']; 
        }

        $needsPrescription = false;
        foreach ($itemsList as $item) {
            if (isset($item['requires_prescription']) && $item['requires_prescription'] == 1) {
                $needsPrescription = true;
                break;
            }
        }

        $divisions = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur','Mymensingh'];
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['place_order'])) {
            $delAddress = trim($_POST['del_address']??'');
            $delDiv = $_POST['del_division']??'';
            $delDist = trim($_POST['del_district']??'');
            $delUpaz = trim($_POST['del_upazila']??'');
            $delPhone = trim($_POST['del_phone']??'');
            $payMethod = $_POST['payment_method']??'';

            if (empty($delAddress)||empty($delDiv)||empty($delDist)||empty($delPhone)) {
                $error = 'Please fill all delivery details.';
            } elseif (empty($payMethod)) {
                $error = 'Please select a payment method.';
            } elseif (empty($itemsList)) {
                $error = 'Your cart is empty.';
            } else {
                $prescriptionImg = null;
                if ($needsPrescription) {
                    if (!isset($_FILES['prescription']) || $_FILES['prescription']['error'] !== 0) {
                        $error = 'A prescription upload is required for prescription-only medicines.';
                    } else {
                        $ext = strtolower(pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                            $error = 'Invalid prescription format. Use JPG, PNG, WEBP or PDF.';
                        } elseif ($_FILES['prescription']['size'] > 5242880) {
                            $error = 'Prescription file must be under 5MB.';
                        } else {
                            if (!is_dir('uploads/prescriptions')) mkdir('uploads/prescriptions', 0755, true);
                            $prescriptionImg = uniqid('rx_') . '.' . $ext;
                            if (!move_uploaded_file($_FILES['prescription']['tmp_name'], 'uploads/prescriptions/' . $prescriptionImg)) {
                                $error = 'Failed to upload prescription image.';
                            }
                        }
                    }
                }

                if (!$error) {
                    $deliveryCharge = $total >= 500 ? 0 : 60;
                    $grandTotal = $total + $deliveryCharge;

                    if ($payMethod === 'cod') {
                        $this->db->begin_transaction();
                        try {
                            $orderId = $this->orderModel->insertOrder($user['id'], $grandTotal, $delAddress, $delDiv, $delDist, $delUpaz, $delPhone, $payMethod, $prescriptionImg, 0);
                            if ($orderId) {
                                foreach($itemsList as $item) {
                                    $this->orderModel->insertOrderItem($orderId, $item['medicine_id'], $item['quantity'], $item['price']);
                                    $this->orderModel->deductMedicineStock($item['medicine_id'], $item['quantity']);
                                }
                                $this->orderModel->clearCart($user['id']);
                                $this->db->commit();
                                $success = "Order #$orderId placed successfully!";
                                $itemsList = [];
                                $total = 0;
                                $needsPrescription = false;
                            } else {
                                throw new Exception("Insert order failed");
                            }
                        } catch(Exception $e) {
                            $this->db->rollback();
                            $error = 'Order failed. Please try again.';
                        }
                    } else {
                        // Store checkout data in session and redirect to payment.php
                        $_SESSION['checkout_data'] = [
                            'total' => $grandTotal,
                            'del_address' => $delAddress,
                            'del_division' => $delDiv,
                            'del_district' => $delDist,
                            'del_upazila' => $delUpaz,
                            'del_phone' => $delPhone,
                            'payment_method' => $payMethod,
                            'prescription_img' => $prescriptionImg
                        ];
                        header("Location: payment.php");
                        exit;
                    }
                }
            }
        }

        $viewFile = __DIR__ . '/../views/cart.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Cart View not found.";
        }
    }

    public function listOrdersCustomer() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='customer') { header("Location: index.php"); exit; }

        $orders = $this->orderModel->getUserOrders($user['id']);
        $statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];

        $viewFile = __DIR__ . '/../views/orders.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Orders View not found.";
        }
    }

    public function manageOrdersAdmin() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
        }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['approve_rx'])) {
            $oid = intval($_POST['order_id']);
            $stmt = $this->db->prepare("UPDATE orders SET rx_approved = 1 WHERE id = ?");
            $stmt->bind_param("i", $oid);
            if ($stmt->execute()) {
                $success = "Prescription for Order #$oid approved successfully.";
            }
        }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['reject_rx'])) {
            $oid = intval($_POST['order_id']);
            $stmt = $this->db->prepare("UPDATE orders SET rx_approved = -1 WHERE id = ?");
            $stmt->bind_param("i", $oid);
            if ($stmt->execute()) {
                $success = "Prescription for Order #$oid rejected.";
            }
        }

        $filter = $_GET['filter'] ?? 'all';
        $where = $filter==='all' ? "1" : "o.status='$filter'";
        $orders = $this->orderModel->getOrdersAdmin($where);

        $statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];

        $viewFile = __DIR__ . '/../views/admin/orders.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Admin Orders View not found.";
        }
    }

    public function manageOrdersSeller() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

        $success = '';
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
            
            if (isset($_POST['approve_rx'])) {
                $oid = intval($_POST['order_id']);
                $stmt = $this->db->prepare("UPDATE orders SET rx_approved = 1 WHERE id = ?");
                $stmt->bind_param("i", $oid);
                if ($stmt->execute()) {
                    $success = "Prescription for Order #$oid approved successfully.";
                }
            }
            if (isset($_POST['reject_rx'])) {
                $oid = intval($_POST['order_id']);
                $stmt = $this->db->prepare("UPDATE orders SET rx_approved = -1 WHERE id = ?");
                $stmt->bind_param("i", $oid);
                if ($stmt->execute()) {
                    $success = "Prescription for Order #$oid rejected.";
                }
            }
            if (isset($_POST['update_status'])) {
                $oid = intval($_POST['order_id']);
                $status = $_POST['status'];
                $allowed = ['pending','processing','shipped','delivered','cancelled'];
                if (in_array($status, $allowed)) {
                    if ($this->orderModel->updateOrderStatus($oid, $status)) {
                        $success = "Order #$oid status updated to $status.";
                    }
                }
            }
        }

        $myMedIdsResult = $this->db->query("SELECT GROUP_CONCAT(id) as ids FROM medicines WHERE salesperson_id={$user['id']}");
        $myMedIds = $myMedIdsResult->fetch_assoc()['ids'];

        if (!$myMedIds) {
            $orders = [];
            $orderCount = 0;
        } else {
            $orderResult = $this->db->query("SELECT DISTINCT o.*, u.name as customer_name, u.phone as customer_phone FROM orders o JOIN order_items oi ON o.id=oi.order_id JOIN users u ON o.user_id=u.id WHERE oi.medicine_id IN ($myMedIds) ORDER BY o.created_at DESC");
            $orders = [];
            while($r=$orderResult->fetch_assoc()) $orders[]=$r;
            $orderCount = count($orders);
        }

        $statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];

        $viewFile = __DIR__ . '/../views/seller/orders.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Seller Orders View not found.";
        }
    }
}
