<?php
// invoice.php
// Official Tax Invoice Generator & Secure PDF Downloader

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/fpdf.php';

$orderId = (int)($_GET['id'] ?? 0);
$action = trim($_GET['action'] ?? 'view');
$db = getDBConnection();

// Fetch order
$stmt = $db->prepare("SELECT o.*, i.invoice_number, i.generated_at, u.full_name, u.email, u.phone 
                      FROM orders o 
                      JOIN invoice_records i ON o.id = i.order_id 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Invoice not found.");
}

// Security verification: Logged in customer must own invoice OR admin
$currentUserId = $_SESSION['user_id'] ?? null;
$isAdmin = isAdminLoggedIn();

if (!$isAdmin && (!isLoggedIn() || $currentUserId != $order['user_id'])) {
    http_response_code(403);
    die("Unauthorized Access: You do not have permission to view this invoice.");
}

// Fetch items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Handle PDF Generation Stream
if ($action === 'pdf' || $action === 'download') {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);

    // Header Title
    $pdf->SetFont('Helvetica', 'B', 20);
    $pdf->SetTextColor(61, 53, 46);
    $pdf->Cell(110, 10, 'TILEPOINT', 0, 0, 'L');
    
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->SetTextColor(200, 109, 81);
    $pdf->Cell(70, 10, 'TAX INVOICE', 0, 1, 'R');

    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(110, 104, 95);
    $pdf->Cell(110, 5, 'PREMIUM TILES & SURFACES', 0, 0, 'L');
    $pdf->Cell(70, 5, 'Invoice #: ' . $order['invoice_number'], 0, 1, 'R');

    $pdf->Cell(110, 5, 'Indiranagar Studio, Bengaluru, Karnataka 560038', 0, 0, 'L');
    $pdf->Cell(70, 5, 'Order #: ' . $order['order_number'], 0, 1, 'R');

    $pdf->Cell(110, 5, 'Phone: +91 98765 43210 | GSTIN: 29AAAAA0000A1Z5', 0, 0, 'L');
    $pdf->Cell(70, 5, 'Date: ' . date('d M Y', strtotime($order['generated_at'] ?? $order['created_at'])), 0, 1, 'R');

    $pdf->Ln(8);
    $pdf->SetDrawColor(230, 224, 212);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(6);

    // Customer & Billing Info
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(61, 53, 46);
    $pdf->Cell(90, 6, 'Customer Information:', 0, 0, 'L');
    $pdf->Cell(90, 6, 'Shipping / Site Address:', 0, 1, 'L');

    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor(34, 32, 30);
    $pdf->Cell(90, 5, 'Name: ' . $order['full_name'], 0, 0, 'L');
    $pdf->MultiCell(90, 5, str_replace("\n", ", ", $order['shipping_address']), 0, 'L');

    $pdf->Cell(90, 5, 'Email: ' . $order['email'], 0, 1, 'L');
    $pdf->Cell(90, 5, 'Phone: ' . $order['phone'], 0, 1, 'L');

    $pdf->Ln(6);

    // Table Header
    $pdf->SetFillColor(61, 53, 46);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(75, 8, ' Tile Surface Item', 1, 0, 'L', true);
    $pdf->Cell(25, 8, 'Size/Finish', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Boxes', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Coverage', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Total (INR)', 1, 1, 'R', true);

    // Table Rows
    $pdf->SetTextColor(34, 32, 30);
    $pdf->SetFont('Helvetica', '', 8.5);
    foreach ($items as $it) {
        $pdf->Cell(75, 7, ' ' . substr($it['product_name'], 0, 40), 'LRB', 0, 'L');
        $pdf->Cell(25, 7, substr($it['size'], 0, 14), 'RB', 0, 'C');
        $pdf->Cell(20, 7, $it['boxes'] . ' Box', 'RB', 0, 'C');
        $pdf->Cell(30, 7, $it['coverage'] . ' sq.ft', 'RB', 0, 'C');
        $pdf->Cell(30, 7, number_format($it['total'], 2) . ' ', 'RB', 1, 'R');
    }

    $pdf->Ln(6);

    // Totals Box
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Subtotal:', 0, 0, 'L');
    $pdf->Cell(30, 5, number_format($order['subtotal'], 2), 0, 1, 'R');

    if ($order['discount'] > 0) {
        $pdf->Cell(120, 5, '', 0, 0);
        $pdf->Cell(30, 5, 'Discount:', 0, 0, 'L');
        $pdf->Cell(30, 5, '-' . number_format($order['discount'], 2), 0, 1, 'R');
    }

    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'GST (18%):', 0, 0, 'L');
    $pdf->Cell(30, 5, number_format($order['tax'], 2), 0, 1, 'R');

    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Freight Shipping:', 0, 0, 'L');
    $pdf->Cell(30, 5, number_format($order['shipping'], 2), 0, 1, 'R');

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(120, 7, '', 0, 0);
    $pdf->Cell(30, 7, 'Grand Total:', 'T', 0, 'L');
    $pdf->Cell(30, 7, 'INR ' . number_format($order['grand_total'], 2), 'T', 1, 'R');

    $pdf->Ln(10);
    $pdf->SetFont('Helvetica', 'I', 8);
    $pdf->SetTextColor(110, 104, 95);
    $pdf->Cell(180, 5, 'Thank you for choosing TilePoint Premium Tiles & Surfaces.', 0, 1, 'C');

    $pdf->Output('D', 'TilePoint_Invoice_' . $order['invoice_number'] . '.pdf');
    exit;
}

// HTML View
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - <?php echo htmlspecialchars($order['invoice_number']); ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; padding: 40px; color: #222; max-width: 850px; margin: 0 auto; background: #fff; }
        .header-row { display: flex; justify-content: space-between; border-bottom: 2px solid #3D352E; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: 700; color: #3D352E; letter-spacing: 1px; }
        .sublogo { font-size: 10px; color: #C86D51; letter-spacing: 3px; display: block; }
        .inv-title { text-align: right; color: #C86D51; }
        .inv-title h2 { margin: 0; font-size: 22px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .table th { background: #3D352E; color: #fff; padding: 10px; text-align: left; font-size: 12px; }
        .table td { border-bottom: 1px solid #E6E0D4; padding: 10px; font-size: 13px; }
        .totals { width: 300px; margin-left: auto; margin-top: 24px; font-size: 14px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .actions { margin-top: 40px; text-align: center; }
        .btn { padding: 10px 20px; background: #3D352E; color: #fff; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>

<div class="header-row">
    <div>
        <span class="logo">TILEPOINT</span>
        <span class="sublogo">PREMIUM TILES & SURFACES</span>
        <div style="font-size: 12px; color: #666; margin-top: 8px;">
            Indiranagar Studio, 100 Feet Rd, Bengaluru 560038<br>
            GSTIN: 29AAAAA0000A1Z5 | Phone: +91 98765 43210
        </div>
    </div>
    <div class="inv-title">
        <h2>TAX INVOICE</h2>
        <div style="font-size: 13px; color: #444; margin-top: 6px;">
            Invoice #: <strong><?php echo htmlspecialchars($order['invoice_number']); ?></strong><br>
            Order #: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong><br>
            Date: <strong><?php echo date('d M Y', strtotime($order['generated_at'] ?? $order['created_at'])); ?></strong>
        </div>
    </div>
</div>

<div style="display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 13px;">
    <div>
        <strong>Billed To:</strong><br>
        <?php echo htmlspecialchars($order['full_name']); ?><br>
        Email: <?php echo htmlspecialchars($order['email']); ?><br>
        Phone: <?php echo htmlspecialchars($order['phone']); ?>
    </div>
    <div>
        <strong>Shipping / Site Address:</strong><br>
        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Tile Product</th>
            <th>Size / Finish</th>
            <th>Box Qty</th>
            <th>Coverage</th>
            <th style="text-align: right;">Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($it['product_name']); ?></strong><br>
                    <span style="font-size: 11px; color: #777;">SKU: <?php echo htmlspecialchars($it['sku']); ?></span>
                </td>
                <td><?php echo htmlspecialchars($it['size']); ?> — <?php echo htmlspecialchars($it['finish']); ?></td>
                <td><?php echo $it['boxes']; ?> Boxes</td>
                <td><?php echo $it['coverage']; ?> sq. ft.</td>
                <td style="text-align: right;"><?php echo formatPrice($it['total']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <div class="totals-row">
        <span>Subtotal:</span>
        <span><?php echo formatPrice($order['subtotal']); ?></span>
    </div>
    <?php if ($order['discount'] > 0): ?>
        <div class="totals-row" style="color: green;">
            <span>Discount:</span>
            <span>-<?php echo formatPrice($order['discount']); ?></span>
        </div>
    <?php endif; ?>
    <div class="totals-row">
        <span>GST (18%):</span>
        <span><?php echo formatPrice($order['tax']); ?></span>
    </div>
    <div class="totals-row">
        <span>Freight Shipping:</span>
        <span><?php echo formatPrice($order['shipping']); ?></span>
    </div>
    <div class="totals-row" style="border-top: 2px solid #222; font-weight: bold; font-size: 16px; margin-top: 6px; padding-top: 8px;">
        <span>Grand Total:</span>
        <span><?php echo formatPrice($order['grand_total']); ?></span>
    </div>
</div>

<div class="actions">
    <button onclick="window.print()" class="btn">Print Invoice</button>
    <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $orderId; ?>&action=pdf" class="btn" style="background: #C86D51; margin-left: 10px;">Download PDF</a>
</div>

</body>
</html>
