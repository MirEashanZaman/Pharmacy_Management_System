<?php
require_once 'session.php';
if (isLoggedIn()) { header("Location: index.php"); exit; }

$divisions = ['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur','Mymensingh'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
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
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows>0) {
            $error = 'Email already registered. Please login.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,phone,address,division,district,upazila) VALUES (?,?,'customer',?,?,?,?,?)");
            
            $stmt2 = $conn->prepare("INSERT INTO users (name,email,password,role,phone,address,division,district,upazila) VALUES (?,?,?,?,?,?,?,?,?)");
            $role = 'customer';
            $stmt2->bind_param("sssssssss", $name,$email,$hash,$role,$phone,$address,$division,$district,$upazila);
            if ($stmt2->execute()) {
                $success = 'Account created successfully! Please login.';
            } else {
                $error = 'Registration failed. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Pharmacy Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box" style="max-width:520px;">
        <div class="login-logo">
            <div class="icon">📝</div>
            <h2>Create Account</h2>
            <p>Join Pharmacy Management System - Bangladesh's Trusted Pharmacy</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?> <a href="login.php">Login Now</a></div><?php endif; ?>
        <?php if(!$success): ?>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">👤 Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">📱 Phone *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="01XXXXXXXXX" required value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">📧 Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">🔒 Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 5 characters" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">🗺️ Division</label>
                    <select name="division" class="form-control">
                        <option value="">Select Division</option>
                        <?php foreach($divisions as $d): ?>
                            <option value="<?= $d ?>" <?= ($_POST['division']??'')===$d?'selected':'' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">🏙️ District</label>
                    <input type="text" name="district" class="form-control" placeholder="e.g., Dhaka" value="<?= htmlspecialchars($_POST['district']??'') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">🏘️ Upazila / Thana</label>
                    <input type="text" name="upazila" class="form-control" placeholder="e.g., Mirpur" value="<?= htmlspecialchars($_POST['upazila']??'') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">📍 Full Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="House no, road, area..."><?= htmlspecialchars($_POST['address']??'') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="padding:14px; font-size:1rem;">
                📝 Create Account
            </button>
        </form>
        <?php endif; ?>
        <div class="text-center mt-2">
            <p class="text-muted">Already have an account? <a href="login.php" style="color:var(--primary);font-weight:600;">Login here</a></p>
        </div>
    </div>
</div>
</body>
</html>

