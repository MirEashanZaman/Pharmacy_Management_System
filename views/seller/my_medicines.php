<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Medicines - Pharmacy Management System</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>My Medicines</h1><p><?= $meds->num_rows ?> medicine(s) listed</p></div>
        <a href="add_medicine.php" class="btn btn-success">Add Medicine</a>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <?php if($meds->num_rows===0): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
        <div style="font-size:1.5rem;font-weight:bold;color:#4f46e5;margin-bottom:16px;">MED</div>
        <h3>No medicines listed yet</h3>
        <p class="text-muted">Start adding medicines to sell</p>
        <a href="add_medicine.php" class="btn btn-success mt-2">Add First Medicine</a>
    </div></div>
    <?php else: ?>
    <div class="card">
        <div class="table-container">
            <table>
                <thead><tr><th>Image</th><th>Medicine</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Sold</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while($m=$meds->fetch_assoc()): ?>
                <?php $sold=$this->db->query("SELECT SUM(oi.quantity) as s FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE oi.medicine_id={$m['id']} AND o.status='delivered'")->fetch_assoc()['s']??0; ?>
                <tr>
                    <td>
                        <?php if($m['image']&&$m['image']!=='default_medicine.png'&&file_exists('../uploads/medicines/'.$m['image'])): ?>
                            <img src="../uploads/medicines/<?= htmlspecialchars($m['image']) ?>" style="width:55px;height:55px;border-radius:10px;object-fit:cover;" alt="">
                        <?php else: ?>
                            <div style="width:55px;height:55px;border-radius:10px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:bold;color:#4f46e5;">MED</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($m['name']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($m['generic_name']) ?></small>
                        <br><small class="text-muted">Brand: <?= htmlspecialchars($m['brand']) ?></small>
                        <?php if($m['requires_prescription']): ?><br><span class="rx-badge">Rx</span><?php endif; ?>
                    </td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($m['category']) ?></span></td>
                    <td class="fw-bold text-primary">৳<?= number_format($m['price'],2) ?><br><small class="text-muted">per <?= $m['unit'] ?></small></td>
                    <td>
                        <span class="fw-bold <?= $m['stock']<=10?'text-danger':($m['stock']<=50?'text-warning':'text-success') ?>">
                            <?= $m['stock'] ?> <?= $m['unit'] ?>
                        </span>
                        <?php if($m['stock']<=10): ?><br><span class="badge badge-danger">Low Stock!</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if($m['is_active']): ?><span class="badge badge-success">Listed</span>
                        <?php else: ?><span class="badge badge-danger">Removed</span><?php endif; ?>
                    </td>
                    <td class="fw-bold"><?= $sold ?? 0 ?> sold</td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="edit_medicine.php?id=<?= $m['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <?php if($m['is_active']): ?>
                            <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this medicine from listing?')">Remove</a>
                            <?php endif; ?>
                            <a href="../medicine_detail.php?id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
