<?php
// includes/auth.php
// Customer Authentication Helper Functions

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, full_name, email, phone, created_at FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Please log in to continue.";
        header("Location: " . BASE_URL . "/" . ltrim($redirect, '/'));
        exit;
    }
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    
    // Transfer guest cart to logged in user if exists
    if (!empty($_SESSION['guest_session_id'])) {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$user['id'], $_SESSION['guest_session_id']]);
    }
}

function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    session_regenerate_id(true);
}
