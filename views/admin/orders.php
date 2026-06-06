<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>All Orders</h1><p>Manage and track customer orders</p></div>
    </div>

    <?php if(!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <div class="tabs mb-2">
        <a href="?filter=all" class="tab <?= $filter==='all'?'active':'' ?>">All</a>
        <a href="?filter=pending" class="tab <?= $filter==='pending'?'active':'' ?>">Pending</a>
        <a href="?filter=processing" class="tab <?= $filter==='processing'?'active':'' ?>">Processing</a>
        <a href="?filter=shipped" class="tab <?= $filter==='shipped'?'active':'' ?>">Shipped</a>
        <a href="?filter=delivered" class="tab <?= $filter==='delivered'?'active':'' ?>">Delivered</a>
        <a href="?filter=cancelled" class="tab <?= $filter==='cancelled'?'active':'' ?>">Cancelled</a>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>#</th><th>Customer</th><th>Amount</th><th>Delivery Location</th><th>Payment</th><th>Prescription</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if($orders->num_rows===0): ?>
                <tr><td colspan="9" class="text-center p-40">No orders found</td></tr>
                <?php else: ?>
                <?php while($o=$orders->fetch_assoc()): ?>
                <?php $items=$this->db->query("SELECT oi.quantity, m.name FROM order_items oi JOIN medicines m ON oi.medicine_id=m.id WHERE oi.order_id={$o['id']}"); ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($o['customer_name']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($o['delivery_phone']) ?></small>
                    </td>
                    <td>
                        <strong class="text-primary">৳<?= number_format($o['total_amount'],2) ?></strong>
                        <br><small class="text-muted">
                        <?php while($it=$items->fetch_assoc()): ?>
                            <?= htmlspecialchars(substr($it['name'],0,15)) ?> (<?= $it['quantity'] ?>)<br>
                        <?php endwhile; ?>
                        </small>
                    </td>
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
                                        <button type="submit" name="reject_rx" class="btn btn-sm btn-danger btn-rx-action">Reject</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted fs-sm">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge badge-<?= $statusColors[$o['status']] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td>
                        <a href="../invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-outline btn-invoice-admin">Invoice</a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
