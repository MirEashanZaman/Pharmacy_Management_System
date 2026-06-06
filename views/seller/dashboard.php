<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Dashboard - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/dashboard-charts.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>Seller Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Manage your medicines and track orders.</p>
        </div>
        <a href="add_medicine.php" class="btn btn-success">Add Medicine</a>
    </div>

    
    <div class="grid-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue"></div>
            <div><div class="stat-value"><?= $myMeds ?></div><div class="stat-label">My Medicines</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"></div>
            <div><div class="stat-value"><?= number_format($totalStock) ?></div><div class="stat-label">Total Stock</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"></div>
            <div><div class="stat-value"><?= $myOrders ?></div><div class="stat-label">Orders Received</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"></div>
            <div><div class="stat-value">৳<?= number_format($myRevenue,0) ?></div><div class="stat-label">Revenue</div></div>
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
            <div class="card-header">📊 Weekly Sales Trend (My Products)</div>
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
                <div class="card-header">💊 My Top Selling Medicines</div>
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
                <div class="card-header">📋 My Order Status Overview</div>
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
                <a href="add_medicine.php" class="btn btn-success">Add New Medicine</a>
                <a href="my_medicines.php" class="btn btn-primary">My Medicines</a>
                <a href="orders.php" class="btn btn-warning">View Orders</a>
                <a href="../profile.php" class="btn btn-secondary">My Profile</a>
            </div>
        </div>
    </div>

    <div class="grid-2 align-start gap-24">
        
        <div class="card">
            <div class="card-header">
                <span>Recently Added Medicines</span>
                <a href="my_medicines.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Medicine</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if($recentMeds->num_rows===0): ?>
                    <tr><td colspan="4" class="text-center p-30">No medicines added yet</td></tr>
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

        
        <div>
            <div class="card mb-3">
                <div class="card-header">
                    <span>Low Stock Alert</span>
                    <span class="badge badge-danger"><?= $lowStock->num_rows ?></span>
                </div>
                <div class="card-body">
                    <?php if($lowStock->num_rows===0): ?>
                    <div class="text-center p-30 text-muted">All medicines have sufficient stock!</div>
                    <?php else: ?>
                    <?php while($m=$lowStock->fetch_assoc()): ?>
                    <div class="expiring-med-row">
                        <div class="med-placeholder-xs">MED</div>
                        <div class="flex-1">
                            <div class="fw-bold fs-9"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="text-danger fw-600 fs-md">Only <?= $m['stock'] ?> <?= $m['unit'] ?> left!</div>
                        </div>
                        <a href="edit_medicine.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Update</a>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medicine Expiry Alerts Card -->
            <div class="card">
                <div class="card-header">
                    <span>Medicine Expiry Alerts</span>
                    <div class="display-flex gap-6">
                        <span class="badge badge-danger fs-xs" title="Expired / Near Expiry (30 days)"><?= $redAlerts ?></span>
                        <span class="badge badge-warning fs-xs" title="Expiring Soon (90 days)"><?= $yellowAlerts ?></span>
                        <span class="badge badge-success fs-xs" title="Safe"><?= $greenAlerts ?></span>
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
        </div>
    </div>
</div>
</body>
</html>
