<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medicines - Pharmacy Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="page-header">
        <div>
            <h1>Medicines</h1>
            <p>Browse <?= $total ?> medicines available</p>
        </div>
    </div>

    
    <form method="GET" style="margin-bottom:20px;" onsubmit="return false;">
        <?php if($cat): ?><input type="hidden" name="cat" id="catFilter" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
        <div class="search-bar" style="max-width:500px;">
            <input type="text" id="searchInput" name="search" placeholder="Search medicines, generic names..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            <button type="submit">Search</button>
        </div>
    </form>

    
    <div class="category-filter">
        <a href="medicines.php<?= $search?'?search='.urlencode($search):'' ?>" class="cat-btn <?= !$cat?'active':'' ?>">All</a>
        <?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?>
            <a href="medicines.php?cat=<?= urlencode($c['category']) ?><?= $search?'&search='.urlencode($search):'' ?>" class="cat-btn <?= $cat===$c['category']?'active':'' ?>"><?= htmlspecialchars($c['category']) ?></a>
        <?php endwhile; ?>
    </div>

    <div id="medicines-container">
        <?php require 'medicines_partial.php'; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const container = document.getElementById("medicines-container");
    const catEl = document.getElementById("catFilter");
    const catVal = catEl ? catEl.value : "";

    let debounceTimer;
    searchInput.addEventListener("input", function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = searchInput.value.trim();
            const url = `medicines.php?ajax=1&search=${encodeURIComponent(query)}&cat=${encodeURIComponent(catVal)}`;
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(err => console.error("Error during AJAX search:", err));
        }, 150);
    });

    // Event delegation to capture cart button clicks dynamically
    document.addEventListener("click", function(e) {
        const btn = e.target.closest(".btn-add-to-cart");
        if (!btn) return;
        
        e.preventDefault();
        const url = btn.getAttribute("href") + "&ajax=1";
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Trigger dynamic floating toast alert
                    showToast(data.message, "success");
                    
                    // Update the global cart badge in navbar
                    const badge = document.querySelector(".cart-badge");
                    if (badge) {
                        badge.innerText = data.cartCount;
                        badge.style.display = "inline-flex";
                        
                        // micro bounce animation
                        badge.animate([
                            { transform: 'scale(1)' },
                            { transform: 'scale(1.4)' },
                            { transform: 'scale(1)' }
                        ], {
                            duration: 250,
                            easing: 'ease-out'
                        });
                    } else {
                        // Dynamically inject badge if first item added
                        const cartLink = document.querySelector("a[href*='cart.php']");
                        if (cartLink) {
                            cartLink.innerHTML = `Cart <span class="cart-badge" style="display:inline-flex;">${data.cartCount}</span>`;
                        }
                    }
                } else {
                    showToast(data.message || "Failed to add item", "error");
                }
            })
            .catch(err => {
                console.error("AJAX Cart Error:", err);
                showToast("Failed to add item to cart", "error");
            });
    });
});
</script>
</body>
</html>
