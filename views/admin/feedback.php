<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>Customer Feedback</h1><p>All customer messages and feedback</p></div>
        <span class="badge badge-info badge-large"><?= $feedbacks->num_rows ?> Messages</span>
    </div>

    <?php if($success || isset($_GET['deleted'])): ?><div class="alert alert-success">Feedback deleted.</div><?php endif; ?>

    <?php if($feedbacks->num_rows===0): ?>
    <div class="card"><div class="card-body text-center p-60">No feedback yet.</div></div>
    <?php else: ?>
    <div class="display-flex flex-column gap-16">
    <?php while($fb=$feedbacks->fetch_assoc()): ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-between align-center mb-2">
                <div class="d-flex align-center gap-2">
                    <div class="avatar-placeholder-md">
                        <?= strtoupper(substr($fb['name'],0,1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($fb['name']) ?></div>
                        <div class="text-muted fs-sm">
                            <?= htmlspecialchars($fb['email']) ?>
                            <?php if($fb['user_id']): ?> · <span class="badge badge-success">Registered User</span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-center gap-1">
                    <span class="text-muted fs-sm"><?= date('d M Y, h:i A', strtotime($fb['created_at'])) ?></span>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="?delete=<?= $fb['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this feedback?')">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($fb['subject']): ?>
            <div class="feedback-subject"><?= htmlspecialchars($fb['subject']) ?></div>
            <?php endif; ?>
            <div class="content-box-light lh-18 text-muted">
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
