<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>My Orders</h1><p>Track your medicine orders</p></div>
        <a href="medicines.php" class="btn btn-primary">Shop More</a>
    </div>

    <?php if(!empty($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if($orders->num_rows===0): ?>
    <div class="card"><div class="card-body text-center p-60">
        <div class="empty-state-icon">ORD</div>
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
            <span class="text-muted fs-md"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="card-body">
            <div class="grid-2 gap-24">
                <div>
                    <h4 class="text-muted section-header-muted">ITEMS</h4>
                    <?php while($item=$items->fetch_assoc()): ?>
                    <div class="order-item-row">
                        <div class="item-placeholder-light">
                            <?php if($item['image']&&$item['image']!=='default_medicine.png'&&file_exists('uploads/medicines/'.$item['image'])): ?>
                                <img src="uploads/medicines/<?= htmlspecialchars($item['image']) ?>" class="thumbnail-img" alt="">
                            <?php else: ?>MED<?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <div class="fw-bold fs-md"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-muted">Qty: <?= $item['quantity'] ?> × ৳<?= number_format($item['price'],2) ?></div>
                        </div>
                        <div class="fw-bold">৳<?= number_format($item['quantity']*$item['price'],2) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div>
                    <h4 class="text-muted section-header-muted">DELIVERY INFO</h4>
                    <div class="content-box-light fs-md lh-19">
                        <div>Phone: <?= htmlspecialchars($order['delivery_phone']) ?></div>
                        <div>Location: <?= htmlspecialchars($order['delivery_division']) ?> › <?= htmlspecialchars($order['delivery_district']) ?> › <?= htmlspecialchars($order['delivery_upazila']) ?></div>
                        <div>Address: <?= htmlspecialchars($order['delivery_address']) ?></div>
                        <div>Payment: <?= ucfirst($order['payment_method']) ?></div>
                    </div>
                    <div class="order-total-amount">
                        Total: ৳<?= number_format($order['total_amount'],2) ?>
                    </div>
                    <div class="text-right mt-1">
                        <a href="invoice.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline btn-sm-padding">Print Invoice</a>
                    </div>
                    <?php if($order['status']==='delivered'): ?>
                    <a href="feedback.php" class="btn btn-success btn-sm mt-1 w-100 justify-center">Review Items</a>
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
                <div class="alert alert-danger mt-3 border-radius-8 fw-600">
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
