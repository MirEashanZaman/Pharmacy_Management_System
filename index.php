<?php
require_once 'session.php';
$user = getUser();

// Fetch a few featured medicines for customer view
$featuredMeds = $conn->query("
    SELECT m.*, u.name as seller_name 
    FROM medicines m 
    LEFT JOIN users u ON m.salesperson_id=u.id 
    WHERE m.is_active=1 AND m.stock>0 
    ORDER BY m.id DESC 
    LIMIT 4
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management System - Bangladesh's Trusted Online Pharmacy</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Customer Hero Section -->
<div class="hero-banner">
    <h1>Quality Medicines, Delivered Fast</h1>
    <p>Bangladesh's trusted online healthcare shop. Search for registered brands, check stocks, upload prescriptions, and order safely to your doorstep across all 64 districts.</p>
    <form action="medicines.php" method="GET" class="search-container" style="margin-bottom: 24px;">
        <input type="text" name="search" placeholder="Type generic names or brand medicines (e.g. Napa, Seclo)..." autocomplete="off" required>
        <button type="submit">Search Shop</button>
    </form>
    <?php if(!$user): ?>
    <div class="hero-cta">
        <a href="login.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem; font-weight: 700; border-radius: 30px;">Sign In</a>
        <a href="registration.php" class="btn btn-outline" style="padding: 12px 30px; font-size: 1rem; font-weight: 700; border-radius: 30px; background: rgba(255,255,255,0.15); color: white; border-color: white;">Sign Up</a>
    </div>
    <?php endif; ?>
</div>

<div class="main-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
    
    <!-- Medicine Categories -->
    <h2 class="section-title">Shop by Category</h2>
    <div class="category-grid">
        <a href="medicines.php?cat=Antibiotic" class="category-card">
            <div class="category-icon">🧪</div>
            <h3>Antibiotics</h3>
        </a>
        <a href="medicines.php?cat=Painkiller" class="category-card">
            <div class="category-icon">💊</div>
            <h3>Painkillers</h3>
        </a>
        <a href="medicines.php?cat=Gastric" class="category-card">
            <div class="category-icon">🔥</div>
            <h3>Gastric Relief</h3>
        </a>
        <a href="medicines.php?cat=Vitamin" class="category-card">
            <div class="category-icon">🍊</div>
            <h3>Vitamins</h3>
        </a>
    </div>

    <!-- Featured Products -->
    <h2 class="section-title">Featured Medicines</h2>
    <div class="grid-auto mb-3" style="margin-bottom: 56px;">
        <?php if($featuredMeds->num_rows === 0): ?>
            <div class="card" style="grid-column: 1/-1;"><div class="card-body text-center" style="padding:40px;">No featured medicines loaded yet.</div></div>
        <?php else: ?>
            <?php while($med = $featuredMeds->fetch_assoc()): ?>
                <div class="med-card">
                    <?php if($med['image'] && $med['image']!=='default_medicine.png' && file_exists('uploads/medicines/'.$med['image'])): ?>
                        <img src="uploads/medicines/<?= htmlspecialchars($med['image']) ?>" class="med-card-img" alt="<?= htmlspecialchars($med['name']) ?>">
                    <?php else: ?>
                        <div class="med-card-img-placeholder" style="font-size:0.9rem;font-weight:bold;color:var(--primary);">MED</div>
                    <?php endif; ?>
                    <div class="med-card-body">
                        <div class="d-flex justify-between align-center mb-1">
                            <span class="badge badge-info"><?= htmlspecialchars($med['category']) ?></span>
                            <?php if($med['requires_prescription']): ?><span class="rx-badge">Rx</span><?php endif; ?>
                        </div>
                        <div class="med-card-name"><?= htmlspecialchars($med['name']) ?></div>
                        <div class="med-card-generic"><?= htmlspecialchars($med['generic_name']) ?> | <?= htmlspecialchars($med['brand']) ?></div>
                        <div class="med-card-price">৳<?= number_format($med['price'],2) ?> <small style="font-size:0.7rem;color:#888;">per <?= $med['unit'] ?></small></div>
                        
                        <div class="d-flex gap-1" style="margin-top:12px;">
                            <a href="medicine_detail.php?id=<?= $med['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">View Details</a>
                            <?php if($user && $user['role']==='customer'): ?>
                                <a href="add_to_cart.php?id=<?= $med['id'] ?>" class="btn btn-primary btn-sm btn-add-to-cart" data-id="<?= $med['id'] ?>" style="flex:1;">Add to Cart</a>
                            <?php elseif(!$user): ?>
                                <a href="login.php" class="btn btn-primary btn-sm" style="flex:1;">Buy Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- Health Benefits Grid -->
    <h2 class="section-title">Why Choose Our Pharmacy</h2>
    <div class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon">🛡️</div>
            <div>
                <h3>Licensed Pharmacy</h3>
                <p>100% genuine and safe prescription and OTC drugs regulated by DGDA Bangladesh guidelines.</p>
            </div>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon">🚀</div>
            <div>
                <h3>District-wide Delivery</h3>
                <p>Swift shipping and tracking services coverage stretching to all 8 divisions and 64 districts.</p>
            </div>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon">💳</div>
            <div>
                <h3>Convenient Payment</h3>
                <p>Secure cash-on-delivery and digital mobile wallet payments via bKash, Nagad, or Rocket.</p>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
