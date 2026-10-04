<?php
// admin/sample-requests.php
// Specimen Sample Request Dispatch Management

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_sample') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? 'Submitted';
    $tracking = trim($_POST['tracking_number'] ?? '');

    $stmt = $db->prepare("UPDATE sample_requests SET status = ?, tracking_number = ? WHERE id = ?");
    $stmt->execute([$status, $tracking, $requestId]);
    $message = "Sample request status updated to '{$status}'!";
}

$requests = $db->query("SELECT s.*, p.name AS product_name, p.size, p.sku FROM sample_requests s JOIN products p ON s.product_id = p.id ORDER BY s.id DESC")->fetchAll();
$statuses = ['Submitted', 'Approved', 'Rejected', 'Dispatched', 'Completed'];
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Tile Sample Requests</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Approve, dispatch, and track specimen sample box requests submitted by clients.</p>
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
                <th>Customer Details</th>
                <th>Requested Tile</th>
                <th>Delivery Address</th>
                <th>Status</th>
                <th>Tracking #</th>
                <th>Update Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($req['customer_name']); ?></strong>
                        <div style="font-size: 0.78rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($req['phone']); ?></div>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($req['product_name']); ?></strong>
                        <div style="font-size: 0.78rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($req['size']); ?> | SKU: <?php echo htmlspecialchars($req['sku']); ?></div>
                    </td>
                    <td style="font-size: 0.85rem; max-width: 200px;">
                        <?php echo htmlspecialchars($req['address']); ?>, <?php echo htmlspecialchars($req['city']); ?> - <?php echo htmlspecialchars($req['pincode']); ?>
                    </td>
                    <td><span class="badge-status <?php echo strtolower($req['status']); ?>"><?php echo htmlspecialchars($req['status']); ?></span></td>
                    <td><code><?php echo htmlspecialchars($req['tracking_number'] ?? 'Not Dispatched'); ?></code></td>
                    <td>
                        <form action="<?php echo ADMIN_URL; ?>/sample-requests.php" method="POST" style="display: flex; gap: 6px;">
                            <input type="hidden" name="action" value="update_sample">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <select name="status" class="form-control" style="font-size: 0.8rem; padding: 4px;">
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo $req['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="tracking_number" value="<?php echo htmlspecialchars($req['tracking_number'] ?? ''); ?>" placeholder="Tracking #" class="form-control" style="font-size: 0.8rem; padding: 4px; width: 100px;">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
