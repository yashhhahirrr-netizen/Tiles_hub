<?php
// payment.php
// Online Payment Gateway Interface & Server-Side Verification

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/payment.php';

requireLogin('payment.php');

$orderId = (int)($_GET['order_id'] ?? 0);
$db = getDBConnection();
$currentUser = getLoggedInUser();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $currentUser['id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: " . BASE_URL . "/account.php");
    exit;
}

$error = null;

// Handle Online Payment Form Submission / Mock Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = 'pay_tilepoint_' . bin2hex(random_bytes(8));
    $txnId = 'TXN-' . date('YmdHis') . '-' . rand(100, 999);

    try {
        $db->beginTransaction();

        // Update Payment Record
        $stmt = $db->prepare("UPDATE payments SET status = 'Paid', payment_id = ?, transaction_id = ?, paid_at = NOW() WHERE order_id = ?");
        $stmt->execute([$paymentId, $txnId, $orderId]);

        // Update Order Status
        $stmt = $db->prepare("UPDATE orders SET payment_status = 'Paid', order_status = 'Confirmed', transaction_id = ? WHERE id = ?");
        $stmt->execute([$txnId, $orderId]);

        $db->commit();

        header("Location: " . BASE_URL . "/order-success.php?order_id=" . $orderId);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Payment Verification Error: " . $e->getMessage();
    }
}
?>

<div class="container section-padding">
    <div style="max-width: 600px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <span class="eyebrow" style="color: var(--color-accent);">Payment Gateway</span>
        <h1 class="font-heading" style="margin-bottom: 16px;">Complete Online Payment</h1>
        
        <div style="background: var(--color-bg-alt); padding: 20px; border-radius: var(--radius-md); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Order Reference:</span>
                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; color: var(--color-primary);">
                <span>Amount Payable:</span>
                <span><?php echo formatPrice($order['grand_total']); ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div style="background: var(--color-bg-alt); border-left: 4px solid var(--color-danger); padding: 14px; margin-bottom: 20px; color: var(--color-danger);">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/payment.php?order_id=<?php echo $orderId; ?>" method="POST">
            <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 20px;">
                This environment is connected to Razorpay Test / TilePoint Gateway. Click below to verify transaction authorization.
            </p>
            <button type="submit" class="btn btn-accent btn-lg btn-block">Pay <?php echo formatPrice($order['grand_total']); ?> Now</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
