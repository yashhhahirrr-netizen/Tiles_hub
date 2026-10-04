<?php
// includes/header.php
// Central HTML Header & Document Head

require_once __DIR__ . '/../config/app.php';
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' | ' . APP_TAGLINE;
$metaDescription = isset($metaDescription) ? $metaDescription : 'TilePoint is India\'s premier destination for luxury vitrified tiles, Italian marble finish slabs, porcelain surfaces, and architectural outdoor pavers.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <script>
        window.BASE_URL = "<?php echo BASE_URL; ?>";
        window.CSRF_TOKEN = "<?php echo generateCSRFToken(); ?>";
    </script>
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>
