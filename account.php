<?php
// account.php
// Customer Dashboard & Profile Management

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin('account.php');

$currentUser = getLoggedInUser();
$db = getDBConnection();
$tab = trim($_GET['tab'] ?? 'overview');
$action = trim($_GET['action'] ?? '');

if ($action === 'logout') {
    logoutUser();
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$message = null;
$error = null;

// Handle Profile / Password Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    
    if ($formAction === 'update_profile') {
        $name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            $error = "Name cannot be empty.";
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $currentUser['id']]);
            $message = "Profile updated successfully!";
            $currentUser = getLoggedInUser();
        }
    } elseif ($formAction === 'change_password') {
        $oldPass = $_POST['old_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($oldPass, $hash)) {
            $error = "Current password does not match.";
        } elseif (strlen($newPass) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($newPass !== $confirmPass) {
            $error = "New passwords do not match.";
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $currentUser['id']]);
            $message = "Password changed successfully!";
        }
    }
}

// Fetch orders
$stmt = $db->prepare("SELECT o.*, i.invoice_number FROM orders o LEFT JOIN invoice_records i ON o.id = i.order_id WHERE o.user_id = ? ORDER BY o.id DESC");
$stmt->execute([$currentUser['id']]);
$orders = $stmt->fetchAll();

// Fetch sample requests
$stmt = $db->prepare("SELECT s.*, p.name AS product_name, p.size, p.finish FROM sample_requests s JOIN products p ON s.product_id = p.id WHERE s.user_id = ? ORDER BY s.id DESC");
$stmt->execute([$currentUser['id']]);
$samples = $stmt->fetchAll();

// Fetch addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$currentUser['id']]);
$addresses = $stmt->fetchAll();
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Client Portal</span>
        <h1 class="font-heading">My Account & Orders</h1>
    </div>

    <?php if ($message): ?>
        <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: var(--radius-sm);">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #FEE2E2; color: #B91C1C; padding: 14px; margin-bottom: 24px; border-radius: var(--radius-sm);">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 240px 1fr; gap: 40px;">
        <!-- Sidebar Navigation -->
        <aside style="background: var(--color-card); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--color-border); height: fit-content;">
            <ul class="footer-links" style="gap: 8px;">
                <li><a href="<?php echo BASE_URL; ?>/account.php?tab=overview" style="display: block; padding: 8px 12px; border-radius: var(--radius-sm); <?php echo $tab === 'overview' ? 'background: var(--color-bg-alt); font-weight: 600; color: var(--color-accent);' : ''; ?>">Overview</a></li>
                <li><a href="<?php echo BASE_URL; ?>/account.php?tab=orders" style="display: block; padding: 8px 12px; border-radius: var(--radius-sm); <?php echo $tab === 'orders' ? 'background: var(--color-bg-alt); font-weight: 600; color: var(--color-accent);' : ''; ?>">My Orders (<?php echo count($orders); ?>)</a></li>
                <li><a href="<?php echo BASE_URL; ?>/account.php?tab=samples" style="display: block; padding: 8px 12px; border-radius: var(--radius-sm); <?php echo $tab === 'samples' ? 'background: var(--color-bg-alt); font-weight: 600; color: var(--color-accent);' : ''; ?>">Sample Requests (<?php echo count($samples); ?>)</a></li>
                <li><a href="<?php echo BASE_URL; ?>/account.php?tab=addresses" style="display: block; padding: 8px 12px; border-radius: var(--radius-sm); <?php echo $tab === 'addresses' ? 'background: var(--color-bg-alt); font-weight: 600; color: var(--color-accent);' : ''; ?>">Saved Addresses</a></li>
                <li><a href="<?php echo BASE_URL; ?>/account.php?tab=profile" style="display: block; padding: 8px 12px; border-radius: var(--radius-sm); <?php echo $tab === 'profile' ? 'background: var(--color-bg-alt); font-weight: 600; color: var(--color-accent);' : ''; ?>">Profile Settings</a></li>
                <hr style="margin: 8px 0; border: none; border-top: 1px solid var(--color-border);">
                <li><a href="<?php echo BASE_URL; ?>/account.php?action=logout" style="display: block; padding: 8px 12px; color: var(--color-danger);">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Tab Content -->
        <main>
            <?php if ($tab === 'overview'): ?>
                <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <h3 class="font-heading" style="margin-bottom: 12px;">Welcome Back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 24px;">Manage your tile orders, sample dispatches, tax invoices, and site delivery addresses.</p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: var(--radius-sm); text-align: center;">
                            <div style="font-size: 1.8rem; font-weight: 700; color: var(--color-primary);"><?php echo count($orders); ?></div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted);">Total Tile Orders</div>
                        </div>
                        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: var(--radius-sm); text-align: center;">
                            <div style="font-size: 1.8rem; font-weight: 700; color: var(--color-accent);"><?php echo count($samples); ?></div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted);">Sample Requests</div>
                        </div>
                        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: var(--radius-sm); text-align: center;">
                            <div style="font-size: 1.8rem; font-weight: 700; color: var(--color-sage);"><?php echo count($addresses); ?></div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted);">Saved Addresses</div>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab === 'orders'): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ord['order_number']); ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($ord['payment_method']); ?> (<?php echo htmlspecialchars($ord['payment_status']); ?>)</td>
                                    <td><span class="badge-status <?php echo strtolower($ord['order_status']); ?>"><?php echo htmlspecialchars($ord['order_status']); ?></span></td>
                                    <td><strong><?php echo formatPrice($ord['grand_total']); ?></strong></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $ord['id']; ?>" target="_blank" class="btn btn-outline btn-sm">Invoice</a>
                                        <a href="<?php echo BASE_URL; ?>/track-order.php?order_number=<?php echo htmlspecialchars($ord['order_number']); ?>" class="btn btn-primary btn-sm">Track</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab === 'samples'): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Requested Tile Specimen</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Tracking #</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($samples as $smp): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($smp['product_name']); ?></strong> (<?php echo htmlspecialchars($smp['size']); ?>)</td>
                                    <td><?php echo date('M d, Y', strtotime($smp['created_at'])); ?></td>
                                    <td><span class="badge-status <?php echo strtolower($smp['status']); ?>"><?php echo htmlspecialchars($smp['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($smp['tracking_number'] ?? 'Pending Dispatch'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab === 'addresses'): ?>
                <div style="display: grid; gap: 16px;">
                    <?php foreach ($addresses as $addr): ?>
                        <div style="background: var(--color-card); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                            <strong><?php echo htmlspecialchars($addr['full_name']); ?></strong> (<?php echo htmlspecialchars($addr['phone']); ?>)
                            <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-top: 4px;">
                                <?php echo htmlspecialchars($addr['address']); ?>, <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> - <?php echo htmlspecialchars($addr['pincode']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($tab === 'profile'): ?>
                <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border); max-width: 600px;">
                    <h3 class="font-heading" style="margin-bottom: 20px;">Edit Profile</h3>
                    <form action="<?php echo BASE_URL; ?>/account.php?tab=profile" method="POST" style="margin-bottom: 32px;">
                        <input type="hidden" name="form_action" value="update_profile">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address (Read Only)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly disabled style="background: var(--color-bg-alt);">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($currentUser['phone']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>

                    <h3 class="font-heading" style="margin-bottom: 20px;">Change Password</h3>
                    <form action="<?php echo BASE_URL; ?>/account.php?tab=profile" method="POST">
                        <input type="hidden" name="form_action" value="change_password">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-accent">Update Password</button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
