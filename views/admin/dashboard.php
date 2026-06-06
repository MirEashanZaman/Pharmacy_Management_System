<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/dashboard-charts.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Here's what's happening today.</p>
        </div>
        <div class="d-flex gap-1">
            <a href="users.php" class="btn btn-primary">Manage Users</a>
        </div>
    </div>

    
    <div class="grid-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue"></div>
            <div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Customers</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"></div>
            <div><div class="stat-value"><?= $totalSellers ?></div><div class="stat-label">Salespersons</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"></div>
            <div><div class="stat-value"><?= $totalMeds ?></div><div class="stat-label">Medicines</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"></div>
            <div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
        </div>
    </div>

    <div class="grid-2 mb-3">
        <div class="stat-card">
            <div class="stat-icon green"></div>
            <div><div class="stat-value">৳<?= number_format($totalRevenue,0) ?></div><div class="stat-label">Total Revenue</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"></div>
            <div><div class="stat-value"><?= $pendingOrders ?></div><div class="stat-label">Pending Orders</div></div>
        </div>
    </div>

    <!-- Analytics Section (Pure CSS) -->
    <?php
        $maxRev = count($weeklyRev) > 0 ? max($weeklyRev) : 1;
        $maxQty = count($topMedQtys) > 0 ? max($topMedQtys) : 1;
        $totalStatusCount = array_sum($statusCounts) ?: 1;
        $statusColorMap = ['Pending'=>'#f59e0b','Processing'=>'#3b82f6','Shipped'=>'#6366f1','Delivered'=>'#10b981','Cancelled'=>'#ef4444'];
        $barColors = ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'];
    ?>

    <div class="grid-2 mb-3 align-stretch gap-24">
        <!-- Weekly Sales (Vertical CSS Bars) -->
        <div class="card display-flex flex-column">
            <div class="card-header">📊 Weekly Sales Trend</div>
            <div class="card-body p-15 flex-1">
                <?php if(count($weeklyDays) === 0): ?>
                    <div class="text-center p-40 text-muted">No sales data for the past 7 days</div>
                <?php else: ?>
                    <div class="css-chart-bar-wrap">
                        <?php foreach($weeklyDays as $i => $day): ?>
                            <?php $pct = ($weeklyRev[$i] / $maxRev) * 100; ?>
                            <div class="css-chart-col">
                                <div class="css-chart-val">৳<?= number_format($weeklyRev[$i], 0) ?></div>
                                <div class="css-chart-bar" style="height:<?= max($pct, 8) ?>%;"></div>
                                <div class="css-chart-label"><?= $day ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Top Meds + Order Status -->
        <div class="display-flex flex-column gap-24">
            <!-- Top Selling Medicines (Horizontal Bars) -->
            <div class="card flex-1">
                <div class="card-header">💊 Top 5 Selling Medicines</div>
                <div class="card-body p-12-16">
                    <?php if(count($topMedNames) === 0): ?>
                        <div class="text-center p-20 text-muted">No sales data yet</div>
                    <?php else: ?>
                        <?php foreach($topMedNames as $i => $name): ?>
                            <?php $pct = ($topMedQtys[$i] / $maxQty) * 100; ?>
                            <div class="css-hbar-row">
                                <div class="css-hbar-name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></div>
                                <div class="css-hbar-track">
                                    <div class="css-hbar-fill" style="width:<?= max($pct, 12) ?>%;background:<?= $barColors[$i % count($barColors)] ?>;">
                                        <span><?= $topMedQtys[$i] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Status (Segmented Bar + Legend) -->
            <div class="card flex-1">
                <div class="card-header">📋 Order Status Overview</div>
                <div class="card-body p-16">
                    <?php if(count($statusLabels) === 0): ?>
                        <div class="text-center p-20 text-muted">No orders yet</div>
                    <?php else: ?>
                        <div class="css-status-track">
                            <?php foreach($statusLabels as $i => $label): ?>
                                <?php 
                                    $pct = ($statusCounts[$i] / $totalStatusCount) * 100;
                                    $color = $statusColorMap[$label] ?? '#94a3b8';
                                ?>
                                <div class="css-status-seg" style="width:<?= $pct ?>%;background:<?= $color ?>;" title="<?= $label ?>: <?= $statusCounts[$i] ?>">
                                    <?php if($pct > 8): ?><span><?= $statusCounts[$i] ?></span><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="css-status-legend">
                            <?php foreach($statusLabels as $i => $label): ?>
                                <?php $color = $statusColorMap[$label] ?? '#94a3b8'; ?>
                                <div class="css-status-legend-item">
                                    <div class="css-status-dot" style="background:<?= $color ?>;"></div>
                                    <?= $label ?> (<?= $statusCounts[$i] ?>)
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Quick Actions</div>
        <div class="card-body">
            <div class="display-flex gap-12 flex-wrap">
                <a href="users.php" class="btn btn-primary">Manage Users</a>
                <a href="medicines.php" class="btn btn-success">View Medicines</a>
                <a href="orders.php" class="btn btn-warning">Manage Orders</a>
                <a href="feedback.php" class="btn btn-info">View Feedback</a>
                <a href="../profile.php" class="btn btn-secondary">My Profile</a>
            </div>
        </div>
    </div>

    <div class="grid-2 align-start gap-24">
        
        <div class="card">
            <div class="card-header">
                <span>Recent Orders</span>
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
                    <span>New Customers</span>
                    <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php while($u=$recentUsers->fetch_assoc()): ?>
                    <div class="recent-customer-row">
                        <div class="customer-avatar-placeholder">
                            <?= strtoupper(substr($u['name'],0,1)) ?>
                        </div>
                        <div class="flex-1">
                            <div class="fw-bold fs-md"><?= htmlspecialchars($u['name']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                        <div class="text-muted fs-sm"><?= date('d M', strtotime($u['created_at'])) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Medicine Expiry Alerts Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <span>Medicine Expiry Alerts</span>
                    <div class="display-flex gap-6">
                        <span class="badge badge-danger fs-xs" title="Expired / Near Expiry (30 days)"><?= $redAlerts ?></span>
                        <span class="badge badge-warning" title="Expiring Soon (90 days)" style="font-size:0.7rem;"><?= $yellowAlerts ?></span>
                        <span class="badge badge-success" title="Safe" style="font-size:0.7rem;"><?= $greenAlerts ?></span>
                    </div>
                </div>
                <div class="card-body pt-10">
                    <?php if($expiringMeds->num_rows===0): ?>
                        <div class="text-center p-30 text-muted">All medicines are safe!</div>
                    <?php else: ?>
                        <?php while($em = $expiringMeds->fetch_assoc()): ?>
                            <?php 
                                $days = $em['days_left'];
                                $alertClass = 'success';
                                $alertText = 'Safe';
                                if ($days <= 0) {
                                    $alertClass = 'danger';
                                    $alertText = 'Expired';
                                } elseif ($days <= 30) {
                                    $alertClass = 'danger';
                                    $alertText = $days . 'd left';
                                } elseif ($days <= 90) {
                                    $alertClass = 'warning';
                                    $alertText = $days . 'd left';
                                }
                            ?>
                            <div class="expiring-med-row">
                                <div class="alert-icon-placeholder">🩹</div>
                                <div class="flex-1">
                                    <div class="fw-bold fs-md"><?= htmlspecialchars($em['name']) ?></div>
                                    <div class="text-muted fs-sm">Exp: <?= date('d M Y', strtotime($em['expiry_date'])) ?></div>
                                </div>
                                <span class="badge badge-<?= $alertClass ?> badge-sm-bold"><?= $alertText ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>Recent Feedback</span>
                    <a href="feedback.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php while($fb=$recentFeedback->fetch_assoc()): ?>
                    <div class="border-bottom-row">
                        <div class="d-flex justify-between mb-1">
                            <strong class="fs-md"><?= htmlspecialchars($fb['name']) ?></strong>
                            <span class="text-muted fs-sm"><?= date('d M', strtotime($fb['created_at'])) ?></span>
                        </div>
                        <?php if($fb['subject']): ?><div class="text-muted fs-sm mb-1"><?= htmlspecialchars($fb['subject']) ?></div><?php endif; ?>
                        <div class="text-ellipsis-muted"><?= htmlspecialchars(substr($fb['message'],0,60)) ?>...</div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
