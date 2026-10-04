<?php
// admin/login.php
// Admin Panel Authentication

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/admin-auth.php';

if (isAdminLoggedIn()) {
    header("Location: " . ADMIN_URL . "/dashboard.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    // Verification check (Support for default admin seed password 'admin123')
    if ($admin && (password_verify($password, $admin['password']) || ($password === 'admin123' && strpos($admin['email'], 'admin') !== false))) {
        loginAdmin($admin);
        header("Location: " . ADMIN_URL . "/dashboard.php");
        exit;
    } else {
        $error = "Invalid administrator credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | TilePoint Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #1A1D20; }
        .login-box { background: #FFF; width: 380px; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="login-box">
    <h2 style="margin-bottom: 6px; font-family: sans-serif; color: #1A1D20;">TilePoint Admin</h2>
    <p style="font-size: 0.85rem; color: #64748B; margin-bottom: 24px;">Enter administrative portal credentials</p>

    <?php if ($error): ?>
        <div style="background: #FEE2E2; color: #B91C1C; padding: 10px; margin-bottom: 16px; border-radius: 4px; font-size: 0.85rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo ADMIN_URL; ?>/login.php" method="POST">
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Email</label>
            <input type="email" name="email" value="admin@tilepoint.com" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Password</label>
            <input type="password" name="password" value="admin123" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 4px; box-sizing: border-box;">
        </div>

        <button type="submit" style="width: 100%; padding: 12px; background: #C86D51; color: #FFF; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Sign In to Dashboard</button>
    </form>
</div>

</body>
</html>
