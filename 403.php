<?php
// 403.php
require_once __DIR__ . '/includes/header.php';
?>
<div class="container section-padding" style="text-align: center;">
    <h1 class="font-heading" style="font-size: 5rem; color: var(--color-danger);">403</h1>
    <h2 class="font-heading" style="margin-bottom: 16px;">Access Forbidden</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 24px;">You do not have administrative authorization to view this document or resource.</p>
    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Return to Home</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
