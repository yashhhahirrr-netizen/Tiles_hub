<?php
// reset-password.php
// Reset Password Verification & New Password Setter

require_once __DIR__ . '/includes/header.php';

$token = trim($_GET['token'] ?? '');
$db = getDBConnection();
$user = null;

if (!empty($token)) {
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires >= NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $success = true;
    }
}
?>

<div class="container section-padding">
    <div style="max-width: 460px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <h1 class="font-heading" style="text-align: center; font-size: 2rem; margin-bottom: 24px;">Reset Password</h1>

        <?php if ($success): ?>
            <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem; text-align: center;">
                Password reset successful! <a href="<?php echo BASE_URL; ?>/login.php" style="font-weight: bold; color: #15803D;">Click here to Login</a>
            </div>
        <?php elseif (!$user): ?>
            <div style="background: #FEE2E2; color: #B91C1C; padding: 14px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem; text-align: center;">
                Invalid or expired reset token. Please request a new reset link.
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="background: #FEE2E2; color: #B91C1C; padding: 12px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/reset-password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Set New Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
