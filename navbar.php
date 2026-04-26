<?php
$user = getUser();
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        💊 Pharmacy Management System
    </a>
    <ul class="navbar-nav">
        <?php if (!$user): ?>
            <li><a href="/pharmacy/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">🏠 Home</a></li>
            <li><a href="/pharmacy/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">💊 Medicines</a></li>
            <li><a href="/pharmacy/login.php" class="<?= $currentPage=='login.php'?'active':'' ?>">🔐 Login</a></li>
            <li><a href="/pharmacy/register.php" class="<?= $currentPage=='register.php'?'active':'' ?>">📝 Register</a></li>
        <?php elseif ($user['role'] === 'admin'): ?>
            <li><a href="/pharmacy/admin/dashboard.php" class="<?= strpos($currentPage,'dashboard')!==false?'active':'' ?>">📊 Dashboard</a></li>
            <li><a href="/pharmacy/admin/users.php" class="<?= $currentPage=='users.php'?'active':'' ?>">👥 Users</a></li>
            <li><a href="/pharmacy/admin/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">💊 Medicines</a></li>
            <li><a href="/pharmacy/admin/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">📦 Orders</a></li>
            <li><a href="/pharmacy/admin/feedback.php" class="<?= $currentPage=='feedback.php'?'active':'' ?>">💬 Feedback</a></li>
            <li><a href="/pharmacy/profile.php">👤 <?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout">🚪 Logout</a></li>
        <?php elseif ($user['role'] === 'salesperson'): ?>
            <li><a href="/pharmacy/seller/dashboard.php" class="<?= strpos($currentPage,'dashboard')!==false?'active':'' ?>">📊 Dashboard</a></li>
            <li><a href="/pharmacy/seller/add_medicine.php" class="<?= $currentPage=='add_medicine.php'?'active':'' ?>">➕ Add Medicine</a></li>
            <li><a href="/pharmacy/seller/my_medicines.php" class="<?= $currentPage=='my_medicines.php'?'active':'' ?>">💊 My Medicines</a></li>
            <li><a href="/pharmacy/seller/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">📦 Orders</a></li>
            <li><a href="/pharmacy/profile.php">👤 <?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout">🚪 Logout</a></li>
        <?php else: ?>
            <li><a href="/pharmacy/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">🏠 Home</a></li>
            <li><a href="/pharmacy/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">💊 Shop</a></li>
            <li><a href="/pharmacy/cart.php" class="<?= $currentPage=='cart.php'?'active':'' ?>">
                🛒 Cart <?php if($cartCount>0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a></li>
            <li><a href="/pharmacy/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">📦 Orders</a></li>
            <li><a href="/pharmacy/feedback.php" class="<?= $currentPage=='feedback.php'?'active':'' ?>">💬 Feedback</a></li>
            <li><a href="/pharmacy/profile.php">👤 <?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout">🚪 Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

