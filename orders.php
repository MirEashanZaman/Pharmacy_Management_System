<?php
require_once 'session.php';
requireLogin('login.php');
$user = getUser();
if ($user['role']!=='customer') { header("Location: index.php"); exit; }

$orders = $conn->query("SELECT * FROM orders WHERE user_id={$user['id']} ORDER BY created_at DESC");
$statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
$statusIcons = ['pending'=>'⏳','processing'=>'⚙️','shipped'=>'🚚','delivered'=>'✅','cancelled'=>'❌'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - Pharmacy Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>📦 My Orders</h1><p>Track your medicine orders</p></div>
        <a href="medicines.php" class="btn btn-primary">💊 Shop More</a>
    </div>

    <?php if($orders->num_rows===0): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:4rem; margin-bottom:16px;">📦</div>
        <h3>No orders yet</h3>
        <p class="text-muted">Start shopping to place your first order</p>
        <a href="medicines.php" class="btn btn-primary mt-2">💊 Shop Now</a>
    </div></div>
    <?php else: ?>
    <?php while($order=$orders->fetch_assoc()): ?>
    <?php $items = $conn->query("SELECT oi.*, m.name, m.image FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id={$order['id']}"); ?>
    <div class="card mb-2">
        <div class="card-header">
            <div class="d-flex align-center gap-2">
                <span>Order #<?= $order['id'] ?></span>
                <span class="badge badge-<?= $statusColors[$order['status']] ?>"><?= $statusIcons[$order['status']] ?> <?= ucfirst($order['status']) ?></span>
            </div>
            <span class="text-muted" style="font-size:0.85rem;"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="card-body">
            <div class="grid-2" style="gap:20px;">
                <div>
                    <h4 style="font-size:0.9rem; margin-bottom:12px; color:#666;">📦 ITEMS</h4>
                    <?php while($item=$items->fetch_assoc()): ?>
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                        <div style="width:40px;height:40px;border-radius:8px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                            <?php if($item['image']&&$item['image']!=='default_medicine.png'&&file_exists('uploads/medicines/'.$item['image'])): ?>
                                <img src="uploads/medicines/<?= htmlspecialchars($item['image']) ?>" style="width:100%;height:100%;border-radius:8px;object-fit:cover;" alt="">
                            <?php else: ?>💊<?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-muted">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'],2) ?></div>
                        </div>
                        <div class="fw-bold">৳<?= number_format($item['quantity']*$item['price'],2) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div>
                    <h4 style="font-size:0.9rem; margin-bottom:12px; color:#666;">📍 DELIVERY INFO</h4>
                    <div style="background:#f8f9fa; border-radius:10px; padding:14px; font-size:0.85rem; line-height:1.9;">
                        <div>📱 <?= htmlspecialchars($order['delivery_phone']) ?></div>
                        <div>🗺️ <?= htmlspecialchars($order['delivery_division']) ?> › <?= htmlspecialchars($order['delivery_district']) ?> › <?= htmlspecialchars($order['delivery_upazila']) ?></div>
                        <div>📍 <?= htmlspecialchars($order['delivery_address']) ?></div>
                        <div>💳 <?= ucfirst($order['payment_method']) ?></div>
                    </div>
                    <div style="text-align:right; margin-top:12px; font-size:1.2rem; font-weight:800; color:var(--primary);">
                        Total: ৳<?= number_format($order['total_amount'],2) ?>
                    </div>
                    <?php if($order['status']==='delivered'): ?>
                    <a href="medicines.php" class="btn btn-success btn-sm mt-1" style="width:100%; justify-content:center;">⭐ Review Items</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>


