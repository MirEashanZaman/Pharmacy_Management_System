<?php
require_once 'session.php';

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
    <link rel="stylesheet" href="style.css">
    <style>
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--secondary) 100%);
            color: white;
            padding: 90px 20px;
            text-align: center;
            border-radius: 0 0 24px 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 48px;
        }
        .hero-banner h1 {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 18px;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .hero-banner p {
            font-size: 1.25rem;
            opacity: 0.95;
            max-width: 750px;
            margin: 0 auto 32px auto;
            line-height: 1.6;
        }
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        .search-container input {
            width: 100%;
            padding: 16px 24px;
            font-size: 1rem;
            border-radius: 30px;
            border: none;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .search-container button {
            position: absolute;
            right: 8px;
            top: 6px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 700;
            transition: var(--transition);
        }
        .search-container button:hover {
            background: var(--primary-dark);
        }
        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 36px;
            position: relative;
            color: var(--primary-dark);
        }
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary);
            margin: 12px auto 0 auto;
            border-radius: 2px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 56px;
        }
        .category-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(60, 48, 112, 0.05);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .category-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }
        .category-icon {
            font-size: 2.2rem;
            color: var(--primary);
        }
        .category-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 56px;
        }
        .benefit-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(60, 48, 112, 0.05);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: var(--transition);
        }
        .benefit-card:hover {
            transform: translateY(-2px);
        }
        .benefit-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .benefit-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--dark);
        }
        .benefit-card p {
            font-size: 0.88rem;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Customer Hero Section -->
<div class="hero-banner">
    <h1>Quality Medicines, Delivered Fast</h1>
    <p>Bangladesh's trusted online healthcare shop. Search for registered brands, check stocks, upload prescriptions, and order safely to your doorstep across all 64 districts.</p>
    <form action="medicines.php" method="GET" class="search-container">
        <input type="text" name="search" placeholder="Type generic names or brand medicines (e.g. Napa, Seclo)..." autocomplete="off" required>
        <button type="submit">Search Shop</button>
    </form>
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
