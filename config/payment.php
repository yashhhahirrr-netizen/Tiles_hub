<?php
// config/payment.php
// Centralized Payment Gateway Settings for TilePoint

define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_tilepoint_demo_key');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'rzp_test_secret_key_demo');
define('PAYMENT_CURRENCY', 'INR');

define('ENABLE_COD', true);
define('ENABLE_ONLINE_PAYMENT', true);
