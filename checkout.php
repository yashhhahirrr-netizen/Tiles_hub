<?php
// checkout.php
// Multi-Step Tile Checkout & Order Placement Engine

require_once __DIR__ . '/config/app.php';
requireLogin('checkout.php');

// Validate cart is not empty BEFORE HTML output
$totals = getCartTotals($_SESSION['coupon_code'] ?? null);
if (empty($totals['items'])) {
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}



$currentUser = getLoggedInUser();
$db = getDBConnection();

// Fetch customer saved addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$currentUser['id']]);
$addresses = $stmt->fetchAll();

$totals = getCartTotals($_SESSION['coupon_code'] ?? null);

if (empty($totals['items'])) {
    header("Location: " . BASE_URL . "/cart.php");
    exit;
}

$error = null;

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $error = "CSRF Token Validation Failed. Please try submitting again.";
    } else {
        $paymentMethod = $_POST['payment_method'] ?? 'COD';
        // address_option is either 'saved' (radio selected) or 'new' (manual form)
        $addressOption = $_POST['address_option'] ?? 'new';

        $shippingAddressStr = '';
        $billingAddressStr  = '';

        if ($addressOption === 'saved') {
            $addressId = (int)($_POST['address_id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $currentUser['id']]);
            $addrObj = $stmt->fetch();

            if (!$addrObj) {
                $error = "Please select a valid saved shipping address, or enter a new one below.";
            } else {
                $shippingAddressStr = "{$addrObj['full_name']}\nPhone: {$addrObj['phone']}\n{$addrObj['address']}"
                    . ($addrObj['apartment'] ? ", {$addrObj['apartment']}" : "")
                    . "\n{$addrObj['city']}, {$addrObj['state']} - {$addrObj['pincode']}\nIndia";
                $billingAddressStr = $shippingAddressStr;
            }
        } else {
            // 'new' address
            $fullName = trim($_POST['full_name'] ?? '');
            $phone    = trim($_POST['phone']     ?? '');
            $addr     = trim($_POST['address']   ?? '');
            $apt      = trim($_POST['apartment'] ?? '');
            $city     = trim($_POST['city']      ?? '');
            $state    = trim($_POST['state']     ?? '');
            $pincode  = trim($_POST['pincode']   ?? '');

            if (empty($fullName) || empty($phone) || empty($addr) || empty($city) || empty($pincode)) {
                $error = "Please fill in all required shipping address fields (Name, Phone, Address, City, Pincode).";
            } else {
                $shippingAddressStr = "{$fullName}\nPhone: {$phone}\n{$addr}"
                    . ($apt ? ", {$apt}" : "")
                    . "\n{$city}, {$state} - {$pincode}\nIndia";
                $billingAddressStr = $shippingAddressStr;

                // Optionally save address to account
                if (!empty($_POST['save_address'])) {
                    $stmt = $db->prepare("INSERT INTO addresses (user_id, full_name, phone, address, apartment, city, state, pincode, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'India')");
                    $stmt->execute([$currentUser['id'], $fullName, $phone, $addr, $apt, $city, $state, $pincode]);
                }
            }
        }

        // Process order if no errors
        if (!$error) {
            try {
                $db->beginTransaction();

                // 1. Re-verify stock & server-side calculations
                foreach ($totals['items'] as $item) {
                    $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");
                    $stmt->execute([$item['product_id']]);
                    $currentStock = $stmt->fetchColumn();

                    if ($item['boxes'] > $currentStock) {
                        throw new Exception("Tile '{$item['name']}' only has {$currentStock} boxes remaining in stock.");
                    }
                }

                // 2. Generate Order Number
                $orderNumber   = 'TP-ORD-' . date('Ymd') . '-' . rand(1000, 9999);
                $paymentStatus = ($paymentMethod === 'COD') ? 'COD Pending' : 'Pending';
                $orderStatus   = 'Confirmed';

                // 3. Insert Order
                $stmt = $db->prepare("INSERT INTO orders
                    (order_number, user_id, subtotal, discount, coupon_code, tax, shipping, grand_total,
                     billing_address, shipping_address, payment_method, payment_status, order_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $orderNumber, $currentUser['id'],
                    $totals['subtotal'], $totals['discount'],
                    $_SESSION['coupon_code'] ?? null,
                    $totals['tax'], $totals['shipping'], $totals['grand_total'],
                    $billingAddressStr, $shippingAddressStr,
                    $paymentMethod, $paymentStatus, $orderStatus
                ]);
                $orderId = $db->lastInsertId();

                // 4. Insert Order Items & Update Stock
                foreach ($totals['items'] as $item) {
                    $effectiveSqFtPrice = (!empty($item['discount_price']) && $item['discount_price'] > 0)
                        ? $item['discount_price'] : $item['price_per_sqft'];
                    $boxPrice  = getBoxPrice($effectiveSqFtPrice, $item['coverage_per_box']);
                    $itemTotal = $boxPrice * $item['boxes'];

                    $stmt = $db->prepare("INSERT INTO order_items
                        (order_id, product_id, product_name, sku, size, finish, boxes, coverage, unit_price, total)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $orderId, $item['product_id'], $item['name'], $item['sku'],
                        $item['size'], $item['finish'], $item['boxes'],
                        $item['coverage_per_box'] * $item['boxes'], $boxPrice, $itemTotal
                    ]);

                    $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                    $stmt->execute([$item['boxes'], $item['product_id']]);
                }

                // 5. Insert Payment Record
                $stmt = $db->prepare("INSERT INTO payments (order_id, user_id, payment_method, gateway, amount, status) VALUES (?, ?, ?, 'System', ?, ?)");
                $stmt->execute([$orderId, $currentUser['id'], $paymentMethod, $totals['grand_total'], $paymentStatus]);

                // 6. Create Invoice Record
                $invoiceNum = 'INV-' . date('Ym') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
                $stmt = $db->prepare("INSERT INTO invoice_records (invoice_number, order_id, user_id) VALUES (?, ?, ?)");
                $stmt->execute([$invoiceNum, $orderId, $currentUser['id']]);

                // 7. Update Coupon Usage
                if (!empty($totals['applied_coupon'])) {
                    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
                    $stmt->execute([$totals['applied_coupon']['id']]);
                }

                // 8. Clear Cart & Coupon
                $stmt = $db->prepare("DELETE ci FROM cart_items ci JOIN cart c ON ci.cart_id = c.id WHERE c.user_id = ?");
                $stmt->execute([$currentUser['id']]);
                unset($_SESSION['coupon_code']);

                $db->commit();

                if ($paymentMethod === 'Online') {
                    header("Location: " . BASE_URL . "/payment.php?order_id=" . $orderId);
                } else {
                    header("Location: " . BASE_URL . "/order-success.php?order_id=" . $orderId);
                }
                exit;

            } catch (Exception $e) {
                $db->rollBack();
                $error = "Order Placement Failed: " . $e->getMessage();
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Secure Order Checkout</span>
        <h1 class="font-heading">Checkout &amp; Address Verification</h1>
    </div>

    <?php if ($error): ?>
        <div style="background: var(--color-bg-alt); border-left: 4px solid var(--color-danger); padding: 16px; margin-bottom: 24px; border-radius: var(--radius-sm); color: var(--color-danger);">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>/checkout.php" method="POST" id="checkout-form">
        <?php echo renderCSRFField(); ?>

        <!-- Hidden field that tracks which address mode is active -->
        <input type="hidden" name="address_option" id="address_option_field" value="<?php echo empty($addresses) ? 'new' : 'saved'; ?>">

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px; align-items: start;">
            <!-- Left: Address & Payment -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

                <!-- Address Card -->
                <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <h3 class="font-heading" style="margin-bottom: 20px;">1. Delivery Address</h3>

                    <?php if (!empty($addresses)): ?>
                        <!-- Tab switcher -->
                        <div style="display: flex; gap: 0; margin-bottom: 20px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); overflow: hidden;">
                            <button type="button" id="tab-saved" onclick="switchAddressTab('saved')"
                                style="flex:1; padding: 10px 16px; border: none; background: var(--color-accent); color: #fff; cursor: pointer; font-weight: 600; transition: background .2s;">
                                Saved Addresses
                            </button>
                            <button type="button" id="tab-new" onclick="switchAddressTab('new')"
                                style="flex:1; padding: 10px 16px; border: none; background: transparent; color: var(--color-text); cursor: pointer; font-weight: 600; transition: background .2s;">
                                + New Address
                            </button>
                        </div>

                        <!-- Saved addresses panel -->
                        <div id="panel-saved">
                            <?php foreach ($addresses as $idx => $addr): ?>
                                <label style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); margin-bottom: 12px; cursor: pointer;">
                                    <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>" <?php echo $idx === 0 ? 'checked' : ''; ?>>
                                    <div>
                                        <strong><?php echo htmlspecialchars($addr['full_name']); ?></strong>
                                        (<?php echo htmlspecialchars($addr['phone']); ?>)
                                        <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px;">
                                            <?php echo htmlspecialchars($addr['address']); ?>,
                                            <?php echo htmlspecialchars($addr['city']); ?>,
                                            <?php echo htmlspecialchars($addr['state']); ?> -
                                            <?php echo htmlspecialchars($addr['pincode']); ?>
                                        </p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- New address panel (hidden initially) -->
                        <div id="panel-new" style="display:none;">
                            <?php include __DIR__ . '/includes/checkout-new-address-fields.php'; ?>
                        </div>

                    <?php else: ?>
                        <!-- No saved addresses - always show new address form -->
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 16px;">Enter your delivery address below:</p>
                        <?php include __DIR__ . '/includes/checkout-new-address-fields.php'; ?>
                    <?php endif; ?>
                </div>

                <!-- Payment Method Card -->
                <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <h3 class="font-heading" style="margin-bottom: 20px;">2. Choose Payment Method</h3>

                    <label style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); margin-bottom: 12px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <div>
                            <strong>Cash on Delivery (COD)</strong>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted);">Pay upon heavy crate delivery at your construction / home site.</p>
                        </div>
                    </label>

                    <label style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); cursor: pointer;">
                        <input type="radio" name="payment_method" value="Online">
                        <div>
                            <strong>Online Payment (UPI / Card / NetBanking / Razorpay)</strong>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted);">Instant verified checkout with official tax invoice generation.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border); position: sticky; top: 100px;">
                <h3 class="font-heading" style="margin-bottom: 20px;">Tile Order Summary</h3>

                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 20px;">
                    <?php foreach ($totals['items'] as $item):
                        $effectiveSqFtPrice = (!empty($item['discount_price']) && $item['discount_price'] > 0) ? $item['discount_price'] : $item['price_per_sqft'];
                        $boxPrice = getBoxPrice($effectiveSqFtPrice, $item['coverage_per_box']);
                    ?>
                        <div style="display: flex; justify-content: space-between; font-size: 0.88rem;">
                            <div>
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <div style="color: var(--color-text-muted);"><?php echo $item['boxes']; ?> Boxes (<?php echo round($item['coverage_per_box'] * $item['boxes'], 2); ?> sq.ft)</div>
                            </div>
                            <strong><?php echo formatPrice($boxPrice * $item['boxes']); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem; margin-bottom: 20px; border-bottom: 1px solid var(--color-border); padding-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span><span><?php echo formatPrice($totals['subtotal']); ?></span>
                    </div>
                    <?php if ($totals['discount'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; color: var(--color-success);">
                            <span>Discount:</span><span>-<?php echo formatPrice($totals['discount']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span>GST (18%):</span><span><?php echo formatPrice($totals['tax']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Freight Delivery:</span>
                        <span><?php echo $totals['shipping'] > 0 ? formatPrice($totals['shipping']) : 'FREE'; ?></span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 700; color: var(--color-primary); margin-bottom: 24px;">
                    <span>Grand Total:</span>
                    <span><?php echo formatPrice($totals['grand_total']); ?></span>
                </div>

                <button type="submit" class="btn btn-accent btn-lg btn-block">Place Tile Order Now</button>
            </div>
        </div>
    </form>
</div>

<script>
function switchAddressTab(mode) {
    document.getElementById('address_option_field').value = mode;
    if (mode === 'saved') {
        document.getElementById('panel-saved').style.display = 'block';
        document.getElementById('panel-new').style.display   = 'none';
        document.getElementById('tab-saved').style.background = 'var(--color-accent)';
        document.getElementById('tab-saved').style.color     = '#fff';
        document.getElementById('tab-new').style.background  = 'transparent';
        document.getElementById('tab-new').style.color       = 'var(--color-text)';
    } else {
        document.getElementById('panel-saved').style.display = 'none';
        document.getElementById('panel-new').style.display   = 'block';
        document.getElementById('tab-saved').style.background = 'transparent';
        document.getElementById('tab-saved').style.color     = 'var(--color-text)';
        document.getElementById('tab-new').style.background  = 'var(--color-accent)';
        document.getElementById('tab-new').style.color       = '#fff';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

