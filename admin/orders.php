<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

$success = '';


if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])) {
    $oid = intval($_POST['order_id']);
    $status = $_POST['status'];
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE orders SET status='$status' WHERE id=$oid");
        $success = "Order #$oid status updated to $status.";
    }
}

$filter = $_GET['filter'] ?? 'all';
$where = $filter==='all' ? "1" : "o.status='$filter'";
$orders = $conn->query("SELECT o.*, u.name as customer_name, u.phone as customer_phone FROM orders o JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC");

$statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
$statusIcons = ['pending'=>'⏳','processing'=>'⚙️','shipped'=>'🚚','delivered'=>'✅','cancelled'=>'❌'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>📦 All Orders</h1><p>Manage and track customer orders</p></div>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

    <div class="tabs mb-2">
        <a href="?filter=all" class="tab <?= $filter==='all'?'active':'' ?>">All</a>
        <a href="?filter=pending" class="tab <?= $filter==='pending'?'active':'' ?>">⏳ Pending</a>
        <a href="?filter=processing" class="tab <?= $filter==='processing'?'active':'' ?>">⚙️ Processing</a>
        <a href="?filter=shipped" class="tab <?= $filter==='shipped'?'active':'' ?>">🚚 Shipped</a>
        <a href="?filter=delivered" class="tab <?= $filter==='delivered'?'active':'' ?>">✅ Delivered</a>
        <a href="?filter=cancelled" class="tab <?= $filter==='cancelled'?'active':'' ?>">❌ Cancelled</a>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>#</th><th>Customer</th><th>Amount</th><th>Delivery Location</th><th>Payment</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if($orders->num_rows===0): ?>
                <tr><td colspan="8" class="text-center" style="padding:40px;">No orders found</td></tr>
                <?php else: ?>
                <?php while($o=$orders->fetch_assoc()): ?>
                <?php $items=$conn->query("SELECT oi.quantity, m.name FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id={$o['id']}"); ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($o['customer_name']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($o['customer_phone']) ?></small>
                        <br><small class="text-muted"><?= htmlspecialchars($o['delivery_phone']) ?></small>
                    </td>
                    <td>
                        <strong class="text-primary">৳<?= number_format($o['total_amount'],2) ?></strong>
                        <br><small class="text-muted">
                        <?php while($it=$items->fetch_assoc()): ?>
                            <?= htmlspecialchars(substr($it['name'],0,15)) ?> (<?= $it['quantity'] ?>)<br>
                        <?php endwhile; ?>
                        </small>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?= htmlspecialchars($o['delivery_division']) ?><br>
                        <?= htmlspecialchars($o['delivery_district']) ?> › <?= htmlspecialchars($o['delivery_upazila']) ?>
                    </td>
                    <td><span class="badge badge-secondary"><?= ucfirst($o['payment_method']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= $statusIcons[$o['status']] ?> <?= ucfirst($o['status']) ?></span></td>
                    <td>
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="form-control" style="padding:6px 10px; font-size:0.8rem; width:auto;">
                                <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">✓</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>





