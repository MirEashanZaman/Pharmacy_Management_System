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
        <div><h1>My Orders</h1><p><?= $orderCount ?> order(s) for your medicines</p></div>
    </div>

    <?php if(empty($orders)): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:1.5rem; font-weight:bold; color:#4f46e5; margin-bottom:16px;">ORD</div>
        <h3>No orders yet</h3>
        <p class="text-muted">Orders for your medicines will appear here</p>
    </div></div>
    <?php else: ?>
    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Amount</th><th>Delivery Location</th><th>Payment</th><th>Prescription</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($orders as $o): ?>
                <?php
                
                $items = $this->db->query("SELECT oi.*, m.name FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id={$o['id']} AND oi.medicine_id IN ($myMedIds)");
                ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($o['customer_name']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($o['delivery_phone']) ?></small>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php while($it=$items->fetch_assoc()): ?>
                            <div><?= htmlspecialchars($it['name']) ?> ×<?= $it['quantity'] ?></div>
                        <?php endwhile; ?>
                    </td>
                    <td class="fw-bold text-primary">৳<?= number_format($o['total_amount'],2) ?></td>
                    <td style="font-size:0.85rem;">
                        <?= htmlspecialchars($o['delivery_division']) ?><br>
                        <?= htmlspecialchars($o['delivery_district']) ?> › <?= htmlspecialchars($o['delivery_upazila']) ?>
                    </td>
                    <td><span class="badge badge-secondary"><?= ucfirst($o['payment_method']) ?></span></td>
                    <td>
                        <?php if ($o['prescription_image']): ?>
                            <a href="../uploads/prescriptions/<?= htmlspecialchars($o['prescription_image']) ?>" target="_blank" style="display:block;margin-bottom:6px;">
                                <img src="../uploads/prescriptions/<?= htmlspecialchars($o['prescription_image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #ddd;" alt="Rx">
                            </a>
                            <?php if ($o['rx_approved'] == 1): ?>
                                <span class="badge badge-success" style="font-size:0.75rem;">Rx Approved</span>
                            <?php elseif ($o['rx_approved'] == -1): ?>
                                <span class="badge badge-danger" style="font-size:0.75rem;">Rx Rejected</span>
                            <?php else: ?>
                                <div style="display:flex;gap:4px;flex-direction:column;">
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <button type="submit" name="approve_rx" class="btn btn-sm btn-success" style="padding:4px 8px;font-size:0.7rem;width:100%;">Approve</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <button type="submit" name="reject_rx" class="btn btn-sm btn-danger" style="padding:4px 8px;font-size:0.7rem;width:100%;">Reject</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.8rem;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td>
                        <a href="../invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-outline" style="display:flex; justify-content:center; align-items:center; padding:5px 10px; font-size:0.75rem;">Invoice</a>
                    </td>
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
