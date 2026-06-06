<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>My Cart</h1><p><?= count($itemsList) ?> item(s)</p></div>
        <a href="medicines.php" class="btn btn-outline">← Continue Shopping</a>
    </div>

    <?php if($success): ?>
    <div class="alert alert-success" style="font-size:1rem; padding:20px;">
        <?= $success ?> <a href="orders.php" class="btn btn-success btn-sm" style="margin-left:12px;">View Orders</a>
    </div>
    <?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if(empty($itemsList)&&!$success): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:1.5rem; font-weight:bold; color:#4f46e5; margin-bottom:16px;">CART</div>
        <h3>Your cart is empty</h3>
        <p class="text-muted">Add some medicines to get started</p>
        <a href="medicines.php" class="btn btn-primary mt-2">Shop Now</a>
    </div></div>
    <?php elseif(!empty($itemsList)): ?>
    <div class="grid-2" style="align-items:start; gap:24px;">
        
        <div class="card">
            <div class="card-header">Cart Items</div>
            <div class="card-body">
                <?php foreach($itemsList as $item): ?>
                <div class="cart-item">
                    <?php if($item['image'] && $item['image']!=='default_medicine.png' && file_exists('uploads/medicines/'.$item['image'])): ?>
                        <img src="uploads/medicines/<?= htmlspecialchars($item['image']) ?>" class="cart-item-img" style="width:60px;height:60px;border-radius:10px;object-fit:cover;" alt="">
                    <?php else: ?>
                        <div class="cart-item-img" style="font-size:0.75rem; font-weight:bold; color:#4f46e5; display:flex; align-items:center; justify-content:center; background:#f0f4ff;">MED</div>
                    <?php endif; ?>
                    <div style="flex:1;">
                        <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="text-muted" style="font-size:0.8rem;"><?= htmlspecialchars($item['brand']) ?></div>
                        <div style="color:var(--primary); font-weight:700;">৳<?= number_format($item['price'],2) ?></div>
                    </div>
                    <form method="POST" style="display:flex;align-items:center;gap:8px;">
                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="update_qty" value="1">
                        <div class="qty-control">
                            <button type="button" class="qty-btn" onclick="changeQty(this,-1)">−</button>
                            <input name="qty" type="number" class="qty-num" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>" style="width:50px; border:none; text-align:center;" onchange="this.form.submit()">
                            <button type="button" class="qty-btn" onclick="changeQty(this,1)">+</button>
                        </div>
                    </form>
                    <div style="font-weight:700; min-width:80px; text-align:right;">৳<?= number_format($item['price']*$item['quantity'],2) ?></div>
                    <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">Remove</a>
                </div>
                <?php endforeach; ?>
                <div style="border-top:2px solid #e9ecef; padding-top:16px; margin-top:8px; text-align:right;">
                    <div style="font-size:0.9rem; color:#666; margin-bottom:4px;">Subtotal: ৳<?= number_format($total,2) ?></div>
                    <?php $delivery = $total>=500?0:60; ?>
                    <div style="font-size:0.9rem; color:#666; margin-bottom:4px;">Delivery: <?= $delivery==0?'<span style="color:var(--success)">FREE</span>':'৳'.$delivery ?></div>
                    <div style="font-size:1.4rem; font-weight:800; color:var(--primary);">Total: ৳<?= number_format($total+$delivery,2) ?></div>
                    <?php if($total<500): ?><div class="text-muted" style="font-size:0.8rem;">Add ৳<?= number_format(500-$total,2) ?> more for free delivery!</div><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">Delivery Details & Checkout</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <?php if ($needsPrescription): ?>
                        <div class="form-group" style="background: #faf9fd; border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid var(--primary);">
                            <label class="form-label" style="color: var(--primary-dark); font-weight: 700;">Prescription Upload Required *</label>
                            <p style="font-size: 0.8rem; color: var(--secondary); margin-bottom: 8px;">Your cart contains prescription-only (Rx) medicines. Please upload a clear photo of your doctor's prescription.</p>
                            <input type="file" name="prescription" class="form-control" accept="image/*,application/pdf" required style="border-color: var(--secondary); background: #fff;">
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label">Delivery Phone *</label>
                        <input type="tel" name="del_phone" class="form-control" placeholder="01XXXXXXXXX" value="<?= htmlspecialchars($user['phone']??'') ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Division *</label>
                            <select name="del_division" class="form-control" required>
                                <option value="">Select Division</option>
                                <?php foreach($divisions as $d): ?>
                                    <option value="<?= $d ?>" <?= ($user['division']??'')===$d?'selected':'' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">District *</label>
                            <input type="text" name="del_district" class="form-control" placeholder="e.g., Dhaka" value="<?= htmlspecialchars($user['district']??'') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upazila / Thana</label>
                        <input type="text" name="del_upazila" class="form-control" placeholder="e.g., Mirpur" value="<?= htmlspecialchars($user['upazila']??'') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Address *</label>
                        <textarea name="del_address" class="form-control" rows="3" placeholder="House no, road, area..." required><?= htmlspecialchars($user['address']??'') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Method *</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <?php $methods=['cod'=>'Cash on Delivery','bkash'=>'bKash','nagad'=>'Nagad','rocket'=>'Rocket']; ?>
                            <?php foreach($methods as $val=>$label): ?>
                            <label style="display:flex;align-items:center;gap:8px;padding:12px;border:2px solid #e9ecef;border-radius:8px;cursor:pointer;transition:all 0.2s;position:relative;">
                                <input type="radio" name="payment_method" value="<?= $val ?>" required style="opacity:0; position:absolute; inset:0; cursor:pointer; width:100%; height:100%; pointer-events:auto; z-index:2;">
                                <span style="position:relative; z-index:1; font-weight:600;"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="background:#f8f9fa; border-radius:10px; padding:16px; margin-bottom:16px;">
                        <div class="d-flex justify-between mb-1"><span>Subtotal:</span><strong>৳<?= number_format($total,2) ?></strong></div>
                        <div class="d-flex justify-between mb-1"><span>Delivery:</span><strong style="color:<?= $delivery==0?'var(--success)':'var(--dark)' ?>"><?= $delivery==0?'FREE':'৳'.$delivery ?></strong></div>
                        <div class="d-flex justify-between" style="font-size:1.2rem; font-weight:800; border-top:2px solid #dee2e6; padding-top:12px; margin-top:8px;">
                            <span>Total:</span><span style="color:var(--primary);">৳<?= number_format($total+$delivery,2) ?></span>
                        </div>
                    </div>
                    <button type="submit" name="place_order" class="btn btn-success w-100" style="padding:14px; font-size:1rem;">
                        Place Order — ৳<?= number_format($total+$delivery,2) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('input[name="qty"]');
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    if (val < 0) val = 0;
    if (val > max) val = max;
    input.value = val;
    btn.closest('form').submit();
}
document.querySelectorAll('input[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('input[name="payment_method"]').forEach(rb => {
            rb.closest('label').style.borderColor = '#e9ecef';
            rb.closest('label').style.background = '';
        });
        r.closest('label').style.borderColor = 'var(--primary)';
        r.closest('label').style.background = '#f0f4ff';
    });
});
</script>
</body>
</html>
