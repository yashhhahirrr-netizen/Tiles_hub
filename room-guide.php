<?php
// room-guide.php
// Architectural Application & Room Specifier Guide

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Space Planning</span>
        <h1 class="font-heading">Tile Specification Guide By Room</h1>
        <p class="subtitle">Understand technical requirements like PEI abrasion ratings, R-ratings for slip resistance, and body porosity for every living zone.</p>
    </div>

    <div class="category-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
        <div style="background: var(--color-card); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <span class="eyebrow">Living & Reception Halls</span>
            <h2 class="font-heading" style="margin-bottom: 12px;">Grand Vitrified Slabs</h2>
            <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 20px;">
                Large format 800x1600mm or 600x1200mm Glazed Vitrified Tiles (GVT) offer unbroken marble mirror reflections. Extremely high breaking strength (&gt; 2200N) handles heavy furniture easily.
            </p>
            <a href="<?php echo BASE_URL; ?>/shop.php?application=Living+Room" class="btn btn-outline btn-sm">Explore Living Room Tiles &rarr;</a>
        </div>

        <div style="background: var(--color-card); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <span class="eyebrow">Master Bathrooms</span>
            <h2 class="font-heading" style="margin-bottom: 12px;">Anti-Skid & Waterproof</h2>
            <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 20px;">
                Floor tiles must utilize R10 or R11 anti-skid surface treatments for wet floor safety. Pair with non-porous ceramic wall tiles for zero mold buildup around shower zones.
            </p>
            <a href="<?php echo BASE_URL; ?>/shop.php?application=Bathroom" class="btn btn-outline btn-sm">Explore Bathroom Tiles &rarr;</a>
        </div>

        <div style="background: var(--color-card); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <span class="eyebrow">Kitchen Backsplash & Floors</span>
            <h2 class="font-heading" style="margin-bottom: 12px;">Stain-Shield Surfaces</h2>
            <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 20px;">
                Subway tiles, bevelled ceramics, and Venetian Terrazzo glazes resist oil splashes, turmeric stains, and acidic cleaner solutions with instant wipeability.
            </p>
            <a href="<?php echo BASE_URL; ?>/shop.php?application=Kitchen" class="btn btn-outline btn-sm">Explore Kitchen Tiles &rarr;</a>
        </div>

        <div style="background: var(--color-card); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <span class="eyebrow">Driveways & Balconies</span>
            <h2 class="font-heading" style="margin-bottom: 12px;">16mm Heavy Duty Pavers</h2>
            <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 20px;">
                For exterior terraces and parking, fullbody 16mm or 20mm porcelain pavers withstand extreme temperature variations, vehicle pressure, and monsoon water pooling.
            </p>
            <a href="<?php echo BASE_URL; ?>/shop.php?application=Outdoor" class="btn btn-outline btn-sm">Explore Outdoor Pavers &rarr;</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
