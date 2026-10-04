<?php
// faq.php
// Frequently Asked Questions

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Customer Guidance</span>
        <h1 class="font-heading">Frequently Asked Questions</h1>
    </div>

    <div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
        <div style="background: var(--color-card); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 8px;">How are tile box quantities calculated?</h3>
            <p style="color: var(--color-text-muted); font-size: 0.925rem;">Every tile product listing specifies the exact square footage coverage per box (e.g. 15.5 sq. ft. per box). When using our Tile Calculator, we add 10% for cutting wastage and divide by coverage per box to calculate required boxes.</p>
        </div>

        <div style="background: var(--color-card); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 8px;">How does TilePoint deliver heavy tile crates?</h3>
            <p style="color: var(--color-text-muted); font-size: 0.925rem;">Tile shipments are packed in wooden palletized crates with corner guards and delivered via heavy freight transport trucks directly to your specified site address.</p>
        </div>

        <div style="background: var(--color-card); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 8px;">What if tiles break during transport?</h3>
            <p style="color: var(--color-text-muted); font-size: 0.925rem;">All shipments carry 100% Transit Insurance. In the rare event of transit breakage, photograph the unopened damaged box upon delivery and notify us for immediate free box replacement.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
