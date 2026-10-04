<?php
// admin/products.php
// Tile Product CRUD Listing

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

// Delete product handler
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$delId]);
    header("Location: " . ADMIN_URL . "/products.php?msg=deleted");
    exit;
}

$search = trim($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE :s OR p.sku LIKE :s OR p.brand LIKE :s OR p.size LIKE :s OR c.name LIKE :s)";
    $params[':s'] = '%' . $search . '%';
}

$stmt = $db->prepare("SELECT p.*, c.name AS category_name, 
                             (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      {$where} 
                      ORDER BY p.id DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Tile Products Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Add, edit, update inventory box stock, and manage tile surface listings.</p>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/product-create.php" class="btn btn-accent btn-sm">+ Add Tile Surface</a>
    </div>
</div>

<div style="margin-bottom: 24px;">
    <form action="<?php echo ADMIN_URL; ?>/products.php" method="GET" style="display: flex; gap: 12px; max-width: 500px;">
        <input type="text" name="search" class="form-control" placeholder="Search by name, SKU, size, category..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Tile Details</th>
                <th>Category</th>
                <th>Size / Finish</th>
                <th>Price / sq.ft</th>
                <th>Stock (Boxes)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($p['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=100&q=80'">
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                        <div style="font-size: 0.78rem; color: var(--admin-text-muted);">SKU: <?php echo htmlspecialchars($p['sku']); ?> | Brand: <?php echo htmlspecialchars($p['brand']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['size']); ?><br><span style="font-size: 0.78rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($p['finish']); ?></span></td>
                    <td><strong><?php echo formatPrice(!empty($p['discount_price']) ? $p['discount_price'] : $p['price']); ?></strong></td>
                    <td>
                        <span class="badge-status <?php echo $p['stock_quantity'] < 20 ? 'pending' : 'active'; ?>">
                            <?php echo $p['stock_quantity']; ?> Boxes
                        </span>
                    </td>
                    <td><span class="badge-status <?php echo strtolower($p['status']); ?>"><?php echo htmlspecialchars($p['status']); ?></span></td>
                    <td>
                        <a href="<?php echo ADMIN_URL; ?>/product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                        <a href="<?php echo ADMIN_URL; ?>/products.php?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure you want to delete this tile surface?')" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
