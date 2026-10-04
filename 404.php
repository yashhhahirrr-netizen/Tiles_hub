<?php
// 404.php
require_once __DIR__ . '/includes/header.php';
?>
<div class="container section-padding" style="text-align: center;">
    <h1 class="font-heading" style="font-size: 5rem; color: var(--color-accent);">404</h1>
    <h2 class="font-heading" style="margin-bottom: 16px;">Tile Surface Not Found</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 24px;">The page or tile product you are searching for has been moved or does not exist.</p>
    <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary">Return to Tile Collections</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
