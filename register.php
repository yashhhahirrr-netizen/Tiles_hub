<?php
// register.php
// Customer Registration Page

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/account.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with this email address already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$fullName, $email, $phone, $hash]);
            $userId = $db->lastInsertId();

            loginUser(['id' => $userId, 'full_name' => $fullName, 'email' => $email]);
            header("Location: " . BASE_URL . "/account.php");
            exit;
        }
    }
}
?>

<div class="container section-padding">
    <div style="max-width: 480px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <span class="eyebrow" style="text-align: center; display: block;">New Client Registration</span>
        <h1 class="font-heading" style="text-align: center; font-size: 2rem; margin-bottom: 24px;">Create Account</h1>

        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #B91C1C; padding: 12px; margin-bottom: 20px; border-radius: var(--radius-sm); font-size: 0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/register.php" method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Rahul Sharma" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="customer@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="9876543210">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.88rem; color: var(--color-text-muted);">
            Already have an account? <a href="<?php echo BASE_URL; ?>/login.php" style="color: var(--color-accent); font-weight: 600;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
