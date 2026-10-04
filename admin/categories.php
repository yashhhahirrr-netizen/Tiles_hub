<?php
// admin/categories.php
// Category Management & Uploads

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$message = null;

// Handle Category Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    
    if (!empty($name)) {
        $slug = slugify($name);
        $imgPath = 'assets/images/categories/floor-tiles.jpg';

        if (!empty($_FILES['image']['name'])) {
            $uploadRes = uploadFile($_FILES['image'], __DIR__ . '/../uploads/categories/');
            if ($uploadRes['success']) {
                $imgPath = 'uploads/categories/' . $uploadRes['filename'];
            }
        }

        $stmt = $db->prepare("INSERT INTO categories (name, slug, description, image, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$name, $slug, $desc, $imgPath]);
        $message = "Category created successfully!";
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $catId = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$catId]);
    $message = "Category deleted.";
}

$categories = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Category Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Organize tile surfaces by space applications & material finishes.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 32px;">
    <!-- Add Category Form -->
    <div style="background: #FFF; padding: 28px; border-radius: 8px; border: 1px solid var(--admin-border); height: fit-content;">
        <h3 class="font-heading" style="margin-bottom: 16px;">Add New Category</h3>
        <form action="<?php echo ADMIN_URL; ?>/categories.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Mosaic Tiles">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Category Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-accent btn-block">Create Category</button>
        </form>
    </div>

    <!-- Category List Table -->
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Tiles Count</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($cat['image'] ?? 'assets/images/categories/floor-tiles.jpg'); ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=100&q=80'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                        <td><?php echo $cat['product_count']; ?> Tiles</td>
                        <td><span class="badge-status <?php echo strtolower($cat['status']); ?>"><?php echo htmlspecialchars($cat['status']); ?></span></td>
                        <td>
                            <a href="<?php echo ADMIN_URL; ?>/categories.php?action=delete&id=<?php echo $cat['id']; ?>" onclick="return confirm('Delete this category?')" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
