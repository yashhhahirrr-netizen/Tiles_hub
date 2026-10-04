<?php
// api/cart.php
// AJAX Cart API Handler

header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDBConnection();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId && empty($_SESSION['guest_session_id'])) {
    $_SESSION['guest_session_id'] = session_id();
}
$sessionId = $_SESSION['guest_session_id'] ?? session_id();

// Helper to get or create cart ID
function getOrCreateCartId($db, $userId, $sessionId) {
    if ($userId) {
        $stmt = $db->prepare("SELECT id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();
        if ($cart) return $cart['id'];

        $stmt = $db->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        return $db->lastInsertId();
    } else {
        $stmt = $db->prepare("SELECT id FROM cart WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$sessionId]);
        $cart = $stmt->fetch();
        if ($cart) return $cart['id'];

        $stmt = $db->prepare("INSERT INTO cart (session_id) VALUES (?)");
        $stmt->execute([$sessionId]);
        return $db->lastInsertId();
    }
}

if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $boxes = max(1, (int)($_POST['boxes'] ?? 1));

    $stmt = $db->prepare("SELECT id, price, discount_price, stock_quantity, coverage_per_box FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Tile product not found or unavailable.']);
        exit;
    }

    if ($boxes > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Requested box quantity exceeds available stock (' . $product['stock_quantity'] . ' boxes remaining).']);
        exit;
    }

    $cartId = getOrCreateCartId($db, $userId, $sessionId);
    $pricePerSqFt = (!empty($product['discount_price']) && $product['discount_price'] > 0) ? $product['discount_price'] : $product['price'];
    $pricePerBox = getBoxPrice($pricePerSqFt, $product['coverage_per_box']);
    $coverageSqFt = $boxes * $product['coverage_per_box'];

    // Check if item exists in cart
    $stmt = $db->prepare("SELECT id, boxes FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newBoxes = $existing['boxes'] + $boxes;
        if ($newBoxes > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more boxes. Total exceeds available stock.']);
            exit;
        }
        $newCoverage = $newBoxes * $product['coverage_per_box'];
        $stmt = $db->prepare("UPDATE cart_items SET boxes = ?, coverage_sqft = ?, price_per_box = ? WHERE id = ?");
        $stmt->execute([$newBoxes, $newCoverage, $pricePerBox, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart_items (cart_id, product_id, boxes, coverage_sqft, price_per_box) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cartId, $productId, $boxes, $coverageSqFt, $pricePerBox]);
    }

    $totals = getCartTotals();
    echo json_encode([
        'success' => true,
        'message' => 'Tile added to cart.',
        'cart_count' => $totals['total_boxes'],
        'grand_total' => formatPrice($totals['grand_total'])
    ]);
    exit;
}

if ($action === 'update') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $boxes = max(1, (int)($_POST['boxes'] ?? 1));

    $stmt = $db->prepare("SELECT ci.id, ci.cart_id, ci.product_id, p.stock_quantity, p.coverage_per_box, p.price, p.discount_price 
                          FROM cart_items ci 
                          JOIN products p ON ci.product_id = p.id 
                          WHERE ci.id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit;
    }

    if ($boxes > $item['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Only ' . $item['stock_quantity'] . ' boxes available in stock.']);
        exit;
    }

    $pricePerSqFt = (!empty($item['discount_price']) && $item['discount_price'] > 0) ? $item['discount_price'] : $item['price'];
    $pricePerBox = getBoxPrice($pricePerSqFt, $item['coverage_per_box']);
    $coverageSqFt = $boxes * $item['coverage_per_box'];

    $stmt = $db->prepare("UPDATE cart_items SET boxes = ?, coverage_sqft = ?, price_per_box = ? WHERE id = ?");
    $stmt->execute([$boxes, $coverageSqFt, $pricePerBox, $itemId]);

    $totals = getCartTotals($_SESSION['coupon_code'] ?? null);
    echo json_encode([
        'success' => true,
        'totals' => $totals
    ]);
    exit;
}

if ($action === 'remove') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$itemId]);

    $totals = getCartTotals($_SESSION['coupon_code'] ?? null);
    echo json_encode([
        'success' => true,
        'totals' => $totals
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
