<?php
// includes/footer.php
// Central Footer Component for TilePoint
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo" style="margin-bottom: 16px;">
                    <span class="logo-main" style="color: #FFFFFF;">TILEPOINT</span>
                    <span class="logo-sub">PREMIUM TILES & SURFACES</span>
                </a>
                <p style="color: var(--color-text-light); font-size: 0.9rem; line-height: 1.7; max-width: 340px;">
                    TilePoint is a premier architectural surfaces brand delivering high-definition glazed vitrified slabs, porcelain planks, and anti-skid outdoor pavers to discerning homeowners and interior designers.
                </p>
                <div style="margin-top: 20px; font-size: 0.85rem; color: var(--color-gold);">
                    📍 Indiranagar Studio: 100 Feet Rd, Bengaluru<br>
                    📞 Phone: +91 98765 43210<br>
                    ✉️ Concierge: concierge@tilepoint.com
                </div>
            </div>

            <div class="footer-col">
                <h4>Collections</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=marble-finish-tiles">Italian Marble Finish</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=floor-tiles">Vitrified Floor Slabs</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=bathroom-tiles">Anti-Skid Bathroom Surfaces</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=kitchen-tiles">Kitchen Backsplash Tiles</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=wood-finish-tiles">Natural Wood Planks</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php?category=outdoor-parking">Heavy Duty Outdoor Pavers</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Architect Tools</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/tile-calculator.php">Tile Area & Box Calculator</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/sample-request.php">Order Tile Samples</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/room-guide.php">Room Application Guide</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/track-order.php">Track Order Status</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about.php">About TilePoint</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/contact.php">Contact Design Concierge</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Store Policies</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/faq.php">Frequently Asked Questions</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/privacy.php">Privacy & Data Policy</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/terms.php">Terms & Conditions</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shipping-policy.php">Shipping & Delivery Policy</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/return-policy.php">Returns & Breakage Guarantee</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                &copy; <?php echo date('Y'); ?> TilePoint Premium Tiles & Surfaces. All Rights Reserved.
            </div>
            <div>
                Crafted with PHP 8, MySQL & Vanilla JS
            </div>
        </div>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/calculator.js"></script>
</body>
</html>
