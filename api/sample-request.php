<?php
// api/sample-request.php
// AJAX Tile Sample Request API Handler

header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';

$productId = (int)($_POST['product_id'] ?? 0);
$name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

if (!$productId || empty($name) || empty($phone) || empty($address) || empty($city) || empty($pincode)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all mandatory address fields.']);
    exit;
}

$db = getDBConnection();
$userId = $_SESSION['user_id'] ?? null;

$stmt = $db->prepare("INSERT INTO sample_requests (user_id, product_id, customer_name, phone, address, city, state, pincode, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')");
$stmt->execute([$userId, $productId, $name, $phone, $address, $city, $state, $pincode]);

echo json_encode([
    'success' => true,
    'message' => 'Tile sample request submitted! Our concierge will dispatch your specimen box shortly.'
]);
