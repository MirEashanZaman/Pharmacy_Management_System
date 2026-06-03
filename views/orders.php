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
        <div><h1>My Orders</h1><p>Track your medicine orders</p></div>
        <a href="medicines.php" class="btn btn-primary">Shop More</a>
    </div>

    <?php if($orders->num_rows===0): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:1.5rem; font-weight:bold; color:#4f46e5; margin-bottom:16px;">ORD</div>
        <h3>No orders yet</h3>
        <p class="text-muted">Start shopping to place your first order</p>
        <a href="medicines.php" class="btn btn-primary mt-2">Shop Now</a>
    </div></div>
    <?php else: ?>
    <?php while($order=$orders->fetch_assoc()): ?>
    <?php
    
    $items = $this->orderModel->getOrderItems($order['id']);
    ?>
    <div class="card mb-2">
        <div class="card-header">
            <div class="d-flex align-center gap-2">
                <span>Order #<?= $order['id'] ?></span>
                <span class="badge badge-<?= $statusColors[$order['status']] ?>"><?= ucfirst($order['status']) ?></span>
            </div>
            <span class="text-muted" style="font-size:0.85rem;"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="card-body">
            <div class="grid-2" style="gap:20px;">
                <div>
                    <h4 style="font-size:0.9rem; margin-bottom:12px; color:#666;">ITEMS</h4>
                    <?php while($item=$items->fetch_assoc()): ?>
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                        <div style="width:40px;height:40px;border-radius:8px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:bold;color:var(--primary);">
                            <?php if($item['image']&&$item['image']!=='default_medicine.png'&&file_exists('uploads/medicines/'.$item['image'])): ?>
                                <img src="uploads/medicines/<?= htmlspecialchars($item['image']) ?>" style="width:100%;height:100%;border-radius:8px;object-fit:cover;" alt="">
                            <?php else: ?>MED<?php endif; ?>
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
                    <h4 style="font-size:0.9rem; margin-bottom:12px; color:#666;">DELIVERY INFO</h4>
                    <div style="background:#f8f9fa; border-radius:10px; padding:14px; font-size:0.85rem; line-height:1.9;">
                        <div>Phone: <?= htmlspecialchars($order['delivery_phone']) ?></div>
                        <div>Location: <?= htmlspecialchars($order['delivery_division']) ?> › <?= htmlspecialchars($order['delivery_district']) ?> › <?= htmlspecialchars($order['delivery_upazila']) ?></div>
                        <div>Address: <?= htmlspecialchars($order['delivery_address']) ?></div>
                        <div>Payment: <?= ucfirst($order['payment_method']) ?></div>
                    </div>
                    <div style="text-align:right; margin-top:12px; font-size:1.2rem; font-weight:800; color:var(--primary);">
                        Total: ৳<?= number_format($order['total_amount'],2) ?>
                    </div>
                    <div style="text-align:right; margin-top:8px;">
                        <a href="invoice.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline" style="display:inline-flex; padding:6px 12px; font-size:0.8rem;">Print Invoice</a>
                    </div>
                    <?php if($order['status']==='delivered'): ?>
                    <a href="medicines.php" class="btn btn-success btn-sm mt-1" style="width:100%; justify-content:center;">Review Items</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interactive Step Timeline -->
            <?php
            $statusSteps = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
            $currentStep = $statusSteps[$order['status']] ?? 0;
            ?>
            <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="order-timeline">
                    <div class="timeline-step <?= $currentStep >= 1 ? 'active' : '' ?>">
                        <div class="step-icon">1</div>
                        <div class="step-label">Pending</div>
                    </div>
                    <div class="timeline-line <?= $currentStep >= 2 ? 'active' : '' ?>"></div>
                    <div class="timeline-step <?= $currentStep >= 2 ? 'active' : '' ?>">
                        <div class="step-icon">2</div>
                        <div class="step-label">Processing</div>
                    </div>
                    <div class="timeline-line <?= $currentStep >= 3 ? 'active' : '' ?>"></div>
                    <div class="timeline-step <?= $currentStep >= 3 ? 'active' : '' ?>">
                        <div class="step-icon">3</div>
                        <div class="step-label">Shipped</div>
                    </div>
                    <div class="timeline-line <?= $currentStep >= 4 ? 'active' : '' ?>"></div>
                    <div class="timeline-step <?= $currentStep >= 4 ? 'active' : '' ?>">
                        <div class="step-icon">4</div>
                        <div class="step-label">Delivered</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger" style="margin-top: 20px; border-radius: 8px; font-weight: 600;">
                    This order has been cancelled.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>
</body>
</html>
