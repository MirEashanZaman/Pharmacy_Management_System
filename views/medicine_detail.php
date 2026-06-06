<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($med['name']) ?> - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="mb-2">
        <a href="#" class="btn btn-secondary" onclick="history.back(); return false;">← Back</a>
    </div>

    <div class="grid-2 align-start">
        
        <div>
            <div class="card mb-2">
                <div class="card-body text-center">
                    <?php if($med['image'] && $med['image']!=='default_medicine.png' && file_exists('uploads/medicines/'.$med['image'])): ?>
                        <img src="uploads/medicines/<?= htmlspecialchars($med['image']) ?>" class="medicine-detail-img" alt="<?= htmlspecialchars($med['name']) ?>">
                    <?php else: ?>
                        <div class="medicine-detail-placeholder">MED</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-1 mb-2 flex-wrap">
                        <span class="badge badge-info"><?= htmlspecialchars($med['category']) ?></span>
                        <?php if($med['requires_prescription']): ?><span class="rx-badge">Prescription Required</span><?php endif; ?>
                        <?php if($med['stock']>0): ?>
                            <span class="badge badge-success">In Stock</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <h1 class="medicine-detail-title"><?= htmlspecialchars($med['name']) ?></h1>
                    <p class="text-muted mb-1">Generic: <strong><?= htmlspecialchars($med['generic_name']) ?></strong></p>
                    <p class="text-muted mb-1">Brand: <strong><?= htmlspecialchars($med['brand']) ?></strong></p>
                    <p class="text-muted mb-2">Unit: <strong><?= htmlspecialchars($med['unit']) ?></strong></p>

                    <div class="medicine-detail-price">
                        ৳<?= number_format($med['price'],2) ?>
                    </div>
                    <p class="text-muted mb-2">Per <?= $med['unit'] ?></p>

                    <?php if($avgRating): ?>
                    <div class="d-flex align-center gap-1 mb-2">
                        <div class="stars">
                            <?php for($i=1;$i<=5;$i++): ?>
                                <span class="star <?= $i<=$avgRating?'filled':'' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="text-muted">(<?= number_format($avgRating,1) ?> / 5)</span>
                    </div>
                    <?php endif; ?>

                    <div class="content-box-light mb-2">
                        <p class="lh-18"><?= nl2br(htmlspecialchars($med['description'])) ?></p>
                    </div>

                    <?php if($med['seller_name']): ?>
                    <p class="text-muted mb-2">Sold by: <strong><?= htmlspecialchars($med['seller_name']) ?></strong></p>
                    <?php endif; ?>
                    <p class="text-muted mb-2">Available Stock: <strong><?= $med['stock'] ?> <?= $med['unit'] ?></strong></p>

                    <?php if($user && $user['role']==='customer' && $med['stock']>0): ?>
                    <div class="d-flex gap-1">
                        <a href="add_to_cart.php?id=<?= $med['id'] ?>" class="btn btn-primary flex-1 btn-large">
                            Add to Cart
                        </a>
                        <a href="add_to_cart.php?id=<?= $med['id'] ?>&buy=1" class="btn btn-success flex-1 btn-large">
                            Buy Now
                        </a>
                    </div>
                    <?php elseif(!$user): ?>
                    <a href="login.php" class="btn btn-primary w-100 btn-large">Login to Purchase</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mt-3">
        <div class="card-header">
            <span>Customer Reviews</span>
            <span class="text-muted fs-md"><?= $reviews->num_rows ?> review(s)</span>
        </div>
        <div class="card-body">
            <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            
            <?php if($user && $user['role']==='customer'): ?>
                <?php if($canReview && !$alreadyReviewed): ?>
                <div class="review-form-box">
                    <h3 class="fs-lg mb-2">Write Your Review</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Rating *</label>
                            <div class="stars cursor-pointer" id="starRating">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <span class="star" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)" onmouseover="hoverRating(<?= $i ?>)" onmouseout="resetHover()">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" placeholder="Share your experience with this medicine..." rows="3"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
                <?php elseif(!$canReview): ?>
                <div class="alert alert-info">You can only review medicines you have <strong>purchased and received</strong>.</div>
                <?php elseif($alreadyReviewed): ?>
                <div class="alert alert-success">You have already reviewed this medicine. Thank you!</div>
                <?php endif; ?>
            <?php elseif(!$user): ?>
            <div class="alert alert-warning"><a href="login.php">Login</a> as a customer to leave a review.</div>
            <?php endif; ?>

            
            <?php if($reviews->num_rows===0): ?>
                <div class="text-center p-30 text-muted">No reviews yet. Be the first to review!</div>
            <?php else: ?>
            <?php while($rev=$reviews->fetch_assoc()): ?>
            <div class="review-row">
                <div class="d-flex justify-between align-center mb-1">
                    <div class="d-flex align-center gap-1">
                        <div class="reviewer-avatar-placeholder">
                            <?= strtoupper(substr($rev['reviewer_name'],0,1)) ?>
                        </div>
                        <div>
                            <strong><?= htmlspecialchars($rev['reviewer_name']) ?></strong>
                            <div class="fs-sm text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                        </div>
                    </div>
                    <div class="stars">
                        <?php for($i=1;$i<=5;$i++): ?>
                            <span class="star <?= $i<=$rev['rating']?'filled':'' ?> fs-md">★</span>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php if($rev['comment']): ?>
                <p class="lh-17 text-muted"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
            <?php endif; ?>
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
