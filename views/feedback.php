<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback - Pharmacy Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>Customer Feedback</h1><p>We'd love to hear your thoughts!</p></div>
    </div>

    <div class="grid-2" style="align-items:start;">
        <div>
            <div class="card">
                <div class="card-header">Send Feedback</div>
                <div class="card-body">
                    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user?$user['name']:($_POST['name']??'')) ?>" placeholder="Your name" required <?= $user?'readonly':'' ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user?$user['email']:($_POST['email']??'')) ?>" placeholder="your@email.com" required <?= $user?'readonly':'' ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="What's this about?" value="<?= htmlspecialchars($_POST['subject']??'') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Tell us your experience, suggestions, or complaints..." required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="padding:14px;">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-2">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:#f8f9fa; border-radius:10px;">
                            <div style="font-weight:bold; color:var(--primary);">PHONE</div>
                            <div>
                                <div class="fw-bold">Hotline</div>
                                <div class="text-muted">16700 (24/7)</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:#f8f9fa; border-radius:10px;">
                            <div style="font-weight:bold; color:var(--primary);">EMAIL</div>
                            <div>
                                <div class="fw-bold">Email</div>
                                <div class="text-muted">info@pharmacymanagementsystem.com</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:#f8f9fa; border-radius:10px;">
                            <div style="font-weight:bold; color:var(--primary);">OFFICE</div>
                            <div>
                                <div class="fw-bold">Head Office</div>
                                <div class="text-muted">Dhaka, Bangladesh</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:#f8f9fa; border-radius:10px;">
                            <div style="font-weight:bold; color:var(--primary);">HOURS</div>
                            <div>
                                <div class="fw-bold">Working Hours</div>
                                <div class="text-muted">Sat–Thu: 9AM–10PM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Delivery Coverage</div>
                <div class="card-body">
                    <p class="text-muted mb-2">We deliver across all 8 divisions and 64 districts of Bangladesh:</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <?php foreach(['Dhaka','Chittagong','Rajshahi','Khulna','Barisal','Sylhet','Rangpur','Mymensingh'] as $div): ?>
                        <div style="padding:8px 12px; background:#e8f4fd; border-radius:8px; font-size:0.85rem; font-weight:500;"><?= $div ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="alert alert-success mt-2">
                        <strong>Free delivery</strong> on orders above ৳500!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
