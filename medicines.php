<?php
require_once 'session.php';
$user = getUser();

$search = trim($_GET['search'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page-1)*$perPage;

$where = "m.is_active=1";
$params = [];
$types = "";
if ($search) { $where .= " AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand LIKE ?)"; $s="%$search%"; $params[]=$s; $params[]=$s; $params[]=$s; $types.="sss"; }
if ($cat) { $where .= " AND m.category=?"; $params[]=$cat; $types.="s"; }

$countQuery = "SELECT COUNT(*) as c FROM medicines m WHERE $where";
if ($params) {
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['c'];
} else {
    $total = $conn->query($countQuery)->fetch_assoc()['c'];
}
$totalPages = ceil($total / $perPage);

$query = "SELECT m.*, u.name as seller_name FROM medicines m LEFT JOIN users u ON m.salesperson_id=u.id WHERE $where ORDER BY m.created_at DESC LIMIT $perPage OFFSET $offset";
if ($params) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $meds = $stmt->get_result();
} else {
    $meds = $conn->query($query);
}

$cats = $conn->query("SELECT DISTINCT category FROM medicines WHERE is_active=1 ORDER BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medicines - Pharmacy Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>💊 Medicines</h1>
            <p>Browse <?= $total ?> medicines available</p>
        </div>
    </div>

    
    <form method="GET" style="margin-bottom:20px;">
        <?php if($cat): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
        <div class="search-bar" style="max-width:500px;">
            <input type="text" name="search" placeholder="🔍 Search medicines, generic names..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">🔍</button>
        </div>
    </form>

    
    <div class="category-filter">
        <a href="medicines.php<?= $search?'?search='.urlencode($search):'' ?>" class="cat-btn <?= !$cat?'active':'' ?>">🔹 All</a>
        <?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?>
            <a href="medicines.php?cat=<?= urlencode($c['category']) ?><?= $search?'&search='.urlencode($search):'' ?>" class="cat-btn <?= $cat===$c['category']?'active':'' ?>"><?= htmlspecialchars($c['category']) ?></a>
        <?php endwhile; ?>
    </div>

    <?php if($meds->num_rows===0): ?>
        <div class="card"><div class="card-body text-center" style="padding:60px;">
            <div style="font-size:3rem; margin-bottom:16px;">😔</div>
            <h3>No medicines found</h3>
            <p class="text-muted">Try searching with different keywords</p>
            <a href="medicines.php" class="btn btn-primary mt-2">View All</a>
        </div></div>
    <?php else: ?>
    <div class="grid-auto">
        <?php while($med=$meds->fetch_assoc()): ?>
        <div class="med-card">
            <?php if($med['image'] && $med['image']!=='default_medicine.png' && file_exists('uploads/medicines/'.$med['image'])): ?>
                <img src="uploads/medicines/<?= htmlspecialchars($med['image']) ?>" class="med-card-img" alt="<?= htmlspecialchars($med['name']) ?>">
            <?php else: ?>
                <div class="med-card-img-placeholder">💊</div>
            <?php endif; ?>
            <div class="med-card-body">
                <div class="d-flex justify-between align-center mb-1">
                    <span class="badge badge-info"><?= htmlspecialchars($med['category']) ?></span>
                    <?php if($med['requires_prescription']): ?><span class="rx-badge">Rx</span><?php endif; ?>
                </div>
                <div class="med-card-name"><?= htmlspecialchars($med['name']) ?></div>
                <div class="med-card-generic"><?= htmlspecialchars($med['generic_name']) ?> | <?= htmlspecialchars($med['brand']) ?></div>
                <div class="med-card-price">৳<?= number_format($med['price'],2) ?> <small style="font-size:0.7rem;color:#888;">per <?= $med['unit'] ?></small></div>
                <div class="med-card-stock <?= $med['stock']<20?'low':'' ?>">
                    <?= $med['stock']>0 ? '✅ In Stock ('.$med['stock'].')' : '❌ Out of Stock' ?>
                </div>
                <?php if($med['seller_name']): ?>
                <div style="font-size:0.75rem; color:#888; margin-bottom:8px;">🏪 By: <?= htmlspecialchars($med['seller_name']) ?></div>
                <?php endif; ?>
                <div class="d-flex gap-1">
                    <a href="medicine_detail.php?id=<?= $med['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">👁️ View</a>
                    <?php if($user && $user['role']==='customer' && $med['stock']>0): ?>
                    <a href="add_to_cart.php?id=<?= $med['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;">🛒 Cart</a>
                    <?php elseif(!$user): ?>
                    <a href="login.php" class="btn btn-primary btn-sm" style="flex:1;">🛒 Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    
    <?php if($totalPages>1): ?>
    <div class="pagination">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($cat) ?>" class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>


