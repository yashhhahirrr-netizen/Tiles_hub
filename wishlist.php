<?php
// wishlist.php
// Customer Wishlist Page

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin('wishlist.php');

$userId = $_SESSION['user_id'];
$db = getDBConnection();

$stmt = $db->prepare("SELECT p.*, w.id AS wishlist_id,
                             (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image 
                      FROM wishlist w 
                      JOIN products p ON w.product_id = p.id 
                      WHERE w.user_id = ? AND p.status = 'active'
                      ORDER BY w.id DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Saved Surfaces</span>
        <h1 class="font-heading">My Saved Tile Wishlist</h1>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--color-card); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 12px;">Your Wishlist is Empty</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 24px;">Explore our collections and click the heart icon on any tile card to save items here.</p>
            <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary">Browse Catalogue</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($wishlistItems as $tile): ?>
                <div class="tile-card">
                    <div class="tile-media">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($tile['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" alt="<?php echo htmlspecialchars($tile['name']); ?>">
                        <button class="tile-wishlist-btn js-wishlist-toggle active" data-product-id="<?php echo $tile['id']; ?>" title="Remove from Wishlist">
                            ♥
                        </button>
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
                            <button class="btn btn-primary btn-sm btn-block js-add-to-cart" data-product-id="<?php echo $tile['id']; ?>">Move To Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
