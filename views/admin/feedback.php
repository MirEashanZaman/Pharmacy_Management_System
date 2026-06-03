<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>Customer Feedback</h1><p>All customer messages and feedback</p></div>
        <span class="badge badge-info" style="font-size:0.9rem; padding:8px 16px;"><?= $feedbacks->num_rows ?> Messages</span>
    </div>

    <?php if($success || isset($_GET['deleted'])): ?><div class="alert alert-success">Feedback deleted.</div><?php endif; ?>

    <?php if($feedbacks->num_rows===0): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">No feedback yet.</div></div>
    <?php else: ?>
    <div style="display:flex; flex-direction:column; gap:16px;">
    <?php while($fb=$feedbacks->fetch_assoc()): ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-between align-center mb-2">
                <div class="d-flex align-center gap-2">
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;">
                        <?= strtoupper(substr($fb['name'],0,1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($fb['name']) ?></div>
                        <div class="text-muted" style="font-size:0.8rem;">
                            <?= htmlspecialchars($fb['email']) ?>
                            <?php if($fb['user_id']): ?> · <span class="badge badge-success">Registered User</span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-center gap-1">
                    <span class="text-muted" style="font-size:0.8rem;"><?= date('d M Y, h:i A', strtotime($fb['created_at'])) ?></span>
                    <a href="?delete=<?= $fb['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this feedback?')">Delete</a>
                </div>
            </div>
            <?php if($fb['subject']): ?>
            <div style="font-weight:600; margin-bottom:8px; color:var(--primary);"><?= htmlspecialchars($fb['subject']) ?></div>
            <?php endif; ?>
            <div style="background:#f8f9fa; border-radius:10px; padding:16px; line-height:1.8; color:#555;">
                <?= nl2br(htmlspecialchars($fb['message'])) ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
