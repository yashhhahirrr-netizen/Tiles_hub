<?php
// search.php
// Database-Backed Tile Search

require_once __DIR__ . '/includes/header.php';

$query = trim($_GET['q'] ?? '');
$db = getDBConnection();
$tiles = [];

if (!empty($query)) {
    $searchKey = '%' . $query . '%';
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name,
                                 (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image
                          FROM products p
                          JOIN categories c ON p.category_id = c.id
                          WHERE p.status = 'active' AND (
                              p.name LIKE :q OR
                              p.sku LIKE :q OR
                              p.description LIKE :q OR
                              p.brand LIKE :q OR
                              p.tile_type LIKE :q OR
                              p.material LIKE :q OR
                              p.finish LIKE :q OR
                              p.color LIKE :q OR
                              p.size LIKE :q OR
                              c.name LIKE :q
                          )
                          ORDER BY p.name ASC");
    $stmt->execute([':q' => $searchKey]);
    $tiles = $stmt->fetchAll();
}
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Database Search</span>
        <h1 class="font-heading">Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
        <p class="subtitle">Found <?php echo count($tiles); ?> matching architectural tile products.</p>
    </div>

    <?php if (empty($tiles)): ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--color-card); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 12px;">No Tile Surfaces Found</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 24px;">Try searching for generic keywords like "marble", "600x1200", "bathroom", or "vitrified".</p>
            <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary">Browse Full Catalogue</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($tiles as $tile): ?>
                <div class="tile-card">
                    <div class="tile-media">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($tile['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" alt="<?php echo htmlspecialchars($tile['name']); ?>">
                    </div>
                    <div class="tile-body">
                        <div class="tile-meta-tags">
                            <span class="spec-pill"><?php echo htmlspecialchars($tile['size']); ?></span>
                            <span class="spec-pill"><?php echo htmlspecialchars($tile['finish']); ?></span>
                        </div>
                        <h3 class="tile-title font-heading">
                            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $tile['id']; ?>"><?php echo htmlspecialchars($tile['name']); ?></a>
                        </h3>
                        <div class="tile-pricing">
                            <div class="price-sqft"><?php echo formatPrice(!empty($tile['discount_price']) ? $tile['discount_price'] : $tile['price']); ?> <span class="unit">/ sq. ft.</span></div>
                        </div>
                        <div class="tile-actions">
                            <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $tile['id']; ?>" class="btn btn-outline btn-sm btn-block">Details</a>
                            <button class="btn btn-primary btn-sm btn-block js-add-to-cart" data-product-id="<?php echo $tile['id']; ?>">+ Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
