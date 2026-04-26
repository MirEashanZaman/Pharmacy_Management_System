<?php
require_once '../session.php';
requireLogin('../login.php');
$user = getUser();
if ($user['role']!=='admin') { header("Location: ../index.php"); exit; }

$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$totalSellers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='salesperson'")->fetch_assoc()['c'];
$totalMeds = $conn->query("SELECT COUNT(*) as c FROM medicines WHERE is_active=1")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as r FROM orders WHERE status!='cancelled'")->fetch_assoc()['r'] ?? 0;
$pendingOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$recentOrders = $conn->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 10");
$recentUsers = $conn->query("SELECT * FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 5");
$recentFeedback = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5");
$statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>📊 Admin Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Here's what's happening today.</p>
        </div>
        <div class="d-flex gap-1">
            <a href="users.php" class="btn btn-primary">👥 Manage Users</a>
        </div>
    </div>

    
    <div class="grid-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue">👥</div>
            <div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Customers</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">🏪</div>
            <div><div class="stat-value"><?= $totalSellers ?></div><div class="stat-label">Salespersons</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">💊</div>
            <div><div class="stat-value"><?= $totalMeds ?></div><div class="stat-label">Medicines</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">📦</div>
            <div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
        </div>
    </div>

    <div class="grid-2 mb-3">
        <div class="stat-card">
            <div class="stat-icon green">💰</div>
            <div><div class="stat-value">৳<?= number_format($totalRevenue,0) ?></div><div class="stat-label">Total Revenue</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">⏳</div>
            <div><div class="stat-value"><?= $pendingOrders ?></div><div class="stat-label">Pending Orders</div></div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header">⚡ Quick Actions</div>
        <div class="card-body">
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="users.php" class="btn btn-primary">👥 Manage Users</a>
                <a href="medicines.php" class="btn btn-success">💊 View Medicines</a>
                <a href="orders.php" class="btn btn-warning">📦 Manage Orders</a>
                <a href="feedback.php" class="btn btn-info" style="color:white;">💬 View Feedback</a>
                <a href="../profile.php" class="btn btn-secondary">👤 My Profile</a>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start; gap:24px;">
        
        <div class="card">
            <div class="card-header">
                <span>📦 Recent Orders</span>
                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php while($o=$recentOrders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $o['id'] ?></strong><br><small class="text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></small></td>
                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td class="fw-bold">৳<?= number_format($o['total_amount'],2) ?></td>
                        <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            
            <div class="card mb-2">
                <div class="card-header">
                    <span>👥 New Customers</span>
                    <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php while($u=$recentUsers->fetch_assoc()): ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f0f0f0;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">
                            <?= strtoupper(substr($u['name'],0,1)) ?>
                        </div>
                        <div style="flex:1;">
                            <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($u['name']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= date('d M', strtotime($u['created_at'])) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            
            <div class="card">
                <div class="card-header">
                    <span>💬 Recent Feedback</span>
                    <a href="feedback.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php while($fb=$recentFeedback->fetch_assoc()): ?>
                    <div style="padding:10px 0; border-bottom:1px solid #f0f0f0;">
                        <div class="d-flex justify-between mb-1">
                            <strong style="font-size:0.9rem;"><?= htmlspecialchars($fb['name']) ?></strong>
                            <span class="text-muted" style="font-size:0.75rem;"><?= date('d M', strtotime($fb['created_at'])) ?></span>
                        </div>
                        <?php if($fb['subject']): ?><div class="text-muted" style="font-size:0.8rem; margin-bottom:4px;"><?= htmlspecialchars($fb['subject']) ?></div><?php endif; ?>
                        <div style="font-size:0.85rem; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars(substr($fb['message'],0,60)) ?>...</div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>





