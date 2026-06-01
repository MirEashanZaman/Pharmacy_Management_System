<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new UserModel($db);
    }

    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            if (empty($email)||empty($pass)) {
                $error = 'Please fill all fields.';
            } else {
                $user = $this->userModel->getUserByEmail($email);
                if ($user) {
                    if (password_verify($pass, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_role'] = $user['role'];
                        if ($user['role']==='admin') header("Location: admin/dashboard.php");
                        elseif ($user['role']==='salesperson') header("Location: seller/dashboard.php");
                        else header("Location: index.php");
                        exit;
                    } else {
                        $error = 'Invalid password.';
                    }
                } else {
                    $error = 'No account found with this email.';
                }
            }
        }
        
        $viewFile = __DIR__ . '/../views/login.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Login View not found.";
        }
    }

    public function register() {
        $divisions = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur','Mymensingh'];
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $division = $_POST['division'] ?? '';
            $district = trim($_POST['district'] ?? '');
            $upazila = trim($_POST['upazila'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name)||empty($email)||empty($pass)||empty($phone)) {
                $error = 'Please fill all required fields.';
            } elseif (strlen($pass)<5) {
                $error = 'Password must be at least 5 characters.';
            } else {
                if ($this->userModel->checkEmailExists($email)) {
                    $error = 'Email already registered. Please login.';
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $role = 'customer';
                    if ($this->userModel->insertUser($name, $email, $hash, $role, $phone, $address, $division, $district, $upazila)) {
                        $success = 'Account created successfully! Please login.';
                    } else {
                        $error = 'Registration failed. Try again.';
                    }
                }
            }
        }

        $viewFile = __DIR__ . '/../views/register.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Register View not found.";
        }
    }

    public function profile() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        $divisions = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur','Mymensingh'];
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
        }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
            $name = trim($_POST['name']??'');
            $phone = trim($_POST['phone']??'');
            $division = $_POST['division']??'';
            $district = trim($_POST['district']??'');
            $upazila = trim($_POST['upazila']??'');
            $address = trim($_POST['address']??'');

            if (empty($name)||empty($phone)) { 
                $error = 'Name and phone are required.'; 
            } else {
                $pic = $user['profile_pic'];
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error']===0) {
                    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext,['jpg','jpeg','png','gif'])) { 
                        $error = 'Invalid image format.'; 
                    } elseif ($_FILES['profile_pic']['size']>2097152) { 
                        $error = 'Image must be under 2MB.'; 
                    } else {
                        if (!is_dir('uploads/profiles')) mkdir('uploads/profiles', 0755, true);
                        $pic = uniqid().'.'.$ext;
                        move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/profiles/'.$pic);
                    }
                }
                if (!$error) {
                    if ($this->userModel->updateUser($user['id'], $name, $phone, $division, $district, $upazila, $address, $pic)) {
                        $success = 'Profile updated!';
                        $user = $this->userModel->getUserById($user['id']);
                    } else {
                        $error = 'Update failed.';
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['change_pass'])) {
            $old = $_POST['old_pass']??'';
            $new = $_POST['new_pass']??'';
            $conf = $_POST['confirm_pass']??'';
            if (!password_verify($old, $user['password'])) { 
                $error = 'Current password is incorrect.'; 
            } elseif (strlen($new)<5) { 
                $error = 'New password must be at least 5 characters.'; 
            } elseif ($new!==$conf) { 
                $error = 'Passwords do not match.'; 
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                if ($this->userModel->updatePassword($user['id'], $hash)) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password.';
                }
            }
        }

        $viewFile = __DIR__ . '/../views/profile.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Profile View not found.";
        }
    }

    public function manageUsersAdmin() {
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

        $success = '';
        $error = '';

        if (isset($_GET['delete'])) {
            $delId = intval($_GET['delete']);
            if ($delId === $user['id']) {
                $error = 'You cannot deactivate your own account.';
            } else {
                $target = $this->userModel->getUserById($delId);
                if (!$target) {
                    $error = 'User not found.';
                } elseif ($target['role'] === 'admin') {
                    $error = 'You cannot deactivate another admin.';
                } else {
                    if ($this->userModel->deactivateUser($delId)) {
                        $success = 'User deactivated successfully.';
                    } else {
                        $error = 'Failed to deactivate user.';
                    }
                }
            }
        }

        if (isset($_GET['restore'])) {
            $resId = intval($_GET['restore']);
            if ($this->userModel->restoreUser($resId)) {
                $success = 'User restored successfully.';
            } else {
                $error = 'Failed to restore user.';
            }
        }

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                die("CSRF Token validation failed.");
            }
            $_POST = sanitizeInput($_POST);
        }

        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_salesperson'])) {
            $name = trim($_POST['sp_name']??'');
            $email = trim($_POST['sp_email']??'');
            $pass = $_POST['sp_pass']??'';
            $phone = trim($_POST['sp_phone']??'');
            if (empty($name)||empty($email)||empty($pass)) { $error='Please fill all fields.'; }
            else {
                if ($this->userModel->checkEmailExists($email)) { $error='Email already exists.'; }
                else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $role = 'salesperson';
                    if ($this->userModel->insertUser($name, $email, $hash, $role, $phone)) {
                        $success='Salesperson added!';
                    } else {
                        $error='Failed to add salesperson.';
                    }
                }
            }
        }

        $filter = $_GET['filter'] ?? 'all';
        $where = $filter==='all' ? "1" : ($filter==='inactive' ? "is_active=0" : "role='$filter' AND is_active=1");
        $users = $this->userModel->getAllUsers($where);

        $viewFile = __DIR__ . '/../views/admin/users.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Admin Users View not found.";
        }
    }
}
