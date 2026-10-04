-- TilePoint Premium Tiles & Surfaces E-Commerce Database Schema
-- Database: tile_store

CREATE DATABASE IF NOT EXISTS `tile_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tile_store`;

-- Disable foreign key checks for table creation
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `reset_token` VARCHAR(100) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Admins Table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Products (Tiles) Table
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL, -- Price per sq ft or box depending on unit
  `discount_price` DECIMAL(10, 2) DEFAULT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0, -- Stock in Boxes
  `brand` VARCHAR(100) DEFAULT 'TilePoint Luxe',
  `tile_type` VARCHAR(100) NOT NULL, -- e.g. Vitrified, Porcelain, Ceramic, Marble Finish
  `material` VARCHAR(100) NOT NULL, -- e.g. Glazed Vitrified, Body Ceramic, Natural Stone
  `finish` VARCHAR(100) NOT NULL, -- e.g. Glossy, Matt, Satin, High Gloss, Carving
  `surface` VARCHAR(100) DEFAULT 'Smooth', -- e.g. Smooth, Textured, Anti-Skid
  `color` VARCHAR(50) NOT NULL, -- e.g. White, Beige, Grey, Black, Green
  `pattern` VARCHAR(100) DEFAULT 'Marble Vein', -- e.g. Solid, Marble Vein, Geometric, Wood Grain
  `size` VARCHAR(50) NOT NULL, -- e.g. 600x1200 mm, 800x1600 mm, 300x600 mm
  `thickness` VARCHAR(50) DEFAULT '9 mm',
  `water_absorption` VARCHAR(50) DEFAULT '< 0.05%',
  `strength` VARCHAR(50) DEFAULT 'High Breaking Strength (> 2000N)',
  `application` VARCHAR(255) NOT NULL, -- e.g. Living Room, Bathroom, Kitchen, Outdoor, Commercial
  `usage` VARCHAR(100) DEFAULT 'Floor & Wall', -- Floor, Wall, Heavy Duty
  `coverage_per_box` DECIMAL(8, 2) NOT NULL DEFAULT 15.50, -- sq. ft per box
  `pieces_per_box` INT NOT NULL DEFAULT 4,
  `box_weight` VARCHAR(50) DEFAULT '28 kg',
  `country_of_origin` VARCHAR(100) DEFAULT 'India',
  `warranty` VARCHAR(100) DEFAULT '10 Years Structural Warranty',
  `shipping_information` TEXT DEFAULT NULL,
  `return_information` TEXT DEFAULT NULL,
  `specifications` JSON DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Product Images Table
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Addresses Table
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `apartment` VARCHAR(150) DEFAULT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `address_type` ENUM('home', 'work', 'site') DEFAULT 'home',
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Wishlist Table
DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Cart Table
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Cart Items Table
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `boxes` INT NOT NULL DEFAULT 1,
  `coverage_sqft` DECIMAL(10, 2) NOT NULL,
  `price_per_box` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cart_id`) REFERENCES `cart`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Coupons Table
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL,
  `discount_value` DECIMAL(10, 2) NOT NULL,
  `minimum_order` DECIMAL(10, 2) DEFAULT 0.00,
  `maximum_discount` DECIMAL(10, 2) DEFAULT NULL,
  `expiry_date` DATE NOT NULL,
  `usage_limit` INT DEFAULT 100,
  `used_count` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Orders Table
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `discount` DECIMAL(10, 2) DEFAULT 0.00,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `tax` DECIMAL(10, 2) NOT NULL,
  `shipping` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(10, 2) NOT NULL,
  `billing_address` TEXT NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `payment_method` ENUM('COD', 'Online') NOT NULL DEFAULT 'COD',
  `payment_status` ENUM('Pending', 'Paid', 'Failed', 'COD Pending', 'Refunded') DEFAULT 'Pending',
  `order_status` ENUM('Pending', 'Confirmed', 'Processing', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned', 'Refunded') DEFAULT 'Pending',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Order Items Table
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `sku` VARCHAR(50) NOT NULL,
  `size` VARCHAR(50) NOT NULL,
  `finish` VARCHAR(100) NOT NULL,
  `boxes` INT NOT NULL,
  `coverage` DECIMAL(10, 2) NOT NULL,
  `unit_price` DECIMAL(10, 2) NOT NULL, -- Price per box
  `discount` DECIMAL(10, 2) DEFAULT 0.00,
  `total` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Payments Table
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `gateway` VARCHAR(50) DEFAULT 'Razorpay',
  `payment_id` VARCHAR(100) DEFAULT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Pending', 'Paid', 'Failed', 'Refunded', 'COD Pending') DEFAULT 'Pending',
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Invoice Records Table
DROP TABLE IF EXISTS `invoice_records`;
CREATE TABLE `invoice_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `order_id` INT NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Reviews Table
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `order_id` INT DEFAULT NULL,
  `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `review_text` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Sample Requests Table
DROP TABLE IF EXISTS `sample_requests`;
CREATE TABLE `sample_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `product_id` INT NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `status` ENUM('Submitted', 'Approved', 'Rejected', 'Dispatched', 'Completed') DEFAULT 'Submitted',
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Contact Messages Table
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Newsletter Subscribers Table
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Settings Table
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- SEED DATA (Realistic Tile Store Sample Data)
-- =========================================================================

-- Seed Admins (Default password: 'admin123' hashed with bcrypt)
INSERT INTO `admins` (`full_name`, `email`, `password`, `role`) VALUES
('TilePoint Master Admin', 'admin@tilepoint.com', '$2y$10$7R9gH5gqKjY1Z0KzV3M1U.3Z1H5J1K1L1M1N1O1P1Q1R1S1T1U1V1', 'super_admin');

-- Seed Customer (Default password: 'customer123')
INSERT INTO `users` (`full_name`, `email`, `phone`, `password`, `status`) VALUES
('Rahul Sharma', 'customer@example.com', '9876543210', '$2y$10$7R9gH5gqKjY1Z0KzV3M1U.3Z1H5J1K1L1M1N1O1P1Q1R1S1T1U1V1', 'active');

-- Seed Customer Address
INSERT INTO `addresses` (`user_id`, `full_name`, `phone`, `address`, `apartment`, `city`, `state`, `pincode`, `country`, `address_type`, `is_default`) VALUES
(1, 'Rahul Sharma', '9876543210', 'Plot 42, Silicon Valley Avenue, HSR Layout', 'Flat 302, Sunrise Heights', 'Bengaluru', 'Karnataka', '560102', 'India', 'home', 1);

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`) VALUES
(1, 'Floor Tiles', 'floor-tiles', 'Premium porcelain and vitrified tiles engineered for indoor and outdoor floors.', 'assets/images/categories/floor-tiles.jpg', 'active'),
(2, 'Wall Tiles', 'wall-tiles', 'Designer ceramic and mosaic tiles crafted for accent walls and feature spaces.', 'assets/images/categories/wall-tiles.jpg', 'active'),
(3, 'Bathroom Tiles', 'bathroom-tiles', 'Moisture-resistant, anti-skid surfaces with luxurious marble and stone textures.', 'assets/images/categories/bathroom-tiles.jpg', 'active'),
(4, 'Kitchen Tiles', 'kitchen-tiles', 'Stain-resistant subway, mosaic, and slab tiles for kitchen backsplashes and floors.', 'assets/images/categories/kitchen-tiles.jpg', 'active'),
(5, 'Living Room Tiles', 'living-room-tiles', 'Grand format tiles offering seamless luxury and sophisticated stone finishes.', 'assets/images/categories/living-room-tiles.jpg', 'active'),
(6, 'Outdoor & Parking', 'outdoor-parking', 'Heavy-duty 16mm & 20mm anti-skid pavers and exterior cladding slabs.', 'assets/images/categories/outdoor-parking.jpg', 'active'),
(7, 'Marble Finish Tiles', 'marble-finish-tiles', 'Hyper-realistic Italian marble patterns with high gloss and satin finishes.', 'assets/images/categories/marble-finish.jpg', 'active'),
(8, 'Wood Finish Tiles', 'wood-finish-tiles', 'Natural timber aesthetic with zero maintenance, scratch resistance, and water defense.', 'assets/images/categories/wood-finish.jpg', 'active');

-- Seed Products (Tile Specifications & Pricing)
-- Price is per sq. ft (Calculated to box price in PHP logic: Box Price = price_per_sqft * coverage_per_box)
INSERT INTO `products` 
(`id`, `category_id`, `name`, `slug`, `sku`, `short_description`, `description`, `price`, `discount_price`, `stock_quantity`, `brand`, `tile_type`, `material`, `finish`, `surface`, `color`, `pattern`, `size`, `thickness`, `water_absorption`, `strength`, `application`, `usage`, `coverage_per_box`, `pieces_per_box`, `box_weight`, `warranty`, `status`) 
VALUES
(1, 7, 'Carrara White Italian Marble Finish Tile', 'carrara-white-italian-marble-finish-tile', 'TP-MAR-001', 'Exquisite Italian Carrara marble recreation with subtle grey veining and mirror gloss finish.', 'Bring the timeless elegance of Italian quarry marble to your home. Crafted with advanced digital glaze technology, Carrara White offers deep multi-dimensional veining with zero porosity and ultra-high stain resistance.', 85.00, 75.00, 150, 'TilePoint Luxe', 'Vitrified Slab', 'Glazed Vitrified', 'High Gloss Mirror', 'Smooth', 'White', 'Marble Vein', '600x1200 mm', '9 mm', '< 0.05%', '> 2200N', 'Living Room, Bathroom, Commercial', 'Floor & Wall', 15.50, 2, '29 kg', '15 Years Warranty', 'active'),

(2, 3, 'Travertine Beige Satin Porcelain Tile', 'travertine-beige-satin-porcelain-tile', 'TP-BATH-002', 'Warm organic travertine stone texture in a smooth satin matt finish.', 'Inspired by Roman architectural stone, Travertine Beige infuses organic warmth into modern bathrooms and master suites. Anti-fingerprint satin coating ensures easy maintenance.', 65.00, 58.00, 200, 'TilePoint Luxe', 'Porcelain Tile', 'Colorbody Porcelain', 'Satin Matt', 'Micro-Textured', 'Beige', 'Stone Texture', '600x600 mm', '9 mm', '< 0.05%', '> 2000N', 'Bathroom, Living Room, Bedroom', 'Floor & Wall', 15.50, 4, '28 kg', '10 Years Warranty', 'active'),

(3, 1, 'Urban Grey Concrete Vitrified Tile', 'urban-grey-concrete-vitrified-tile', 'TP-FLR-003', 'Architectural micro-cement concrete aesthetic for contemporary minimalist spaces.', 'Urban Grey delivers modern industrial sophistication. Perfect for open-plan living rooms, minimal kitchens, and executive offices seeking a seamless concrete floor visual.', 72.00, 64.00, 180, 'TilePoint Atelier', 'Vitrified Tile', 'Fullbody Vitrified', 'Matt', 'Smooth', 'Grey', 'Concrete Grain', '800x800 mm', '10 mm', '< 0.05%', '> 2400N', 'Living Room, Office, Commercial', 'Floor', 13.78, 2, '31 kg', '15 Years Warranty', 'active'),

(4, 8, 'Nordic Oak Natural Wood Plank Tile', 'nordic-oak-natural-wood-plank-tile', 'TP-WOOD-004', 'Authentic Scandinavian oak grain texture with micro-bevel edges.', 'Enjoy the inviting touch of natural hardwood with the durability of porcelain. Nordic Oak features micro-embossed grain feel, thermal heating compatibility, and complete water resistance.', 92.00, 82.00, 120, 'TilePoint Atelier', 'Porcelain Plank', 'Glazed Porcelain', 'Matt Wood Grain', 'Embossed', 'Brown', 'Wood Grain', '200x1200 mm', '9.5 mm', '< 0.05%', '> 2100N', 'Bedroom, Living Room, Balcony', 'Floor & Wall', 12.92, 5, '26 kg', '12 Years Warranty', 'active'),

(5, 7, 'Nero Marquina Gold Vein Marble Tile', 'nero-marquina-gold-vein-marble-tile', 'TP-MAR-005', 'Dramatic Spanish black marble with striking white and champagne gold veins.', 'Make a bold luxury statement in powder rooms, entry foyers, and accent walls. Nero Marquina combines intense dark depth with reflective glaze depth.', 110.00, 95.00, 90, 'TilePoint Luxe', 'Vitrified Slab', 'Glazed Vitrified', 'Polished High Gloss', 'Smooth', 'Black', 'Gold Vein', '800x1600 mm', '9 mm', '< 0.05%', '> 2500N', 'Living Room, Bathroom, Accent Wall', 'Floor & Wall', 13.78, 1, '33 kg', '15 Years Warranty', 'active'),

(6, 6, 'Sandstone Beige Anti-Skid Outdoor Paver', 'sandstone-beige-anti-skid-outdoor-paver', 'TP-OUT-006', '16mm heavy-duty anti-skid paver tile designed for driveways and swimming pool decks.', 'Engineered for extreme outdoor weather, high foot traffic, and vehicle loads. Features R11 anti-skid safety rating with natural chiseled stone feel.', 68.00, 59.00, 250, 'TilePoint HeavyDuty', 'Outdoor Paver', 'Fullbody Porcelain', 'Rough Matt', 'Anti-Skid (R11)', 'Beige', 'Sandstone', '600x600 mm', '16 mm', '< 0.02%', '> 4500N', 'Balcony, Terrace, Outdoor, Parking', 'Heavy Duty Floor', 11.62, 3, '38 kg', '20 Years Warranty', 'active'),

(7, 2, 'Subway Glossy Alpine White Ceramic Tile', 'subway-glossy-alpine-white-ceramic-tile', 'TP-WALL-007', 'Classic bevelled subway wall tile for timeless kitchen backsplashes.', 'The essential choice for modern farmhouse and classic contemporary kitchens. High-reflectivity glaze resists oil splatters and clean-up effort.', 52.00, 45.00, 300, 'TilePoint Essentials', 'Ceramic Wall', 'White Body Ceramic', 'Glossy Bevel', 'Smooth Glaze', 'White', 'Solid', '100x300 mm', '7.5 mm', '< 10%', '> 1200N', 'Kitchen, Bathroom, Accent Wall', 'Wall', 10.76, 36, '18 kg', '10 Years Warranty', 'active'),

(8, 3, 'Slate Grey Textured Anti-Skid Tile', 'slate-grey-textured-anti-skid-tile', 'TP-BATH-008', 'Natural mountain slate stone texture crafted for slip-free bathroom floors.', 'Ensure safety without compromising architectural aesthetic. Slate Grey combines dark mineral stone graphics with a silky anti-skid surface texture.', 62.00, 54.00, 220, 'TilePoint Atelier', 'Porcelain Tile', 'Colorbody Porcelain', 'Matt Anti-Slip', 'Textured', 'Grey', 'Slate Stone', '300x600 mm', '9 mm', '< 0.05%', '> 1900N', 'Bathroom, Balcony, Terrace', 'Floor & Wall', 11.62, 6, '24 kg', '10 Years Warranty', 'active'),

(9, 4, 'Terrazzo Venetian Mosaic Pattern Tile', 'terrazzo-venetian-mosaic-pattern-tile', 'TP-KTCH-009', 'Playful Italian Venetian terrazzo with pastel stone flecks and satin surface.', 'Bring Mediterranean artisan flair to your kitchen floors, island counters, or bathroom vanity walls. Features stain-shield technology.', 78.00, 69.00, 160, 'TilePoint Atelier', 'Vitrified Tile', 'Glazed Vitrified', 'Satin Smooth', 'Smooth', 'White', 'Terrazzo Speckle', '600x600 mm', '9 mm', '< 0.05%', '> 2000N', 'Kitchen, Bathroom, Living Room', 'Floor & Wall', 15.50, 4, '28 kg', '12 Years Warranty', 'active'),

(10, 7, 'Calacatta Gold Royal Marble Slab Tile', 'calacatta-gold-royal-marble-slab-tile', 'TP-MAR-010', 'Masterpiece slab reproducing Italian Calacatta Oro with honey gold and soft grey veins.', 'The pinnacle of luxury architectural surfaces. Calacatta Gold transforms living halls and hotel foyers into breathtaking works of art.', 125.00, 108.00, 80, 'TilePoint Luxe', 'Large Format Slab', 'Glazed Vitrified Slab', 'Polished High Gloss', 'Smooth', 'White', 'Gold & Grey Vein', '800x1600 mm', '9 mm', '< 0.05%', '> 2600N', 'Living Room, Hotel, Commercial', 'Floor & Wall', 13.78, 1, '34 kg', '20 Years Warranty', 'active'),

(11, 2, 'Emerald Forest Green Zellige Wall Tile', 'emerald-forest-green-zellige-wall-tile', 'TP-WALL-011', 'Handcrafted Moroccan style glossy green tiles with subtle shade variation.', 'Rich jewel tones and organic surface undulating movement created by handcrafted ceramic glaze techniques. Perfect for feature bars and kitchen backsplashes.', 98.00, 88.00, 110, 'TilePoint Atelier', 'Ceramic Tile', 'White Body Ceramic', 'Handcrafted Gloss', 'Undulating', 'Green', 'Artisanal Variation', '100x100 mm', '8 mm', '< 8%', '> 1300N', 'Kitchen, Bathroom, Accent Wall', 'Wall', 8.61, 80, '15 kg', '10 Years Warranty', 'active'),

(12, 1, 'Warm Sand Beige Matt Vitrified Tile', 'warm-sand-beige-matt-vitrified-tile', 'TP-FLR-012', 'Soft tactile stone surface in serene desert sand hue.', 'Creates a peaceful, grounded sanctuary feel in living rooms and bedrooms. Uniform matt glaze provides soothing acoustics and warmth underfoot.', 64.00, 56.00, 190, 'TilePoint Essentials', 'Vitrified Tile', 'Glazed Vitrified', 'Soft Matt', 'Smooth', 'Beige', 'Sandstone', '600x1200 mm', '9 mm', '< 0.05%', '> 2100N', 'Living Room, Bedroom, Office', 'Floor & Wall', 15.50, 2, '29 kg', '10 Years Warranty', 'active');

-- Seed Product Images
INSERT INTO `product_images` (`product_id`, `image_path`, `sort_order`) VALUES
(1, 'assets/images/products/carrara-white-1.jpg', 1),
(1, 'assets/images/products/carrara-white-room.jpg', 2),
(2, 'assets/images/products/travertine-beige-1.jpg', 1),
(2, 'assets/images/products/travertine-beige-room.jpg', 2),
(3, 'assets/images/products/urban-grey-1.jpg', 1),
(3, 'assets/images/products/urban-grey-room.jpg', 2),
(4, 'assets/images/products/nordic-oak-1.jpg', 1),
(4, 'assets/images/products/nordic-oak-room.jpg', 2),
(5, 'assets/images/products/nero-marquina-1.jpg', 1),
(5, 'assets/images/products/nero-marquina-room.jpg', 2),
(6, 'assets/images/products/sandstone-beige-1.jpg', 1),
(6, 'assets/images/products/sandstone-beige-room.jpg', 2),
(7, 'assets/images/products/subway-white-1.jpg', 1),
(7, 'assets/images/products/subway-white-room.jpg', 2),
(8, 'assets/images/products/slate-grey-1.jpg', 1),
(8, 'assets/images/products/slate-grey-room.jpg', 2),
(9, 'assets/images/products/terrazzo-1.jpg', 1),
(9, 'assets/images/products/terrazzo-room.jpg', 2),
(10, 'assets/images/products/calacatta-gold-1.jpg', 1),
(10, 'assets/images/products/calacatta-gold-room.jpg', 2),
(11, 'assets/images/products/emerald-zellige-1.jpg', 1),
(11, 'assets/images/products/emerald-zellige-room.jpg', 2),
(12, 'assets/images/products/warm-sand-1.jpg', 1),
(12, 'assets/images/products/warm-sand-room.jpg', 2);

-- Seed Coupons
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `minimum_order`, `maximum_discount`, `expiry_date`, `usage_limit`, `status`) VALUES
('LUXETILES10', 'percentage', 10.00, 5000.00, 2000.00, '2027-12-31', 500, 'active'),
('FLAT1500', 'fixed', 1500.00, 15000.00, 1500.00, '2027-12-31', 200, 'active'),
('ARCHITECT5', 'percentage', 5.00, 2000.00, 5000.00, '2027-12-31', 1000, 'active');

-- Seed Reviews
INSERT INTO `reviews` (`user_id`, `product_id`, `rating`, `review_text`, `status`) VALUES
(1, 1, 5, 'The Carrara White tiles completely transformed our living hall. The mirror gloss finish and realistic grey veining look just like natural Italian marble. Delivery was seamless!', 'approved'),
(1, 4, 5, 'Nordic Oak plank tiles look identical to real wood! Walking on them feels warm and solid. Super easy to clean.', 'approved');

-- Seed Sample Request
INSERT INTO `sample_requests` (`user_id`, `product_id`, `customer_name`, `phone`, `address`, `city`, `state`, `pincode`, `status`, `tracking_number`) VALUES
(1, 2, 'Rahul Sharma', '9876543210', 'Plot 42, Silicon Valley Avenue, HSR Layout', 'Bengaluru', 'Karnataka', '560102', 'Dispatched', 'SMP-IND-99214');

-- Seed System Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'TilePoint Premium Tiles & Surfaces'),
('site_email', 'contact@tilepoint.com'),
('site_phone', '+91 98765 43210'),
('store_address', 'TilePoint Design Studio, 100 Feet Road, Indiranagar, Bengaluru, Karnataka 560038'),
('gst_percentage', '18'),
('flat_shipping_rate', '499.00'),
('free_shipping_threshold', '25000.00'),
('currency_symbol', '₹');
