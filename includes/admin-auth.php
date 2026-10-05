<?php
// includes/admin-auth.php
// Admin Panel Authentication Helper Functions

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function getLoggedInAdmin() {
    if (!isAdminLoggedIn()) return null;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, full_name, email, role FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['flash_error'] = "Admin authentication required.";
        header("Location: " . ADMIN_URL . "/login.php");
        exit;
    }
}

function loginAdmin($admin) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
}

function logoutAdmin() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_role']);
    session_regenerate_id(true);
}
