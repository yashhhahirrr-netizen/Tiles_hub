<?php
// index.php
// TilePoint Homepage - Luxury Architectural Surfaces

require_once __DIR__ . '/includes/header.php';
$db = getDBConnection();

// Fetch active categories
$stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC LIMIT 8");
$stmt->execute();
$categories = $stmt->fetchAll();

// Fetch featured tile products
$stmt = $db->prepare("SELECT p.*, c.name AS category_name, 
                             (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE p.status = 'active' 
                      ORDER BY p.id DESC LIMIT 8");
$stmt->execute();
$featuredTiles = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="eyebrow">Architectural Material Specifier</span>
                <h1 class="font-heading">Surfaces That Define Your Living Space.</h1>
                <p>Explore Italian marble-recreation slabs, high-density porcelain planks, and precision anti-skid outdoor pavers engineered for modern luxury homes.</p>
                <div class="hero-btns">
                    <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary btn-lg">Explore Tile Collections</a>
                    <a href="<?php echo BASE_URL; ?>/tile-calculator.php" class="btn btn-outline btn-lg">Calculate Room Tiles</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-img-wrap">
                    <img src="<?php echo BASE_URL; ?>/assets/images/products/calacatta-gold-room.jpg" alt="Calacatta Gold Italian Marble Finish Tile Living Hall" onerror="this.src='https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80'">
                </div>
                <div class="hero-floating-badge">
                    <div class="badge-icon">💎</div>
                    <div>
                        <strong style="display: block; font-size: 0.95rem;">Calacatta Oro Slab</strong>
                        <span style="font-size: 0.78rem; color: var(--color-text-muted);">800x1600 mm | High Mirror Gloss</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shop By Tile Category -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="eyebrow">Categorized Surfaces</span>
                <h2 class="font-heading">Shop By Tile Category</h2>
            </div>
            <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-outline btn-sm">View All Categories &rarr;</a>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo BASE_URL; ?>/shop.php?category=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-img">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" onerror="this.src='https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=600&q=80'">
                    </div>
                    <div class="category-body">
                        <h3 class="category-title font-heading"><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p class="category-desc"><?php echo htmlspecialchars($cat['description']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Tile Showcase -->
<section class="section-padding" style="background: var(--color-bg-alt);">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="eyebrow">Curated Material Selection</span>
                <h2 class="font-heading">Featured Tile Collections</h2>
            </div>
            <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary btn-sm">Explore All Tiles</a>
        </div>

        <div class="product-grid">
            <?php foreach ($featuredTiles as $tile): ?>
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
                            <span class="spec-pill"><?php echo htmlspecialchars($tile['tile_type']); ?></span>
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
    </div>
</section>

<!-- Tile Calculator Section Widget -->
<section class="section-padding">
    <div class="container">
        <div class="calculator-card">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 32px;">
                <span class="eyebrow" style="color: var(--color-gold);">Instant Measurement Tool</span>
                <h2 class="font-heading" style="color: #FFFFFF;">Tile Coverage & Box Calculator</h2>
                <p style="color: var(--color-bg-alt); font-size: 0.95rem;">Input your room length and width to calculate exact square footage requirements, recommended 10% cutting wastage, and required box quantities.</p>
            </div>

            <form id="tile-calc-form">
                <div class="calc-form-grid">
                    <div class="calc-field">
                        <label>Length</label>
                        <input type="number" step="0.1" id="calc-length" value="12" required>
                    </div>
                    <div class="calc-field">
                        <label>Width</label>
                        <input type="number" step="0.1" id="calc-width" value="10" required>
                    </div>
                    <div class="calc-field">
                        <label>Measurement Unit</label>
                        <select id="calc-unit">
                            <option value="feet">Feet (ft)</option>
                            <option value="meter">Meters (m)</option>
                            <option value="inch">Inches (in)</option>
                        </select>
                    </div>
                    <div class="calc-field">
                        <label>Cutting Wastage</label>
                        <select id="calc-wastage">
                            <option value="5">5% (Simple Grid)</option>
                            <option value="10" selected>10% (Standard Layout)</option>
                            <option value="15">15% (Diagonal / Pattern)</option>
                        </select>
                    </div>
                </div>

                <div class="calc-result-box">
                    <div class="result-stat">
                        <div class="stat-val" id="res-raw-area">120.00 sq. ft.</div>
                        <div class="stat-lbl">Room Carpet Area</div>
                    </div>
                    <div class="result-stat">
                        <div class="stat-val" id="res-wastage-area">12.00 sq. ft.</div>
                        <div class="stat-lbl">Recommended Wastage</div>
                    </div>
                    <div class="result-stat">
                        <div class="stat-val" id="res-total-area">132.00 sq. ft.</div>
                        <div class="stat-lbl">Total Tile Required</div>
                    </div>
                    <div class="result-stat">
                        <div class="stat-val" id="res-boxes-needed">9 Boxes</div>
                        <div class="stat-lbl">Estimated Boxes Needed</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Interior Applications Showcase -->
<section class="section-padding" style="background: var(--color-bg-alt);">
    <div class="container">
        <div class="section-header" style="text-align: center; display: block; max-width: 600px; margin: 0 auto 48px;">
            <span class="eyebrow">Space Optimization</span>
            <h2 class="font-heading">Tiles For Every Living Space</h2>
            <p class="subtitle">Specially formulated surface finishes engineered for moisture, stain resistance, and structural longevity.</p>
        </div>

        <div class="category-grid">
            <div class="category-card">
                <div class="category-body">
                    <span class="eyebrow">High Traffic</span>
                    <h3 class="category-title font-heading">Living Room & Foyers</h3>
                    <p class="category-desc">Grand 800x1600mm vitrified slabs that create unbroken luxury marble vistas with zero grout lines.</p>
                </div>
            </div>
            <div class="category-card">
                <div class="category-body">
                    <span class="eyebrow">Moisture Resistant</span>
                    <h3 class="category-title font-heading">Master Bathrooms</h3>
                    <p class="category-desc">R10 anti-skid satin floor tiles paired with non-porous ceramic wall accent cladding.</p>
                </div>
            </div>
            <div class="category-card">
                <div class="category-body">
                    <span class="eyebrow">Stain Proof</span>
                    <h3 class="category-title font-heading">Gourmet Kitchens</h3>
                    <p class="category-desc">Subway glass glazes and Venetian terrazzo tiles resistant to hot oil, spices, and acid spills.</p>
                </div>
            </div>
            <div class="category-card">
                <div class="category-body">
                    <span class="eyebrow">Heavy Duty</span>
                    <h3 class="category-title font-heading">Outdoor & Driveways</h3>
                    <p class="category-desc">16mm fullbody porcelain pavers engineered for high vehicle load and weather durability.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why TilePoint -->
<section class="section-padding">
    <div class="container">
        <div class="section-header" style="text-align: center; display: block; max-width: 600px; margin: 0 auto 48px;">
            <span class="eyebrow">Architectural Excellence</span>
            <h2 class="font-heading">Why Choose TilePoint?</h2>
        </div>

        <div class="category-grid">
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">🛡️</div>
                <h4 style="margin-bottom: 8px;">15-Year Structural Warranty</h4>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">All vitrified tiles are fired at 1200°C for exceptional breaking strength and color permanence.</p>
            </div>
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">📦</div>
                <h4 style="margin-bottom: 8px;">Specimen Sample Box</h4>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">Request real cut-tile samples delivered straight to your site before making bulk decisions.</p>
            </div>
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">💧</div>
                <h4 style="margin-bottom: 8px;">Zero Water Absorption</h4>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">&lt; 0.05% water absorption ensures no dampness, mold growth, or tile swelling over decades.</p>
            </div>
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">🚚</div>
                <h4 style="margin-bottom: 8px;">Breakage Guarantee</h4>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">Heavy crate packaging with 100% transit insurance. Damaged boxes replaced instantly.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
