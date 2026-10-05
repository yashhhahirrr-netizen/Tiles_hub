<?php
// product.php
// Tile Product Details & Architectural Specification Page

require_once __DIR__ . '/config/app.php';

$productId = (int)($_GET['id'] ?? 0);
$db = getDBConnection();

$stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$productId]);
$tile = $stmt->fetch();

if (!$tile) {
    header("Location: " . BASE_URL . "/404.php");
    exit;
}

// Fetch images
$stmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->execute([$productId]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($images)) {
    $images = ['assets/images/products/carrara-white-1.jpg'];
}

// Fetch reviews
$stmt = $db->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.id DESC");
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll();

// Related tiles
$stmt = $db->prepare("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image 
                      FROM products p WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4");
$stmt->execute([$tile['category_id'], $productId]);
$relatedTiles = $stmt->fetchAll();

$effectivePrice = (!empty($tile['discount_price']) && $tile['discount_price'] > 0) ? $tile['discount_price'] : $tile['price'];
$boxPrice = getBoxPrice($effectivePrice, $tile['coverage_per_box']);
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container section-padding">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 24px;">
        <a href="<?php echo BASE_URL; ?>/index.php">Home</a> &nbsp;/&nbsp;
        <a href="<?php echo BASE_URL; ?>/shop.php">Tiles</a> &nbsp;/&nbsp;
        <a href="<?php echo BASE_URL; ?>/shop.php?category=<?php echo $tile['category_slug']; ?>"><?php echo htmlspecialchars($tile['category_name']); ?></a> &nbsp;/&nbsp;
        <span style="color: var(--color-primary); font-weight: 500;"><?php echo htmlspecialchars($tile['name']); ?></span>
    </div>

    <!-- Product Main Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-bottom: 60px;">
        <!-- Left: Image Gallery -->
        <div>
            <div style="background: var(--color-bg-alt); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--color-border); margin-bottom: 16px; height: 480px; position: relative;">
                <img id="main-product-img" src="<?php echo BASE_URL . '/' . htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($tile['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=1000&q=80'">
            </div>

            <div style="display: flex; gap: 12px; overflow-x: auto;">
                <?php foreach ($images as $img): ?>
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($img); ?>" 
                         onclick="document.getElementById('main-product-img').src = this.src"
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-sm); border: 2px solid var(--color-border); cursor: pointer;"
                         onerror="this.src='https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=200&q=80'">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Specification & Order Panel -->
        <div>
            <span class="eyebrow"><?php echo htmlspecialchars($tile['brand']); ?> &bull; SKU: <?php echo htmlspecialchars($tile['sku']); ?></span>
            <h1 class="font-heading" style="font-size: 2.4rem; margin-bottom: 12px;"><?php echo htmlspecialchars($tile['name']); ?></h1>

            <div class="tile-meta-tags" style="margin-bottom: 16px;">
                <span class="spec-pill" style="font-size: 0.8rem; padding: 4px 10px;"><?php echo htmlspecialchars($tile['size']); ?></span>
                <span class="spec-pill" style="font-size: 0.8rem; padding: 4px 10px;"><?php echo htmlspecialchars($tile['finish']); ?></span>
                <span class="spec-pill" style="font-size: 0.8rem; padding: 4px 10px;"><?php echo htmlspecialchars($tile['material']); ?></span>
                <span class="spec-pill" style="font-size: 0.8rem; padding: 4px 10px;"><?php echo htmlspecialchars($tile['tile_type']); ?></span>
            </div>

            <!-- Pricing Box -->
            <div style="background: var(--color-card); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 24px;">
                <div style="display: flex; align-items: baseline; gap: 16px; margin-bottom: 8px;">
                    <span style="font-size: 2.2rem; font-weight: 700; color: var(--color-primary);"><?php echo formatPrice($effectivePrice); ?></span>
                    <span style="font-size: 1rem; color: var(--color-text-muted);">/ sq. ft.</span>
                    
                    <?php if (!empty($tile['discount_price'])): ?>
                        <span style="text-decoration: line-through; color: var(--color-text-light); font-size: 1.1rem;"><?php echo formatPrice($tile['price']); ?></span>
                    <?php endif; ?>
                </div>

                <div style="font-size: 0.9rem; color: var(--color-text-muted);">
                    <strong>Box Price:</strong> <?php echo formatPrice($boxPrice); ?> 
                    (Coverage: <strong><?php echo $tile['coverage_per_box']; ?> sq. ft.</strong> | <strong><?php echo $tile['pieces_per_box']; ?> Pcs/Box</strong>)
                </div>

                <div style="margin-top: 12px; font-size: 0.85rem; color: <?php echo $tile['stock_quantity'] > 0 ? 'var(--color-success)' : 'var(--color-danger)'; ?>; font-weight: 600;">
                    <?php echo $tile['stock_quantity'] > 0 ? 'In Stock (' . $tile['stock_quantity'] . ' Boxes Available)' : 'Out of Stock'; ?>
                </div>
            </div>

            <!-- Quantity & Purchase Box -->
            <form action="<?php echo BASE_URL; ?>/cart.php" method="POST" style="margin-bottom: 24px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $tile['id']; ?>">

                <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 20px;">
                    <label style="font-weight: 500; font-size: 0.9rem;">Quantity (Boxes):</label>
                    <div style="display: flex; align-items: center; border: 1px solid var(--color-border); border-radius: var(--radius-sm); overflow: hidden; background: #FFF;">
                        <button type="button" class="js-qty-btn" data-dir="minus" style="padding: 8px 16px; border: none; background: none; cursor: pointer; font-size: 1.1rem;">-</button>
                        <input type="number" id="buy-boxes" name="boxes" value="1" min="1" max="<?php echo $tile['stock_quantity']; ?>" style="width: 60px; text-align: center; border: none; font-weight: 600; outline: none;">
                        <button type="button" class="js-qty-btn" data-dir="plus" style="padding: 8px 16px; border: none; background: none; cursor: pointer; font-size: 1.1rem;">+</button>
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 2;">Add To Cart</button>
                    <button type="button" class="btn btn-outline btn-lg js-wishlist-toggle <?php echo isInWishlist($_SESSION['user_id'] ?? null, $tile['id']) ? 'active' : ''; ?>" data-product-id="<?php echo $tile['id']; ?>" style="flex: 1;">â™¥ Wishlist</button>
                </div>
            </form>

            <!-- Sample Box CTA Button -->
            <div style="background: var(--color-bg-alt); padding: 16px 20px; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="font-size: 0.9rem; display: block;">Unsure about texture or color match?</strong>
                    <span style="font-size: 0.8rem; color: var(--color-text-muted);">Request a cut specimen sample delivered to your site.</span>
                </div>
                <a href="<?php echo BASE_URL; ?>/sample-request.php?product_id=<?php echo $tile['id']; ?>" class="btn btn-accent btn-sm">Order Sample</a>
            </div>
        </div>
    </div>

    <!-- Detailed Architectural Specifications Table -->
    <div style="margin-bottom: 60px;">
        <h2 class="font-heading" style="margin-bottom: 24px;">Technical Specifications</h2>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <tbody>
                    <tr><td style="width: 30%; font-weight: 600;">Tile Brand</td><td><?php echo htmlspecialchars($tile['brand']); ?></td></tr>
                    <tr><td style="font-weight: 600;">Tile Type & Category</td><td><?php echo htmlspecialchars($tile['tile_type']); ?> (<?php echo htmlspecialchars($tile['category_name']); ?>)</td></tr>
                    <tr><td style="font-weight: 600;">Body Material</td><td><?php echo htmlspecialchars($tile['material']); ?></td></tr>
                    <tr><td style="font-weight: 600;">Surface Finish</td><td><?php echo htmlspecialchars($tile['finish']); ?> (<?php echo htmlspecialchars($tile['surface']); ?>)</td></tr>
                    <tr><td style="font-weight: 600;">Color & Pattern</td><td><?php echo htmlspecialchars($tile['color']); ?> â€” <?php echo htmlspecialchars($tile['pattern']); ?></td></tr>
                    <tr><td style="font-weight: 600;">Tile Dimensions</td><td><?php echo htmlspecialchars($tile['size']); ?> (Thickness: <?php echo htmlspecialchars($tile['thickness']); ?>)</td></tr>
                    <tr><td style="font-weight: 600;">Water Absorption</td><td><?php echo htmlspecialchars($tile['water_absorption']); ?></td></tr>
                    <tr><td style="font-weight: 600;">Breaking Strength</td><td><?php echo htmlspecialchars($tile['strength']); ?></td></tr>
                    <tr><td style="font-weight: 600;">Suitable Spaces / Application</td><td><?php echo htmlspecialchars($tile['application']); ?> (<?php echo htmlspecialchars($tile['usage']); ?>)</td></tr>
                    <tr><td style="font-weight: 600;">Box Packaging Details</td><td><?php echo $tile['coverage_per_box']; ?> sq. ft. per box / <?php echo $tile['pieces_per_box']; ?> pieces per box (Weight: <?php echo htmlspecialchars($tile['box_weight']); ?>)</td></tr>
                    <tr><td style="font-weight: 600;">Warranty</td><td><?php echo htmlspecialchars($tile['warranty']); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Reviews -->
    <div style="margin-bottom: 60px;">
        <h2 class="font-heading" style="margin-bottom: 24px;">Customer Reviews & Feedback</h2>
        
        <?php if (empty($reviews)): ?>
            <p style="color: var(--color-text-muted);">No reviews yet for this tile product. Be the first verified buyer to submit a review.</p>
        <?php else: ?>
            <div style="display: grid; gap: 16px;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: var(--color-card); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <strong><?php echo htmlspecialchars($rev['full_name']); ?></strong>
                            <span style="color: var(--color-gold); font-size: 1.1rem;"><?php echo str_repeat('â˜…', $rev['rating']); ?></span>
                        </div>
                        <p style="font-size: 0.9rem; color: var(--color-text-main);"><?php echo htmlspecialchars($rev['review_text']); ?></p>
                        <span style="font-size: 0.75rem; color: var(--color-text-light); margin-top: 8px; display: block;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Tile Products -->
    <?php if (!empty($relatedTiles)): ?>
        <div>
            <h2 class="font-heading" style="margin-bottom: 24px;">Complementary Tile Surfaces</h2>
            <div class="product-grid">
                <?php foreach ($relatedTiles as $rel): ?>
                    <div class="tile-card">
                        <div class="tile-media">
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($rel['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                        </div>
                        <div class="tile-body">
                            <h3 class="tile-title font-heading"><a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $rel['id']; ?>"><?php echo htmlspecialchars($rel['name']); ?></a></h3>
                            <div class="price-sqft"><?php echo formatPrice($rel['price']); ?> <span class="unit">/ sq. ft.</span></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
