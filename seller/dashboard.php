<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='salesperson') { header("Location: ../index.php"); exit; }

$myMeds = $conn->query("SELECT COUNT(*) as c FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1")->fetch_assoc()['c'];
$totalStock = $conn->query("SELECT SUM(stock) as s FROM medicines WHERE salesperson_id={$user['id']} AND is_active=1")->fetch_assoc()['s'] ?? 0;
$myMedIds = $conn->query("SELECT GROUP_CONCAT(id) as ids FROM medicines WHERE salesperson_id={$user['id']}")->fetch_assoc()['ids'];
$myOrders = 0;
$myRevenue = 0;
if ($myMedIds) {
    $myOrders = $conn->query("SELECT COUNT(DISTINCT order_id) as c FROM order_items WHERE medicine_id IN ($myMedIds)")->fetch_assoc()['c'];
    $myRevenue = $conn->query("SELECT SUM(oi.price*oi.quantity) as r FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE oi.medicine_id IN ($myMedIds) AND o.status!='cancelled'")->fetch_assoc()['r'] ?? 0;
}

$recentMeds = $conn->query("SELECT * FROM medicines WHERE salesperson_id={$user['id']} ORDER BY created_at DESC LIMIT 5");
$lowStock = $conn->query("SELECT * FROM medicines WHERE salesperson_id={$user['id']} AND stock<=10 AND is_active=1 ORDER BY stock ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>📊 Seller Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Manage your medicines and track orders.</p>
        </div>
        <a href="add_medicine.php" class="btn btn-success">➕ Add Medicine</a>
    </div>

    
    <div class="grid-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue">💊</div>
            <div><div class="stat-value"><?= $myMeds ?></div><div class="stat-label">My Medicines</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">📦</div>
            <div><div class="stat-value"><?= number_format($totalStock) ?></div><div class="stat-label">Total Stock</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">🛒</div>
            <div><div class="stat-value"><?= $myOrders ?></div><div class="stat-label">Orders Received</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">💰</div>
            <div><div class="stat-value">৳<?= number_format($myRevenue,0) ?></div><div class="stat-label">Revenue</div></div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header">⚡ Quick Actions</div>
        <div class="card-body">
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="add_medicine.php" class="btn btn-success">➕ Add New Medicine</a>
                <a href="my_medicines.php" class="btn btn-primary">💊 My Medicines</a>
                <a href="orders.php" class="btn btn-warning">📦 View Orders</a>
                <a href="../profile.php" class="btn btn-secondary">👤 My Profile</a>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start; gap:24px;">
        
        <div class="card">
            <div class="card-header">
                <span>💊 Recently Added Medicines</span>
                <a href="my_medicines.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Medicine</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if($recentMeds->num_rows===0): ?>
                    <tr><td colspan="4" class="text-center" style="padding:30px;">No medicines added yet</td></tr>
                    <?php else: ?>
                    <?php while($m=$recentMeds->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($m['name']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($m['category']) ?></small>
                        </td>
                        <td class="fw-bold text-primary">৳<?= number_format($m['price'],2) ?></td>
                        <td><?= $m['stock'] ?></td>
                        <td>
                            <?php if($m['is_active']): ?><span class="badge badge-success">Active</span>
                            <?php else: ?><span class="badge badge-danger">Hidden</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <span>⚠️ Low Stock Alert</span>
                <span class="badge badge-danger"><?= $lowStock->num_rows ?></span>
            </div>
            <div class="card-body">
                <?php if($lowStock->num_rows===0): ?>
                <div class="text-center" style="padding:30px; color:#888;">✅ All medicines have sufficient stock!</div>
                <?php else: ?>
                <?php while($m=$lowStock->fetch_assoc()): ?>
                <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:1.5rem;">💊</div>
                    <div style="flex:1;">
                        <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($m['name']) ?></div>
                        <div style="color:var(--danger); font-weight:600; font-size:0.85rem;">⚠️ Only <?= $m['stock'] ?> <?= $m['unit'] ?> left!</div>
                    </div>
                    <a href="edit_medicine.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Update</a>
                </div>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>





