<?php
// admin/logout.php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/admin-auth.php';

logoutAdmin();
header("Location: " . ADMIN_URL . "/login.php");
exit;
