<?php
require_once 'session.php';
requireLogin('login.php');

$user = getUser();
$orderId = intval($_GET['id'] ?? 0);
if (!$orderId) {
    die("Invalid order ID.");
}

// Fetch order
$stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Access Control: Customer can only view their own invoice
if ($user['role'] === 'customer' && $order['user_id'] !== $user['id']) {
    die("Unauthorized access.");
}

// Fetch items
$items = $conn->query("SELECT oi.*, m.name, m.brand, m.unit FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id=$orderId");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice #<?= $order['id'] ?> - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/invoice.css">
</head>
<body>

<div class="print-actions">
    <a href="#" onclick="history.back(); return false;" class="btn btn-secondary">← Back to Orders</a>
    <div style="display: flex; gap: 10px;">
        <button onclick="downloadTextInvoice()" class="btn btn-outline" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 700; border-width: 2px;">Download Receipt</button>
        <button onclick="window.print()" class="btn btn-primary" style="padding: 12px 28px; font-size: 0.95rem;">Print Invoice</button>
    </div>
</div>

<div class="invoice-card">
    <div class="invoice-header">
        <div>
            <div class="invoice-title">INVOICE</div>
            <p class="text-muted" style="margin-top: 4px;">Order #<?= $order['id'] ?></p>
        </div>
        <div style="text-align: right;">
            <h3 style="color: var(--primary); font-weight: 800;">Pharmacy Care</h3>
            <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6;">
                Dhaka, Bangladesh<br>
                Hotline: 16700 (24/7)<br>
                info@pharmacare.bd
            </p>
        </div>
    </div>

    <div class="invoice-details">
        <div>
            <h4>Billed To:</h4>
            <strong style="font-size: 1.05rem;"><?= htmlspecialchars($order['customer_name']) ?></strong>
            <p class="text-muted" style="font-size: 0.9rem; margin-top: 6px; line-height: 1.6;">
                Phone: <?= htmlspecialchars($order['delivery_phone'] ? $order['delivery_phone'] : $order['customer_phone']) ?><br>
                Email: <?= htmlspecialchars($order['customer_email']) ?>
            </p>
        </div>
        <div>
            <h4>Delivery Address:</h4>
            <p style="font-size: 0.95rem; line-height: 1.6;">
                <?= htmlspecialchars($order['delivery_division']) ?>, <?= htmlspecialchars($order['delivery_district']) ?><br>
                <?= htmlspecialchars($order['delivery_upazila']) ?><br>
                <?= htmlspecialchars($order['delivery_address']) ?>
            </p>
        </div>
    </div>

    <div class="invoice-details" style="border-top: 2px solid #faf9fd; padding-top: 20px;">
        <div>
            <h4>Payment Info:</h4>
            <span class="badge badge-secondary" style="font-size: 0.85rem; padding: 6px 12px;"><?= strtoupper($order['payment_method']) ?></span>
        </div>
        <div>
            <h4>Invoice Date:</h4>
            <strong><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></strong>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Medicine / Brand</th>
                <th>Price</th>
                <th>Qty</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item=$items->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($item['brand']) ?></small>
                </td>
                <td>৳<?= number_format($item['price'], 2) ?> / <?= $item['unit'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td style="text-align: right; font-weight: 700;">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="invoice-totals">
        <div>Subtotal: <strong>৳<?= number_format($order['total_amount'], 2) ?></strong></div>
        <?php $delivery = $order['total_amount'] >= 500 ? 0 : 60; ?>
        <div>Delivery Fee: <strong><?= $delivery == 0 ? 'FREE' : '৳'.number_format($delivery, 2) ?></strong></div>
        <div class="grand-total">Total: ৳<?= number_format($order['total_amount'] + $delivery, 2) ?></div>
    </div>
</div>

<script>
// Auto-trigger print dialog if requested
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') {
        window.print();
    }
});

// Download Text Receipt format
function downloadTextInvoice() {
    const orderId = "#<?= $order['id'] ?>";
    const name = "<?= htmlspecialchars($order['customer_name']) ?>";
    const phone = "<?= htmlspecialchars($order['customer_phone']) ?>";
    const division = "<?= htmlspecialchars($order['delivery_division']) ?>";
    const district = "<?= htmlspecialchars($order['delivery_district']) ?>";
    const method = "<?= strtoupper($order['payment_method']) ?>";
    const date = "<?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>";
    const subtotal = "<?= number_format($order['total_amount'], 2) ?>";
    const delivery = "<?= $order['total_amount'] >= 500 ? 'FREE' : '৳60.00' ?>";
    const total = "৳<?= number_format($order['total_amount'] + ($order['total_amount'] >= 500 ? 0 : 60), 2) ?>";
    
    let text = "";
    text += "========================================\n";
    text += "       PHARMACY MANAGEMENT SYSTEM       \n";
    text += "       Bangladesh's Trusted Online      \n";
    text += "       Hotline: 16700 | info@pms.bd     \n";
    text += "========================================\n\n";
    text += "INVOICE ID  : " + orderId + "\n";
    text += "DATE        : " + date + "\n";
    text += "CUSTOMER    : " + name + "\n";
    text += "PHONE       : " + phone + "\n";
    text += "DELIVERY    : " + district + ", " + division + "\n";
    text += "----------------------------------------\n";
    text += "PAYMENT     : " + method + "\n";
    text += "========================================\n\n";
    text += "Subtotal    : ৳" + subtotal + "\n";
    text += "Delivery    : " + delivery + "\n";
    text += "Grand Total : " + total + "\n\n";
    text += "========================================\n";
    text += "      Thank you for your purchase!      \n";
    text += "      Please visit us again soon!      \n";
    text += "========================================\n";
    
    const blob = new Blob([text], { type: 'text/plain' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = "receipt_" + orderId.replace("#", "") + ".txt";
    link.click();
}
</script>
</body>
</html>
