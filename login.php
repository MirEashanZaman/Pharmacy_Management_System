<?php
require_once 'session.php';
if (isLoggedIn()) {
    $u = getUser();
    if ($u['role']==='admin') header("Location: admin/dashboard.php");
    elseif ($u['role']==='salesperson') header("Location: seller/dashboard.php");
    else header("Location: index.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (empty($email)||empty($pass)) {
        $error = 'Please fill all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND is_active=1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows===1) {
            $user = $result->fetch_assoc();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="icon">💊</div>
            <h2>Pharmacy Management System</h2>
        </div>
        <?php if($error): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">📧 Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">🔒 Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
<button type="submit" class="btn btn-primary w-100" style="padding:14px; font-size:1rem; display:flex; justify-content:center; align-items:center;">Login</button>        <div class="text-center mt-2">
            <p class="text-muted">Don't have an account? <a href="register.php" style="color:var(--primary);font-weight:600;">Register here</a></p>
        </div>
        <div style="margin-top:24px; padding:16px; background:#f8f9fa; border-radius:10px;">
            <p style="font-size:0.8rem; font-weight:600; margin-bottom:8px; color:#666;">🔑 Demo Credentials:</p>
            <div style="font-size:0.78rem; color:#555; line-height:2;">
                <div>👑 <b>Admin:</b> admin@pharma.bd / 12345!</div>
                <div>🛒 <b>Customer:</b> milton@pharma.bd / 12345</div>
                <div>🏪 <b>Salesperson:</b> tanjim@pharma.bd / 12345</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

