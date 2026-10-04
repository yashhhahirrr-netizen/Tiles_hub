<?php
// tile-calculator.php
// Dedicated Tile Calculator Page

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Architectural Utility Tool</span>
        <h1 class="font-heading">Tile Quantity & Box Calculator</h1>
        <p class="subtitle">Accurately calculate room surface area, recommended cutting wastage allowance (5% to 15%), and required box quantities before ordering.</p>
    </div>

    <div class="calculator-card" style="max-width: 900px; margin: 0 auto;">
        <form id="tile-calc-form">
            <div class="calc-form-grid">
                <div class="calc-field">
                    <label>Room Length</label>
                    <input type="number" step="0.1" id="calc-length" value="15" required>
                </div>
                <div class="calc-field">
                    <label>Room Width</label>
                    <input type="number" step="0.1" id="calc-width" value="12" required>
                </div>
                <div class="calc-field">
                    <label>Unit of Measure</label>
                    <select id="calc-unit">
                        <option value="feet" selected>Feet (ft)</option>
                        <option value="meter">Meters (m)</option>
                        <option value="inch">Inches (in)</option>
                    </select>
                </div>
                <div class="calc-field">
                    <label>Tile Coverage per Box</label>
                    <input type="number" step="0.01" id="calc-coverage-per-box" value="15.50" required>
                </div>
                <div class="calc-field">
                    <label>Wastage Factor</label>
                    <select id="calc-wastage">
                        <option value="5">5% (Straight Grid Alignment)</option>
                        <option value="10" selected>10% (Standard Brick / Staggered Layout)</option>
                        <option value="15">15% (Diagonal / Herringbone / Curved Cuts)</option>
                    </select>
                </div>
            </div>

            <div class="calc-result-box">
                <div class="result-stat">
                    <div class="stat-val" id="res-raw-area">180.00 sq. ft.</div>
                    <div class="stat-lbl">Room Surface Area</div>
                </div>
                <div class="result-stat">
                    <div class="stat-val" id="res-wastage-area">18.00 sq. ft.</div>
                    <div class="stat-lbl">Cutting Wastage</div>
                </div>
                <div class="result-stat">
                    <div class="stat-val" id="res-total-area">198.00 sq. ft.</div>
                    <div class="stat-lbl">Total Tile Needed</div>
                </div>
                <div class="result-stat">
                    <div class="stat-val" id="res-boxes-needed">13 Boxes</div>
                    <div class="stat-lbl">Required Box Count</div>
                </div>
            </div>
        </form>
    </div>

    <div style="max-width: 900px; margin: 48px auto 0; text-align: center;">
        <h3 class="font-heading" style="margin-bottom: 12px;">Ready to Spec Your Project?</h3>
        <p style="color: var(--color-text-muted); margin-bottom: 20px;">Explore our catalog with verified box coverage data on every tile product page.</p>
        <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary btn-lg">Browse Tile Catalogue</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
