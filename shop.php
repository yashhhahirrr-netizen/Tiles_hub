<?php
// shop.php
// Tile Collections & Advanced Filter System

require_once __DIR__ . '/includes/header.php';
$db = getDBConnection();

// Fetch filter options from database
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$tileTypes = $db->query("SELECT DISTINCT tile_type FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
$materials = $db->query("SELECT DISTINCT material FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
$finishes = $db->query("SELECT DISTINCT finish FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
$colors = $db->query("SELECT DISTINCT color FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
$sizes = $db->query("SELECT DISTINCT size FROM products WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);

// Retrieve GET filters
$selectedCategory = trim($_GET['category'] ?? '');
$selectedType = trim($_GET['type'] ?? '');
$selectedMaterial = trim($_GET['material'] ?? '');
$selectedFinish = trim($_GET['finish'] ?? '');
$selectedColor = trim($_GET['color'] ?? '');
$selectedSize = trim($_GET['size'] ?? '');
$selectedApp = trim($_GET['application'] ?? '');
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 1000;
$sort = trim($_GET['sort'] ?? 'name_asc');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build Dynamic SQL Query
$whereClause = ["p.status = 'active'"];
$params = [];

if ($selectedCategory) {
    $whereClause[] = "c.slug = :category";
    $params[':category'] = $selectedCategory;
}
if ($selectedType) {
    $whereClause[] = "p.tile_type = :type";
    $params[':type'] = $selectedType;
}
if ($selectedMaterial) {
    $whereClause[] = "p.material = :material";
    $params[':material'] = $selectedMaterial;
}
if ($selectedFinish) {
    $whereClause[] = "p.finish = :finish";
    $params[':finish'] = $selectedFinish;
}
if ($selectedColor) {
    $whereClause[] = "p.color = :color";
    $params[':color'] = $selectedColor;
}
if ($selectedSize) {
    $whereClause[] = "p.size = :size";
    $params[':size'] = $selectedSize;
}
if ($selectedApp) {
    $whereClause[] = "p.application LIKE :app";
    $params[':app'] = '%' . $selectedApp . '%';
}
if ($minPrice > 0) {
    $whereClause[] = "p.price >= :min_price";
    $params[':min_price'] = $minPrice;
}
if ($maxPrice < 1000) {
    $whereClause[] = "p.price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

$whereSql = implode(" AND ", $whereClause);

// Sorting order
$orderBy = "p.name ASC";
switch ($sort) {
    case 'price_asc': $orderBy = "p.price ASC"; break;
    case 'price_desc': $orderBy = "p.price DESC"; break;
    case 'name_desc': $orderBy = "p.name DESC"; break;
    default: $orderBy = "p.name ASC"; break;
}

// Count total products matching filter
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE {$whereSql}");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Fetch products for current page
$query = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                 (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image
          FROM products p
          JOIN categories c ON p.category_id = c.id
          WHERE {$whereSql}
          ORDER BY {$orderBy}
          LIMIT {$limit} OFFSET {$offset}";

$stmt = $db->prepare($query);
$stmt->execute($params);
$tiles = $stmt->fetchAll();
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Architectural Material Catalogue</span>
        <h1 class="font-heading">Tile Collections & Surfaces</h1>
        <p class="subtitle">Filter through our high-density porcelain tiles, Italian marble reproductions, and anti-skid floor slabs.</p>
    </div>

    <div class="shop-layout">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <form action="<?php echo BASE_URL; ?>/shop.php" method="GET" id="filter-form">
                <div class="filter-group">
                    <h3 class="filter-title font-heading">Tile Category</h3>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['slug']; ?>" <?php echo $selectedCategory === $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title font-heading">Finish</h3>
                    <select name="finish" class="form-control" onchange="this.form.submit()">
                        <option value="">All Finishes</option>
                        <?php foreach ($finishes as $fin): ?>
                            <option value="<?php echo htmlspecialchars($fin); ?>" <?php echo $selectedFinish === $fin ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($fin); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title font-heading">Tile Size</h3>
                    <select name="size" class="form-control" onchange="this.form.submit()">
                        <option value="">All Sizes</option>
                        <?php foreach ($sizes as $sz): ?>
                            <option value="<?php echo htmlspecialchars($sz); ?>" <?php echo $selectedSize === $sz ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sz); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title font-heading">Material</h3>
                    <select name="material" class="form-control" onchange="this.form.submit()">
                        <option value="">All Materials</option>
                        <?php foreach ($materials as $mat): ?>
                            <option value="<?php echo htmlspecialchars($mat); ?>" <?php echo $selectedMaterial === $mat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title font-heading">Color Palette</h3>
                    <select name="color" class="form-control" onchange="this.form.submit()">
                        <option value="">All Colors</option>
                        <?php foreach ($colors as $col): ?>
                            <option value="<?php echo htmlspecialchars($col); ?>" <?php echo $selectedColor === $col ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($col); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title font-heading">Space Application</h3>
                    <select name="application" class="form-control" onchange="this.form.submit()">
                        <option value="">All Spaces</option>
                        <option value="Bathroom" <?php echo $selectedApp === 'Bathroom' ? 'selected' : ''; ?>>Bathroom</option>
                        <option value="Kitchen" <?php echo $selectedApp === 'Kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                        <option value="Living Room" <?php echo $selectedApp === 'Living Room' ? 'selected' : ''; ?>>Living Room</option>
                        <option value="Bedroom" <?php echo $selectedApp === 'Bedroom' ? 'selected' : ''; ?>>Bedroom</option>
                        <option value="Outdoor" <?php echo $selectedApp === 'Outdoor' ? 'selected' : ''; ?>>Outdoor / Balcony</option>
                        <option value="Commercial" <?php echo $selectedApp === 'Commercial' ? 'selected' : ''; ?>>Commercial</option>
                    </select>
                </div>

                <div class="filter-group">
                    <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-outline btn-sm btn-block">Reset All Filters</a>
                </div>
            </form>
        </aside>

        <!-- Main Product Area -->
        <main>
            <!-- Sort & Toolbar Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; background: var(--color-card); padding: 16px 24px; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                <div style="font-size: 0.9rem; color: var(--color-text-muted);">
                    Showing <strong><?php echo count($tiles); ?></strong> of <strong><?php echo $totalProducts; ?></strong> tile products
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="font-size: 0.85rem; font-weight: 500;">Sort By:</label>
                    <select class="form-control" style="width: auto; padding: 6px 12px;" onchange="location = this.value;">
                        <option value="<?php echo BASE_URL; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name_asc'])); ?>" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="<?php echo BASE_URL; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name_desc'])); ?>" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        <option value="<?php echo BASE_URL; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_asc'])); ?>" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="<?php echo BASE_URL; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_desc'])); ?>" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <?php if (empty($tiles)): ?>
                <div style="text-align: center; padding: 60px 20px; background: var(--color-card); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <h3 class="font-heading" style="margin-bottom: 12px;">No Tiles Match Your Criteria</h3>
                    <p style="color: var(--color-text-muted); margin-bottom: 24px;">Try adjusting your size, finish, or material filters to see available stock.</p>
                    <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($tiles as $tile): ?>
                        <div class="tile-card">
                            <div class="tile-media">
                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($tile['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" alt="<?php echo htmlspecialchars($tile['name']); ?>" onerror="this.src='https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=600&q=80'">
                                <button class="tile-wishlist-btn js-wishlist-toggle <?php echo isInWishlist($_SESSION['user_id'] ?? null, $tile['id']) ? 'active' : ''; ?>" data-product-id="<?php echo $tile['id']; ?>" title="Add to Wishlist">
                                    ♥
                                </button>
                            </div>
                            <div class="tile-body">
                                <div class="tile-meta-tags">
                                    <span class="spec-pill"><?php echo htmlspecialchars($tile['size']); ?></span>
                                    <span class="spec-pill"><?php echo htmlspecialchars($tile['finish']); ?></span>
                                    <span class="spec-pill"><?php echo htmlspecialchars($tile['material']); ?></span>
                                </div>
                                <h3 class="tile-title font-heading">
                                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $tile['id']; ?>"><?php echo htmlspecialchars($tile['name']); ?></a>
                                </h3>
                                
                                <div class="tile-pricing">
                                    <div>
                                        <div class="price-sqft">
                                            <?php echo formatPrice(!empty($tile['discount_price']) ? $tile['discount_price'] : $tile['price']); ?>
                                            <span class="unit">/ sq. ft.</span>
                                        </div>
                                        <div class="price-box-hint">
                                            Box (<?php echo $tile['coverage_per_box']; ?> sq.ft): <?php echo formatPrice(getBoxPrice(!empty($tile['discount_price']) ? $tile['discount_price'] : $tile['price'], $tile['coverage_per_box'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tile-actions">
                                    <a href="<?php echo BASE_URL; ?>/product.php?id=<?php echo $tile['id']; ?>" class="btn btn-outline btn-sm btn-block">Details</a>
                                    <button class="btn btn-primary btn-sm btn-block js-add-to-cart" data-product-id="<?php echo $tile['id']; ?>">+ Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 48px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
