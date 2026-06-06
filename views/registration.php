<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box login-box-wide">
        <div class="login-logo">
            <div class="icon"></div>
            <h2>Create Account</h2>
            <p>Join Pharmacy Management System - Bangladesh's Trusted Pharmacy</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?= $success ?> <a href="login.php">Login Now</a></div><?php endif; ?>
        <?php if(!$success): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input type="tel" name="phone" class="form-control" placeholder="01XXXXXXXXX" required value="<?= htmlspecialchars($_POST['phone']??'') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 5 characters" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Division</label>
                    <select name="division" class="form-control">
                        <option value="">Select Division</option>
                        <?php foreach($divisions as $d): ?>
                            <option value="<?= $d ?>" <?= ($_POST['division']??'')===$d?'selected':'' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">District</label>
                    <input type="text" name="district" class="form-control" placeholder="e.g., Dhaka" value="<?= htmlspecialchars($_POST['district']??'') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Upazila / Thana</label>
                    <input type="text" name="upazila" class="form-control" placeholder="e.g., Mirpur" value="<?= htmlspecialchars($_POST['upazila']??'') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Full Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="House no, road, area..."><?= htmlspecialchars($_POST['address']??'') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-large">
                Create Account
            </button>
        </form>
        <?php endif; ?>
        <div class="text-center mt-2">
            <p class="text-muted">Already have an account? <a href="login.php" class="link-primary">Login here</a></p>
        </div>
    </div>
</div>
</body>
</html>
