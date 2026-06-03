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
    <style>
        .css-chart-bar-wrap{display:flex;align-items:flex-end;gap:10px;padding:16px 0 0 0;min-height:180px;}
        .css-chart-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;}
        .css-chart-bar{width:100%;min-width:28px;border-radius:6px 6px 0 0;transition:height 0.5s ease;position:relative;background:linear-gradient(180deg,#6366f1 0%,#818cf8 100%);}
        .css-chart-bar:hover{filter:brightness(1.15);transform:scaleX(1.08);cursor:default;}
        .css-chart-val{font-size:0.72rem;font-weight:700;color:var(--primary-dark);white-space:nowrap;}
        .css-chart-label{font-size:0.7rem;color:#666;font-weight:600;text-align:center;}
        .css-hbar-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #f3f4f6;}
        .css-hbar-row:last-child{border-bottom:none;}
        .css-hbar-name{width:120px;font-size:0.82rem;font-weight:600;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex-shrink:0;}
        .css-hbar-track{flex:1;height:22px;background:#f3f4f6;border-radius:6px;overflow:hidden;position:relative;}
        .css-hbar-fill{height:100%;border-radius:6px;transition:width 0.6s ease;display:flex;align-items:center;justify-content:flex-end;padding-right:8px;}
        .css-hbar-fill span{font-size:0.68rem;font-weight:700;color:white;text-shadow:0 1px 2px rgba(0,0,0,0.2);}
        .css-status-track{display:flex;height:28px;border-radius:8px;overflow:hidden;margin-bottom:16px;box-shadow:0 1px 4px rgba(0,0,0,0.06);}
        .css-status-seg{transition:width 0.5s ease;position:relative;display:flex;align-items:center;justify-content:center;}
        .css-status-seg:hover{filter:brightness(1.1);}
        .css-status-seg span{font-size:0.65rem;font-weight:700;color:white;text-shadow:0 1px 2px rgba(0,0,0,0.15);}
        .css-status-legend{display:flex;flex-wrap:wrap;gap:12px;}
        .css-status-legend-item{display:flex;align-items:center;gap:6px;font-size:0.78rem;color:#555;}
        .css-status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    </style>

    <div class="grid-2 mb-3" style="align-items: stretch; gap: 24px;">
        <!-- Weekly Sales (Vertical CSS Bars) -->
        <div class="card" style="display:flex;flex-direction:column;">
            <div class="card-header">📊 Weekly Sales Trend (My Products)</div>
            <div class="card-body" style="padding:15px;flex:1;">
                <?php if(count($weeklyDays) === 0): ?>
                    <div class="text-center" style="padding:40px;color:#888;">No sales data for the past 7 days</div>
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
        <div style="display:flex;flex-direction:column;gap:24px;">
            <!-- Top Selling Medicines (Horizontal Bars) -->
            <div class="card" style="flex:1;">
                <div class="card-header">💊 My Top Selling Medicines</div>
                <div class="card-body" style="padding:12px 16px;">
                    <?php if(count($topMedNames) === 0): ?>
                        <div class="text-center" style="padding:20px;color:#888;">No sales data yet</div>
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
            <div class="card" style="flex:1;">
                <div class="card-header">📋 My Order Status Overview</div>
                <div class="card-body" style="padding:16px;">
                    <?php if(count($statusLabels) === 0): ?>
                        <div class="text-center" style="padding:20px;color:#888;">No orders yet</div>
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
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="add_medicine.php" class="btn btn-success">Add New Medicine</a>
                <a href="my_medicines.php" class="btn btn-primary">My Medicines</a>
                <a href="orders.php" class="btn btn-warning">View Orders</a>
                <a href="../profile.php" class="btn btn-secondary">My Profile</a>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start; gap:24px;">
        
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

        
        <div>
            <div class="card mb-3">
                <div class="card-header">
                    <span>Low Stock Alert</span>
                    <span class="badge badge-danger"><?= $lowStock->num_rows ?></span>
                </div>
                <div class="card-body">
                    <?php if($lowStock->num_rows===0): ?>
                    <div class="text-center" style="padding:30px; color:#888;">All medicines have sufficient stock!</div>
                    <?php else: ?>
                    <?php while($m=$lowStock->fetch_assoc()): ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:bold;color:#4f46e5;">MED</div>
                        <div style="flex:1;">
                            <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($m['name']) ?></div>
                            <div style="color:var(--danger); font-weight:600; font-size:0.85rem;">Only <?= $m['stock'] ?> <?= $m['unit'] ?> left!</div>
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
                    <div style="display:flex; gap:6px;">
                        <span class="badge badge-danger" title="Expired / Near Expiry (30 days)" style="font-size:0.7rem;"><?= $redAlerts ?></span>
                        <span class="badge badge-warning" title="Expiring Soon (90 days)" style="font-size:0.7rem;"><?= $yellowAlerts ?></span>
                        <span class="badge badge-success" title="Safe" style="font-size:0.7rem;"><?= $greenAlerts ?></span>
                    </div>
                </div>
                <div class="card-body" style="padding-top: 10px;">
                    <?php if($expiringMeds->num_rows===0): ?>
                        <div class="text-center" style="padding:30px; color:#888;">All medicines are safe!</div>
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
                            <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                                <div style="width:36px;height:36px;border-radius:50%;background:var(--light);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--primary);flex-shrink:0;">🩹</div>
                                <div style="flex:1;">
                                    <div class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($em['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;">Exp: <?= date('d M Y', strtotime($em['expiry_date'])) ?></div>
                                </div>
                                <span class="badge badge-<?= $alertClass ?>" style="font-size:0.72rem; padding:4px 8px; font-weight:700;"><?= $alertText ?></span>
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
