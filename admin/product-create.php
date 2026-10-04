<?php
// admin/product-create.php
// Add New Tile Product Form

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $brand = trim($_POST['brand'] ?? 'TilePoint Luxe');
    $tileType = trim($_POST['tile_type'] ?? 'Vitrified Tile');
    $material = trim($_POST['material'] ?? 'Glazed Vitrified');
    $finish = trim($_POST['finish'] ?? 'Matt');
    $surface = trim($_POST['surface'] ?? 'Smooth');
    $color = trim($_POST['color'] ?? 'White');
    $pattern = trim($_POST['pattern'] ?? 'Marble Vein');
    $size = trim($_POST['size'] ?? '600x1200 mm');
    $thickness = trim($_POST['thickness'] ?? '9 mm');
    $waterAbsorption = trim($_POST['water_absorption'] ?? '< 0.05%');
    $strength = trim($_POST['strength'] ?? '> 2000N');
    $application = trim($_POST['application'] ?? 'Living Room, Bathroom');
    $usage = trim($_POST['usage'] ?? 'Floor & Wall');
    $coveragePerBox = (float)($_POST['coverage_per_box'] ?? 15.5);
    $piecesPerBox = (int)($_POST['pieces_per_box'] ?? 4);
    $boxWeight = trim($_POST['box_weight'] ?? '28 kg');
    $description = trim($_POST['description'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');

    if (empty($name) || !$categoryId || empty($sku) || $price <= 0) {
        $error = "Please fill in all required tile product details.";
    } else {
        $slug = slugify($name);
        
        $stmt = $db->prepare("INSERT INTO products 
            (category_id, name, slug, sku, short_description, description, price, discount_price, stock_quantity, brand, tile_type, material, finish, surface, color, pattern, size, thickness, water_absorption, strength, application, usage, coverage_per_box, pieces_per_box, box_weight, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        
        $stmt->execute([
            $categoryId, $name, $slug, $sku, $shortDesc, $description, $price, $discountPrice, $stock, $brand, $tileType, $material, $finish, $surface, $color, $pattern, $size, $thickness, $waterAbsorption, $strength, $application, $usage, $coveragePerBox, $piecesPerBox, $boxWeight
        ]);

        $productId = $db->lastInsertId();

        // Handle File Upload for Images
        if (!empty($_FILES['product_images']['name'][0])) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            foreach ($_FILES['product_images']['name'] as $key => $filename) {
                $file = [
                    'name' => $_FILES['product_images']['name'][$key],
                    'type' => $_FILES['product_images']['type'][$key],
                    'tmp_name' => $_FILES['product_images']['tmp_name'][$key],
                    'error' => $_FILES['product_images']['error'][$key],
                    'size' => $_FILES['product_images']['size'][$key]
                ];
                $result = uploadFile($file, $uploadDir);
                if ($result['success']) {
                    $relPath = 'uploads/products/' . $result['filename'];
                    $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)");
                    $stmtImg->execute([$productId, $relPath, $key + 1]);
                }
            }
        } else {
            // Default placeholder image
            $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, 'assets/images/products/carrara-white-1.jpg', 1)");
            $stmtImg->execute([$productId]);
        }

        header("Location: " . ADMIN_URL . "/products.php?msg=created");
        exit;
    }
}
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Add New Tile Surface</h1>
    </div>
</div>

<?php if ($error): ?>
    <div style="background: #FEE2E2; color: #B91C1C; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="<?php echo ADMIN_URL; ?>/product-create.php" method="POST" enctype="multipart/form-data" style="background: #FFF; padding: 32px; border-radius: 8px; border: 1px solid var(--admin-border);">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
        <div class="form-group">
            <label class="form-label">Tile Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Statuario White Gloss Slab" required>
        </div>
        <div class="form-group">
            <label class="form-label">Category *</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;">
        <div class="form-group">
            <label class="form-label">SKU *</label>
            <input type="text" name="sku" class="form-control" placeholder="TP-MAR-099" required>
        </div>
        <div class="form-group">
            <label class="form-label">Price per sq. ft. (₹) *</label>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="85.00" required>
        </div>
        <div class="form-group">
            <label class="form-label">Discount Price (₹)</label>
            <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="75.00">
        </div>
        <div class="form-group">
            <label class="form-label">Stock Quantity (Boxes) *</label>
            <input type="number" name="stock_quantity" class="form-control" value="100" required>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;">
        <div class="form-group">
            <label class="form-label">Tile Size</label>
            <input type="text" name="size" class="form-control" value="600x1200 mm">
        </div>
        <div class="form-group">
            <label class="form-label">Finish</label>
            <input type="text" name="finish" class="form-control" value="High Gloss">
        </div>
        <div class="form-group">
            <label class="form-label">Material</label>
            <input type="text" name="material" class="form-control" value="Glazed Vitrified">
        </div>
        <div class="form-group">
            <label class="form-label">Tile Type</label>
            <input type="text" name="tile_type" class="form-control" value="Vitrified Slab">
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px;">
        <div class="form-group">
            <label class="form-label">Coverage Per Box (sq. ft.)</label>
            <input type="number" step="0.01" name="coverage_per_box" class="form-control" value="15.50">
        </div>
        <div class="form-group">
            <label class="form-label">Pieces Per Box</label>
            <input type="number" name="pieces_per_box" class="form-control" value="4">
        </div>
        <div class="form-group">
            <label class="form-label">Box Weight</label>
            <input type="text" name="box_weight" class="form-control" value="28 kg">
        </div>
    </div>

    <div class="form-group" style="margin-bottom: 24px;">
        <label class="form-label">Space Application</label>
        <input type="text" name="application" class="form-control" value="Living Room, Bathroom, Commercial">
    </div>

    <div class="form-group" style="margin-bottom: 24px;">
        <label class="form-label">Product Description</label>
        <textarea name="description" class="form-control" rows="4"></textarea>
    </div>

    <div class="form-group" style="margin-bottom: 32px;">
        <label class="form-label">Upload Product Images</label>
        <input type="file" name="product_images[]" multiple class="form-control">
    </div>

    <button type="submit" class="btn btn-accent btn-lg">Save Tile Product</button>
</form>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
