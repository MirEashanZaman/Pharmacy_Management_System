<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders - Seller - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>My Orders</h1><p><?= $orderCount ?> order(s) for your medicines</p></div>
    </div>

    <?php if(empty($orders)): ?>
    <div class="card"><div class="card-body text-center p-60">
        <div class="empty-state-icon">ORD</div>
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
                    <td class="fs-md">
                        <?php while($it=$items->fetch_assoc()): ?>
                            <div><?= htmlspecialchars($it['name']) ?> ×<?= $it['quantity'] ?></div>
                        <?php endwhile; ?>
                    </td>
                    <td class="fw-bold text-primary">৳<?= number_format($o['total_amount'],2) ?></td>
                    <td class="fs-md">
                        <?= htmlspecialchars($o['delivery_division']) ?><br>
                        <?= htmlspecialchars($o['delivery_district']) ?> › <?= htmlspecialchars($o['delivery_upazila']) ?>
                    </td>
                    <td><span class="badge badge-secondary"><?= ucfirst($o['payment_method']) ?></span></td>
                    <td>
                        <?php if ($o['prescription_image']): ?>
                            <a href="../uploads/prescriptions/<?= htmlspecialchars($o['prescription_image']) ?>" target="_blank" class="display-block mb-1">
                                <img src="../uploads/prescriptions/<?= htmlspecialchars($o['prescription_image']) ?>" class="prescription-preview-sm" alt="Rx">
                            </a>
                            <?php if ($o['rx_approved'] == 1): ?>
                                <span class="badge badge-success fs-sm">Rx Approved</span>
                            <?php elseif ($o['rx_approved'] == -1): ?>
                                <span class="badge badge-danger fs-sm">Rx Rejected</span>
                            <?php else: ?>
                                <div class="display-flex gap-4 flex-column">
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <button type="submit" name="approve_rx" class="btn btn-sm btn-success btn-rx-action">Approve</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <button type="submit" name="reject_rx" class="btn btn-sm btn-danger" class="btn btn-sm btn-danger btn-rx-action">Reject</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted fs-8">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td>
                        <form method="POST" class="display-flex gap-6 align-center">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="form-control p-6-10 fs-8 w-auto">
                                <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">✓</button>
                        </form>
                        <a href="../invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-outline mt-1 display-flex justify-content-center align-center p-5-0 fs-sm w-100">Invoice</a>
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
