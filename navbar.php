<?php
$user = getUser();
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<script>
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
    }
</script>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        Pharmacy Management System
    </a>
    <ul class="navbar-nav">
        <?php if (!$user): ?>
            <li><a href="/pharmacy/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">Home</a></li>
            <li><a href="/pharmacy/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">Medicines</a></li>
            <li><a href="/pharmacy/login.php" class="<?= $currentPage=='login.php'?'active':'' ?>">Login</a></li>
            <li><a href="/pharmacy/registration.php" class="<?= $currentPage=='registration.php'?'active':'' ?>">Register</a></li>
        <?php elseif ($user['role'] === 'admin'): ?>
            <li><a href="/pharmacy/admin/dashboard.php" class="<?= strpos($currentPage,'dashboard')!==false?'active':'' ?>">Dashboard</a></li>
            <li><a href="/pharmacy/admin/users.php" class="<?= $currentPage=='users.php'?'active':'' ?>">Users</a></li>
            <li><a href="/pharmacy/admin/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">Medicines</a></li>
            <li><a href="/pharmacy/admin/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">Orders</a></li>
            <li><a href="/pharmacy/admin/feedback.php" class="<?= $currentPage=='feedback.php'?'active':'' ?>">Feedback</a></li>
            <li><a href="/pharmacy/profile.php"><?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">Logout</a></li>
        <?php elseif ($user['role'] === 'salesperson'): ?>
            <li><a href="/pharmacy/seller/dashboard.php" class="<?= strpos($currentPage,'dashboard')!==false?'active':'' ?>">Dashboard</a></li>
            <li><a href="/pharmacy/seller/add_medicine.php" class="<?= $currentPage=='add_medicine.php'?'active':'' ?>">Add Medicine</a></li>
            <li><a href="/pharmacy/seller/my_medicines.php" class="<?= $currentPage=='my_medicines.php'?'active':'' ?>">My Medicines</a></li>
            <li><a href="/pharmacy/seller/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">Orders</a></li>
            <li><a href="/pharmacy/seller/feedback.php" class="<?= $currentPage=='feedback.php'?'active':'' ?>">Feedback</a></li>
            <li><a href="/pharmacy/profile.php"><?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">Logout</a></li>
        <?php else: ?>
            <li><a href="/pharmacy/index.php" class="<?= $currentPage=='index.php'?'active':'' ?>">Home</a></li>
            <li><a href="/pharmacy/medicines.php" class="<?= $currentPage=='medicines.php'?'active':'' ?>">Shop</a></li>
            <li><a href="/pharmacy/cart.php" class="<?= $currentPage=='cart.php'?'active':'' ?>">
                Cart <?php if($cartCount>0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a></li>
            <li><a href="/pharmacy/orders.php" class="<?= $currentPage=='orders.php'?'active':'' ?>">Orders</a></li>
            <li><a href="/pharmacy/feedback.php" class="<?= $currentPage=='feedback.php'?'active':'' ?>">Feedback</a></li>
            <li><a href="/pharmacy/profile.php"><?= htmlspecialchars($user['name']) ?></a></li>
            <li><a href="/pharmacy/logout.php" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">Logout</a></li>
        <?php endif; ?>
        <li style="margin-left: 8px;">
            <button id="themeToggleBtn" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); font-size: 1rem; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; transition: var(--transition);" title="Toggle Dark/Light Mode">
                🌙
            </button>
        </li>
    </ul>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const themeBtn = document.getElementById("themeToggleBtn");
    if (!themeBtn) return;
    
    // Check local storage for theme
    const currentTheme = localStorage.getItem("theme") || "light";
    if (currentTheme === "dark") {
        document.body.classList.add("dark-mode");
        themeBtn.innerText = "☀️";
    } else {
        themeBtn.innerText = "🌙";
    }

    themeBtn.addEventListener("click", function() {
        if (document.body.classList.contains("dark-mode")) {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
            themeBtn.innerText = "🌙";
        } else {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
            themeBtn.innerText = "☀️";
        }
    });
});

// --- Vanilla JS Toast Notification Utility ---
function showToast(message, type = 'info', duration = 4000) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'ℹ️';
    if (type === 'success') icon = '✅';
    else if (type === 'error') icon = '❌';
    else if (type === 'warning') icon = '⚠️';
    
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 1.25rem;">${icon}</span>
            <span style="font-size: 0.9rem; font-weight: 600; line-height:1.4;">${message}</span>
        </div>
        <span style="cursor: pointer; opacity: 0.6; font-size: 0.85rem; padding: 4px; margin-left: 8px;" onclick="this.parentElement.classList.add('fade-out'); setTimeout(() => this.parentElement.remove(), 300)">✕</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('fade-out');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }, duration);
}
</script>

<?php
// Capture flash alerts dynamically
$flashMsg = '';
$flashType = 'info';

if (isset($_SESSION['success'])) {
    $flashMsg = $_SESSION['success'];
    $flashType = 'success';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $flashMsg = $_SESSION['error'];
    $flashType = 'error';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['warning'])) {
    $flashMsg = $_SESSION['warning'];
    $flashType = 'warning';
    unset($_SESSION['warning']);
} elseif (isset($_SESSION['info'])) {
    $flashMsg = $_SESSION['info'];
    $flashType = 'info';
    unset($_SESSION['info']);
}

// Local variable fallbacks (if page has local $success or $error)
if (empty($flashMsg)) {
    if (isset($success) && !empty($success)) {
        $flashMsg = $success;
        $flashType = 'success';
    } elseif (isset($error) && !empty($error)) {
        $flashMsg = $error;
        $flashType = 'error';
    }
}
?>

<?php if (!empty($flashMsg)): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    showToast(<?= json_encode($flashMsg) ?>, <?= json_encode($flashType) ?>);
});
</script>
<?php endif; ?>

