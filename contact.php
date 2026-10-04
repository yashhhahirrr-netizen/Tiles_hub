<?php
// contact.php
// Contact Concierge Page

require_once __DIR__ . '/includes/header.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $msg = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($msg)) {
        $error = "Please fill in all mandatory fields.";
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $msg]);
        $message = "Thank you! Your message has been sent to our Tile Concierge team. We will respond within 24 hours.";
    }
}
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Design Concierge</span>
        <h1 class="font-heading">Get in Touch With TilePoint</h1>
        <p class="subtitle">Visit our Indiranagar design studio or submit an architectural project specification query below.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; max-width: 1000px; margin: 0 auto;">
        <div style="background: var(--color-primary); color: #FFF; padding: 40px; border-radius: var(--radius-lg);">
            <h3 class="font-heading" style="color: #FFF; margin-bottom: 20px;">Design Studio</h3>
            <p style="color: var(--color-bg-alt); font-size: 0.9rem; line-height: 1.7; margin-bottom: 24px;">
                📍 TilePoint Studio, 100 Feet Road, Indiranagar, Bengaluru, Karnataka 560038
            </p>
            <p style="color: var(--color-bg-alt); font-size: 0.9rem; line-height: 1.7; margin-bottom: 24px;">
                📞 Concierge Line: +91 98765 43210<br>
                ✉️ Email: contact@tilepoint.com
            </p>
            <div style="font-size: 0.85rem; color: var(--color-gold);">
                Hours: Mon — Sat: 10:00 AM – 8:00 PM
            </div>
        </div>

        <div style="background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
            <?php if ($message): ?>
                <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 20px; border-radius: var(--radius-sm);">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #FEE2E2; color: #B91C1C; padding: 12px; margin-bottom: 20px; border-radius: var(--radius-sm);">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/contact.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="e.g. Bulk Order Inquiry / Site Specs">
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
