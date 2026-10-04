<?php
// admin/reviews.php
// Review Moderation System

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$message = null;

if (isset($_GET['action']) && isset($_GET['id'])) {
    $revId = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$revId]);
        $message = "Review approved!";
    } elseif ($action === 'reject') {
        $stmt = $db->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$revId]);
        $message = "Review rejected.";
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$revId]);
        $message = "Review deleted.";
    }
}

$reviews = $db->query("SELECT r.*, u.full_name, p.name AS product_name 
                       FROM reviews r 
                       JOIN users u ON r.user_id = u.id 
                       JOIN products p ON r.product_id = p.id 
                       ORDER BY r.id DESC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Customer Reviews Moderation</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Approve, reject, or moderate client feedback on tile surfaces.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Tile Product</th>
                <th>Rating</th>
                <th>Review Text</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['product_name']); ?></td>
                    <td><span style="color: var(--color-gold);"><?php echo str_repeat('★', $r['rating']); ?></span></td>
                    <td style="max-width: 300px; font-size: 0.85rem;"><?php echo htmlspecialchars($r['review_text']); ?></td>
                    <td><span class="badge-status <?php echo strtolower($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
                    <td>
                        <a href="<?php echo ADMIN_URL; ?>/reviews.php?action=approve&id=<?php echo $r['id']; ?>" class="btn btn-outline btn-sm">Approve</a>
                        <a href="<?php echo ADMIN_URL; ?>/reviews.php?action=reject&id=<?php echo $r['id']; ?>" class="btn btn-sm" style="background: #FEF9C3; color: #A16207;">Reject</a>
                        <a href="<?php echo ADMIN_URL; ?>/reviews.php?action=delete&id=<?php echo $r['id']; ?>" onclick="return confirm('Delete review?')" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
