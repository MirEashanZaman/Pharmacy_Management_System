<?php if($meds->num_rows===0): ?>
    <div class="card"><div class="card-body text-center" style="padding:60px;">
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
            <div class="med-card-stock <?= $med['stock']<20?'low':'' ?>">
                <?= $med['stock']>0 ? 'In Stock ('.$med['stock'].')' : 'Out of Stock' ?>
            </div>
            <?php if($med['seller_name']): ?>
            <div style="font-size:0.75rem; color:#888; margin-bottom:8px;">By: <?= htmlspecialchars($med['seller_name']) ?></div>
            <?php endif; ?>
            <div class="d-flex gap-1">
                <a href="medicine_detail.php?id=<?= $med['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">View</a>
                <?php if($user && $user['role']==='customer' && $med['stock']>0): ?>
                <a href="add_to_cart.php?id=<?= $med['id'] ?>" class="btn btn-primary btn-sm btn-add-to-cart" data-id="<?= $med['id'] ?>" style="flex:1;">Cart</a>
                <?php elseif(!$user): ?>
                <a href="login.php" class="btn btn-primary btn-sm" style="flex:1;">Login</a>
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
