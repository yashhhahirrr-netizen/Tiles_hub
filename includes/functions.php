<?php
// includes/functions.php
// Utility & Helper Functions for TilePoint

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function formatPrice($amount) {
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

function setFlash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type = null) {
    if ($type !== null) {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// Calculate Box Price based on Sq Ft price and coverage per box
function getBoxPrice($pricePerSqFt, $coveragePerBox) {
    return round((float)$pricePerSqFt * (float)$coveragePerBox, 2);
}

// Tile Area Calculator helper
function calculateTileRequirement($length, $width, $unit, $wastagePercent = 10, $coveragePerBox = 15.5) {
    // Convert room dimensions to feet
    $lengthFt = ($unit === 'meter' || $unit === 'm') ? $length * 3.28084 : (($unit === 'inch' || $unit === 'in') ? $length / 12 : $length);
    $widthFt = ($unit === 'meter' || $unit === 'm') ? $width * 3.28084 : (($unit === 'inch' || $unit === 'in') ? $width / 12 : $width);
    
    $rawAreaSqFt = $lengthFt * $widthFt;
    $wastageAreaSqFt = $rawAreaSqFt * ($wastagePercent / 100);
    $totalAreaSqFt = $rawAreaSqFt + $wastageAreaSqFt;
    
    $boxesNeeded = ceil($totalAreaSqFt / $coveragePerBox);
    $actualCoverageProvided = $boxesNeeded * $coveragePerBox;

    return [
        'raw_area_sqft' => round($rawAreaSqFt, 2),
        'wastage_percent' => (float)$wastagePercent,
        'wastage_area_sqft' => round($wastageAreaSqFt, 2),
        'total_area_required' => round($totalAreaSqFt, 2),
        'boxes_needed' => (int)$boxesNeeded,
        'actual_coverage_provided' => round($actualCoverageProvided, 2)
    ];
}

// Get Cart Items for Session or User
function getCartItems() {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;

    if (!$userId && !$sessionId) {
        $_SESSION['guest_session_id'] = session_id();
        $sessionId = $_SESSION['guest_session_id'];
    }

    if ($userId) {
        $stmt = $db->prepare("SELECT c.id AS cart_id, ci.id AS item_id, ci.product_id, ci.boxes, ci.coverage_sqft, ci.price_per_box,
                                     p.name, p.slug, p.sku, p.size, p.finish, p.price AS price_per_sqft, p.discount_price, p.coverage_per_box, p.stock_quantity,
                                     (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image
                              FROM cart c
                              JOIN cart_items ci ON c.id = ci.cart_id
                              JOIN products p ON ci.product_id = p.id
                              WHERE c.user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT c.id AS cart_id, ci.id AS item_id, ci.product_id, ci.boxes, ci.coverage_sqft, ci.price_per_box,
                                     p.name, p.slug, p.sku, p.size, p.finish, p.price AS price_per_sqft, p.discount_price, p.coverage_per_box, p.stock_quantity,
                                     (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) AS image
                              FROM cart c
                              JOIN cart_items ci ON c.id = ci.cart_id
                              JOIN products p ON ci.product_id = p.id
                              WHERE c.session_id = ? AND c.user_id IS NULL");
        $stmt->execute([$sessionId]);
    }
    return $stmt->fetchAll();
}

function getCartTotals($couponCode = null) {
    $items = getCartItems();
    $subtotal = 0.00;
    $totalBoxes = 0;
    $totalSqFt = 0.00;

    foreach ($items as $item) {
        $effectiveSqFtPrice = (!empty($item['discount_price']) && $item['discount_price'] > 0) ? $item['discount_price'] : $item['price_per_sqft'];
        $boxPrice = getBoxPrice($effectiveSqFtPrice, $item['coverage_per_box']);
        $itemSubtotal = $boxPrice * $item['boxes'];
        
        $subtotal += $itemSubtotal;
        $totalBoxes += $item['boxes'];
        $totalSqFt += ($item['coverage_per_box'] * $item['boxes']);
    }

    $discount = 0.00;
    $appliedCoupon = null;

    if ($couponCode && $subtotal > 0) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE()");
        $stmt->execute([strtoupper(trim($couponCode))]);
        $coupon = $stmt->fetch();

        if ($coupon) {
            if ($subtotal >= $coupon['minimum_order']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discount = ($subtotal * $coupon['discount_value']) / 100;
                    if ($coupon['maximum_discount'] && $discount > $coupon['maximum_discount']) {
                        $discount = (float)$coupon['maximum_discount'];
                    }
                } else {
                    $discount = (float)$coupon['discount_value'];
                }
                $appliedCoupon = $coupon;
            }
        }
    }

    $taxable = max(0, $subtotal - $discount);
    $tax = ($taxable * DEFAULT_GST_RATE) / 100;
    
    $shipping = 0.00;
    if ($subtotal > 0 && $subtotal < FREE_SHIPPING_THRESHOLD) {
        $shipping = DEFAULT_SHIPPING_FEE;
    }

    $grandTotal = $taxable + $tax + $shipping;

    return [
        'items' => $items,
        'item_count' => count($items),
        'total_boxes' => $totalBoxes,
        'total_sqft' => round($totalSqFt, 2),
        'subtotal' => round($subtotal, 2),
        'discount' => round($discount, 2),
        'applied_coupon' => $appliedCoupon,
        'tax' => round($tax, 2),
        'shipping' => round($shipping, 2),
        'grand_total' => round($grandTotal, 2)
    ];
}

function isInWishlist($userId, $productId) {
    if (!$userId) return false;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return (bool)$stmt->fetch();
}

function uploadFile($file, $targetDir, $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg']) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error code: ' . ($file['error'] ?? 'unknown')];
    }
    
    $fileInfo = pathinfo($file['name']);
    $ext = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($ext, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file extension. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    // Check size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = uniqid('tile_', true) . '.' . $ext;
    $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $targetPath];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file.'];
}
