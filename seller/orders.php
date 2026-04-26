<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }


$myMedIds = $conn->query("SELECT GROUP_CONCAT(id) as ids FROM medicines WHERE salesperson_id={$user['id']}")->fetch_assoc()['ids'];

if (!$myMedIds) {
    $orders = [];
    $orderCount = 0;
} else {
    $orderResult = $conn->query("SELECT DISTINCT o.*, u.name as customer_name, u.phone as customer_phone FROM orders o JOIN order_items oi ON o.id=oi.order_id JOIN users u ON o.user_id=u.id WHERE oi.medicine_id IN ($myMedIds) ORDER BY o.created_at DESC");
    $orders = [];
    while($r=$orderResult->fetch_assoc()) $orders[]=$r;
    $orderCount = count($orders);
}

$statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
$statusIcons = ['pending'=>'⏳','processing'=>'⚙️','shipped'=>'🚚','delivered'=>'✅','cancelled'=>'❌'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders - Seller - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>📦 My Orders</h1><p><?= $orderCount ?> order(s) for your medicines</p></div>
    </div>

    <?php if(empty($orders)): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:4rem; margin-bottom:16px;">📦</div>
        <h3>No orders yet</h3>
        <p class="text-muted">Orders for your medicines will appear here</p>
    </div></div>
    <?php else: ?>
    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Amount</th><th>Delivery Location</th><th>Payment</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach($orders as $o): ?>
                <?php
                
                $items = $conn->query("SELECT oi.*, m.name FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id={$o['id']} AND oi.medicine_id IN ($myMedIds)");
                ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($o['customer_name']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($o['delivery_phone']) ?></small>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php while($it=$items->fetch_assoc()): ?>
                            <div>💊 <?= htmlspecialchars($it['name']) ?> ×<?= $it['quantity'] ?></div>
                        <?php endwhile; ?>
                    </td>
                    <td class="fw-bold text-primary">৳<?= number_format($o['total_amount'],2) ?></td>
                    <td style="font-size:0.85rem;">
                        <?= htmlspecialchars($o['delivery_division']) ?><br>
                        <?= htmlspecialchars($o['delivery_district']) ?> › <?= htmlspecialchars($o['delivery_upazila']) ?>
                    </td>
                    <td><span class="badge badge-secondary"><?= ucfirst($o['payment_method']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= $statusIcons[$o['status']] ?> <?= ucfirst($o['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>





