<?php
// api/wishlist.php
// AJAX Wishlist API Handler

header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your wishlist.']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int)($_POST['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$db = getDBConnection();
$stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$userId, $productId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $db->prepare("DELETE FROM wishlist WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $added = false;
    $message = "Removed from wishlist.";
} else {
    $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
    $added = true;
    $message = "Saved to your wishlist!";
}

$stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt->execute([$userId]);
$count = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'added' => $added,
    'message' => $message,
    'wishlist_count' => $count
]);
