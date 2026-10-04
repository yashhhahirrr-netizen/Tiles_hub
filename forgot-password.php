<?php
// forgot-password.php
// Password Reset Request Workflow

require_once __DIR__ . '/includes/header.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        $resetLink = BASE_URL . "/reset-password.php?token=" . $token;
        $message = "Password reset link generated successfully! Demo reset link: <a href='{$resetLink}' style='color: var(--color-accent); font-weight: bold;'>Click Here to Reset Password</a>";
    } else {
        $error = "No active account found with that email address.";
    }
}
?>

<div class="container section-padding">
    <div style="max-width: 460px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <span class="eyebrow" style="text-align: center; display: block;">Account Recovery</span>
        <h1 class="font-heading" style="text-align: center; font-size: 2rem; margin-bottom: 24px;">Forgot Password</h1>

        <?php if ($message): ?>
            <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #B91C1C; padding: 12px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/forgot-password.php" method="POST">
            <div class="form-group">
                <label class="form-label">Registered Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="customer@example.com" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Send Reset Instructions</button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.88rem;">
            <a href="<?php echo BASE_URL; ?>/login.php" style="color: var(--color-accent);">&larr; Back to Login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
