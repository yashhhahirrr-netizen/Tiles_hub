<?php
// config/app.php
// Central Application Settings for TilePoint

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'TilePoint');
define('APP_TAGLINE', 'PREMIUM TILES & SURFACES');

// Dynamic Base URL detection for XAMPP / Codespaces / Custom Port
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

// Normalize base URL
if (strpos($script_dir, '/admin') === 0 || strpos($script_dir, '/admin') !== false) {
    $script_dir = preg_replace('/\/admin.*$/', '', $script_dir);
}
define('BASE_URL', $protocol . $host . ($script_dir !== '' ? $script_dir : ''));
define('ADMIN_URL', BASE_URL . '/admin');

// Application Constants
define('CURRENCY_SYMBOL', '₹');
define('DEFAULT_GST_RATE', 18); // 18% GST on Tiles
define('DEFAULT_SHIPPING_FEE', 499.00);
define('FREE_SHIPPING_THRESHOLD', 25000.00);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
