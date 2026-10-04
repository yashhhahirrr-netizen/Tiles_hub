# TilePoint — Premium Tiles & Surfaces E-Commerce Website

TilePoint is a production-grade, architectural luxury e-commerce platform built for modern tile manufacturers, surface importers, and interior specifiers. It features a complete PHP 8+ and MySQL backend, high-definition responsive frontend styling, dynamic tile box coverage calculations, cut-tile specimen sample requests, multi-step checkout with Cash on Delivery (COD) and Online Payment, real-time shipment tracking, pure-PHP PDF tax invoice generation, and a separate administrative management portal.

---

## 🚀 Key Features

### Customer Experience & Public Storefront
- **Architectural Surface Aesthetic**: Premium Cormorant Garamond & Inter typography pairing with restrained ivory, sand, and stone grey color palettes.
- **Dynamic Tile Coverage & Box Calculator**: Input room dimensions (length & width) and wastage factor (5-15%) to calculate exact sq. ft. requirements and required box counts.
- **Tile Catalog & Multi-Criteria Filtering**: Filter by category, tile type (Vitrified, Porcelain, Ceramic, Marble Finish), material, surface finish (Glossy, Matt, Satin), color, size, and room application.
- **Specimen Sample Box Requests**: Customers can request real cut-tile specimen boxes delivered to their site before placing bulk orders.
- **Shopping Cart & Box Quantity Validation**: Real-time server-side inventory validation preventing orders beyond available box stock.
- **Multi-Step Checkout & Order Summary**: Supports Cash on Delivery (COD) and Online Payment with Razorpay gateway architecture.
- **Real-Time Visual Order Tracking**: Visual stepper timeline showing order status from `Confirmed` -> `Processing` -> `Packed` -> `Shipped` -> `Out for Delivery` -> `Delivered`.
- **Pure-PHP PDF Invoice Engine**: Download real PDF tax invoices built with a zero-dependency bundled pure-PHP FPDF engine.
- **Customer Account Portal**: Overview, order history, sample dispatches, address book, and security settings.

### Administrative Control Panel (`/admin/`)
- **Dashboard Analytics**: Real-time database metrics for total revenue, total orders, pending dispatches, tile catalog count, low stock warnings, and pending sample requests.
- **Tile Product CRUD**: Add, edit, update inventory box stock, and upload multi-image tile listings.
- **Category CRUD**: Manage surface categories with image uploads.
- **Order Fulfillment Management**: View order details, update shipping tracking numbers, and modify order/payment status.
- **Sample Request Management**: Approve, reject, dispatch, and assign tracking numbers to specimen sample requests.
- **Coupon Code Management**: Create percentage or fixed discount promo codes with minimum order limits.
- **Customer & Review Moderation**: View registered clients and moderate customer product reviews.
- **Store Configuration**: Manage store address, GST tax rate, shipping fees, and free delivery thresholds.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3 (Vanilla), Vanilla JavaScript (No React, Vue, or Angular)
- **Backend**: PHP 8+ (Session security, CSRF protection, password hashing, prepared SQL statements)
- **Database**: MySQL (PDO engine with foreign key constraints)
- **PDF Engine**: Pure-PHP FPDF 1.84 (Zero external binary dependencies)
- **Target Server**: Apache / XAMPP / GitHub Codespaces

---

## 📥 XAMPP Installation & Setup Guide

### 1. Install XAMPP
Ensure XAMPP with PHP 8.0+ and MySQL is installed on your computer.

### 2. Deployment Directory
Copy the project folder into your XAMPP `htdocs` folder:
```
C:\xampp\htdocs\tile-store\
```

### 3. Database Import
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open your web browser and go to phpMyAdmin:
   `http://localhost/phpmyadmin/`
3. Click **New** and create a database named `tile_store` (Collation: `utf8mb4_unicode_ci`).
4. Click on the `tile_store` database, select the **Import** tab.
5. Choose the file `database.sql` from your project root and click **Import**.

### 4. Database Connection Configuration
Check `config/database.php` to verify your local database settings:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tile_store');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Accessing the Website
- **Public Customer Website**:
  `http://localhost/tile-store/`
- **Admin Management Panel**:
  `http://localhost/tile-store/admin/`

---

## 🔑 Default Credentials

### Administrator Portal
- **URL**: `http://localhost/tile-store/admin/`
- **Email**: `admin@tilepoint.com`
- **Password**: `admin123`

### Sample Customer Account
- **URL**: `http://localhost/tile-store/login.php`
- **Email**: `customer@example.com`
- **Password**: `customer123`

---

## 🌐 GitHub Codespaces & Local Server Setup

To run using PHP's built-in CLI web server:
```bash
php -S localhost:8000
```
Then navigate to:
- Public Store: `http://localhost:8000/`
- Admin Portal: `http://localhost:8000/admin/`

---

## 📁 Directory Architecture

```
tile-store/
├── config/
│   ├── database.php        # PDO Database connection
│   ├── app.php             # Core app constants & dynamic base URL
│   └── payment.php          # Razorpay & COD settings
├── includes/
│   ├── header.php          # Document head & top navbar
│   ├── footer.php          # Site footer & script includes
│   ├── navbar.php          # Central navigation bar
│   ├── auth.php            # Customer authentication helper
│   ├── admin-auth.php      # Admin authentication helper
│   ├── functions.php       # Calculations, cart totals & utilities
│   ├── csrf.php           # CSRF token security
│   └── fpdf.php           # Pure-PHP PDF Generation Engine
├── api/
│   ├── cart.php            # Cart operations AJAX handler
│   ├── wishlist.php        # Wishlist AJAX handler
│   ├── sample-request.php  # Sample request submission
│   └── payment.php         # Payment verification
├── assets/
│   ├── css/
│   │   ├── style.css       # Public design system stylesheet
│   │   └── admin.css       # Admin dashboard stylesheet
│   └── js/
│       ├── main.js         # Interactive JS & toast notifications
│       └── calculator.js   # Tile calculator engine
├── admin/
│   ├── login.php           # Admin login
│   ├── dashboard.php       # Real-time metric analytics dashboard
│   ├── products.php        # Tile CRUD listing
│   ├── product-create.php # Add new tile product
│   ├── product-edit.php   # Edit tile product details
│   ├── categories.php     # Category management
│   ├── orders.php         # Order fulfillment management
│   ├── order-details.php  # Single order details & status update
│   ├── sample-requests.php# Sample dispatch management
│   ├── customers.php       # Registered clients list
│   ├── coupons.php        # Coupon promo management
│   ├── reviews.php        # Review moderation
│   ├── payments.php       # Transaction logs
│   ├── invoices.php       # Invoice lookup
│   └── settings.php       # Store configuration
├── index.php               # Homepage
├── shop.php                # Tile catalog with filters
├── product.php             # Tile details & specifications
├── tile-calculator.php     # Standalone tile calculator
├── sample-request.php      # Cut-sample request page
├── cart.php                # Shopping cart & coverage info
├── checkout.php            # Multi-step checkout
├── payment.php             # Online payment gateway page
├── order-success.php       # Order confirmation
├── track-order.php         # Visual shipment timeline
├── account.php             # Customer dashboard
├── invoice.php             # Printable & PDF invoice generator
├── database.sql            # Database schema & realistic sample data
└── README.md               # Project documentation
```

---

## 🔒 Security Best Practices

- Prepared SQL statements using PDO across 100% of database queries.
- Password hashing using `PASSWORD_BCRYPT`.
- CSRF token validation on sensitive form actions.
- Output escaping using `htmlspecialchars()` to prevent XSS.
- Authorization checks blocking unauthorized customers from accessing third-party invoices.

---

## 📜 License & Copyright

© 2026 TilePoint Premium Tiles & Surfaces. All Rights Reserved.
