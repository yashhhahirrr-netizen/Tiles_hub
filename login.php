<?php
// login.php
// Customer Authentication & Login Page

require_once __DIR__ . '/config/app.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/account.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    $db   = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        loginUser($user);
        $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/account.php';
        unset($_SESSION['redirect_after_login']);
        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Invalid email address or password.";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="max-width: 440px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <span class="eyebrow" style="text-align: center; display: block;">Client Portal Access</span>
        <h1 class="font-heading" style="text-align: center; font-size: 2rem; margin-bottom: 24px;">Sign In to TilePoint</h1>

        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #B91C1C; padding: 12px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/login.php" method="POST">
            <?php echo renderCSRFField(); ?>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="customer@example.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <label class="form-label" style="margin: 0;">Password</label>
                    <a href="<?php echo BASE_URL; ?>/forgot-password.php" style="font-size: 0.8rem; color: var(--color-accent);">Forgot Password?</a>
                </div>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.88rem; color: var(--color-text-muted);">
            Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php" style="color: var(--color-accent); font-weight: 600;">Register Here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
