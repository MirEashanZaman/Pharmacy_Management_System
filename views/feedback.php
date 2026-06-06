<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
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
                <div class="card-header">Rate & Review Delivered Products</div>
                <div class="card-body">
                    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    
                    <?php if ($editReview): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            <input type="hidden" name="review_id" value="<?= $editReview['id'] ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Editing Review for <strong><?= htmlspecialchars($editReview['medicine_name']) ?></strong></label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Star Rating *</label>
                                <div class="stars" id="starRating">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <span class="star" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)" onmouseover="hoverRating(<?= $i ?>)" onmouseout="resetHover()">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="<?= $editReview['rating'] ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Your Comment / Review *</label>
                                <textarea name="comment" class="form-control" rows="4" required><?= htmlspecialchars($editReview['comment']) ?></textarea>
                            </div>

                            <div class="display-flex gap-6">
                                <button type="submit" name="update_review" class="btn btn-primary flex-1 btn-large">Update Feedback</button>
                                <a href="feedback.php" class="btn btn-secondary btn-large">Cancel</a>
                            </div>
                        </form>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                setRating(<?= $editReview['rating'] ?>);
                            });
                        </script>
                    <?php elseif(empty($deliveredMedicines)): ?>
                        <div class="text-center p-40">
                            <div style="font-size: 3rem; margin-bottom: 12px;">📦</div>
                            <h3>No medicines to review</h3>
                            <p class="text-muted mt-1">You can only give feedback and reviews on products from orders that have been successfully <strong>delivered</strong> and haven't been reviewed yet.</p>
                            <a href="medicines.php" class="btn btn-primary mt-2">Shop Medicines</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Select Delivered Medicine *</label>
                                <select name="medicine_id" class="form-control" required style="height: 48px; font-weight: 500;">
                                    <option value="">-- Choose Medicine --</option>
                                    <?php foreach ($deliveredMedicines as $med): ?>
                                        <option value="<?= $med['id'] ?>"><?= htmlspecialchars($med['name']) ?> (<?= htmlspecialchars($med['brand']) ?>) — Order #<?= $med['order_id'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Star Rating *</label>
                                <div class="stars" id="starRating">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <span class="star" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)" onmouseover="hoverRating(<?= $i ?>)" onmouseout="resetHover()">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Your Comment / Review *</label>
                                <textarea name="comment" class="form-control" rows="4" placeholder="How was the product? Share your experience with us..." required></textarea>
                            </div>

                            <button type="submit" name="submit_review" class="btn btn-primary w-100 btn-large">Submit Feedback</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($userReviews)): ?>
                <div class="card mt-3">
                    <div class="card-header">My Past Reviews & Feedback</div>
                    <div class="card-body">
                        <div class="display-flex flex-column gap-12">
                            <?php foreach ($userReviews as $rev): ?>
                                <div class="border-bottom-row">
                                    <div class="display-flex justify-between align-center mb-1">
                                        <div>
                                            <strong><?= htmlspecialchars($rev['medicine_name']) ?></strong>
                                            <span class="text-muted fs-xs" style="margin-left: 8px;"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                                        </div>
                                        <div class="display-flex gap-6">
                                            <a href="?edit_review=<?= $rev['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this review?')" class="m-0">
                                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                                <button type="submit" name="delete_review" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="text-primary fw-600 mb-1 fs-md">
                                        <?php for($i=1;$i<=5;$i++): ?>
                                            <?= $i <= $rev['rating'] ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-muted fs-md m-0"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="card mb-2">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div class="content-box-light" style="display:flex; align-items:center; gap:16px; padding:16px;">
                            <div style="font-weight:bold; color:var(--primary);">PHONE</div>
                            <div>
                                <div class="fw-bold">Hotline</div>
                                <div class="text-muted">16700 (24/7)</div>
                            </div>
                        </div>
                        <div class="content-box-light" style="display:flex; align-items:center; gap:16px; padding:16px;">
                            <div style="font-weight:bold; color:var(--primary);">EMAIL</div>
                            <div>
                                <div class="fw-bold">Email</div>
                                <div class="text-muted">info@pharmacymanagementsystem.com</div>
                            </div>
                        </div>
                        <div class="content-box-light" style="display:flex; align-items:center; gap:16px; padding:16px;">
                            <div style="font-weight:bold; color:var(--primary);">OFFICE</div>
                            <div>
                                <div class="fw-bold">Head Office</div>
                                <div class="text-muted">Dhaka, Bangladesh</div>
                            </div>
                        </div>
                        <div class="content-box-light" style="display:flex; align-items:center; gap:16px; padding:16px;">
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
                        <div class="division-badge"><?= $div ?></div>
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
<script>
let selectedRating = 0;
function setRating(val) {
    selectedRating = val;
    document.getElementById('ratingInput').value = val;
    updateStars(val, false);
}
function hoverRating(val) { updateStars(val, true); }
function resetHover() { updateStars(selectedRating, false); }
function updateStars(val, isHover) {
    document.querySelectorAll('.star[data-val]').forEach(s => {
        s.classList.toggle('filled', parseInt(s.dataset.val) <= val);
        s.classList.toggle('hover', isHover && parseInt(s.dataset.val) <= val);
    });
}
</script>
</body>
</html>
