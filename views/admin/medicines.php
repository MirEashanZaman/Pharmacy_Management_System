<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medicines - Admin - Pharmacy Management System</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div><h1>All Medicines</h1><p>View and manage all medicines</p></div>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Generic</th><th>Category</th><th>Price</th><th>Stock</th><th>Added By</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if($meds->num_rows===0): ?>
                <tr><td colspan="9" class="text-center p-40">No medicines found</td></tr>
                <?php else: ?>
                <?php while($m=$meds->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($m['image']&&$m['image']!=='default_medicine.png'&&file_exists('../uploads/medicines/'.$m['image'])): ?>
                            <img src="../uploads/medicines/<?= htmlspecialchars($m['image']) ?>" class="med-img-sm" alt="">
                        <?php else: ?>
                            <div class="med-placeholder-sm">MED</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($m['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($m['brand']) ?></small></td>
                    <td><?= htmlspecialchars($m['generic_name']) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($m['category']) ?></span></td>
                    <td class="fw-bold text-primary">৳<?= number_format($m['price'],2) ?></td>
                    <td>
                        <span class="<?= $m['stock']<10?'text-danger fw-bold':'' ?>"><?= $m['stock'] ?> <?= $m['unit'] ?></span>
                    </td>
                    <td><?= htmlspecialchars($m['seller_name']??'N/A') ?></td>
                    <td>
                        <?php if($m['is_active']): ?><span class="badge badge-success">Active</span>
                        <?php else: ?><span class="badge badge-danger">Hidden</span><?php endif; ?>
                    </td>
                    <td>
                        <a href="?toggle=<?= $m['id'] ?>" class="btn btn-sm btn-<?= $m['is_active']?'warning':'success' ?>">
                            <?= $m['is_active']?'Hide':'Show' ?>
                        </a>
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
