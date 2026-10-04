<?php
// about.php
// About TilePoint Premium Surfaces

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Brand Heritage</span>
        <h1 class="font-heading">About TilePoint</h1>
        <p class="subtitle">Pioneering architectural surface engineering, Italian glazes, and precision vitrified tile manufacturing for elite interior spaces.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; margin-bottom: 60px;">
        <div>
            <h2 class="font-heading" style="margin-bottom: 16px;">Architectural Excellence in Every Slab</h2>
            <p style="color: var(--color-text-muted); line-height: 1.8; margin-bottom: 16px;">
                Founded with a mission to bridge European surface design with industrial durability, TilePoint curates luxury glazed vitrified tiles, fullbody outdoor pavers, and high-gloss Italian marble finish slabs.
            </p>
            <p style="color: var(--color-text-muted); line-height: 1.8;">
                Every tile in our catalogue is manufactured using 1200°C ultra-kiln firing and digital glaze printing, ensuring zero water absorption, stain immunity, and lifetime color fidelity.
            </p>
        </div>
        <div>
            <img src="<?php echo BASE_URL; ?>/assets/images/products/carrara-white-room.jpg" alt="TilePoint Architectural Studio" style="border-radius: var(--radius-lg); width: 100%; box-shadow: var(--shadow-lg);" onerror="this.src='https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'">
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
