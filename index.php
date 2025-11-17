<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Platform</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/app/assets/css/main.css">
</head>
<body>
    
    <!-- Header Component -->
    <?php include __DIR__ . '/app/components/shared/header.html'; ?>
    
    <!-- Navigation Component -->
    <?php include __DIR__ . '/app/components/shared/nav.html'; ?>
    
    <!-- Login Component -->
    <?php include __DIR__ . '/app/components/login/login.html'; ?>
    
    <!-- Register Component -->
    <?php include __DIR__ . '/app/components/register/register.html'; ?>
    
    <!-- Seller Dashboard Component -->
    <?php include __DIR__ . '/app/components/seller/seller.html'; ?>
    
    <!-- Buyer Dashboard Component -->
    <?php include __DIR__ . '/app/components/buyer/buyer.html'; ?>
    
    <!-- Admin Dashboard Component -->
    <?php include __DIR__ . '/app/components/admin/admin.html'; ?>
    
    <!-- Footer Component -->
    <?php include __DIR__ . '/app/components/shared/footer.html'; ?>
    
    <!-- Shared Modal Component -->
    <?php include __DIR__ . '/app/components/shared/modal.html'; ?>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Shared Utilities -->
    <script src="/app/assets/js/utils.js"></script>
    
    <!-- Shared Modal Functions -->
    <script src="/app/components/shared/modal.js"></script>
    
    <!-- Header and Nav Functions -->
    <script src="/app/components/shared/header.js"></script>
    
    <!-- Main Application -->
    <script src="/app/assets/js/app.js"></script>
    
    <!-- Component Scripts -->
    <script src="/app/components/login/login.js"></script>
    <script src="/app/components/register/register.js"></script>
    <script src="/app/components/seller/seller.js"></script>
    <script src="/app/components/buyer/buyer.js"></script>
    <script src="/app/components/admin/admin.js"></script>
    
</body>
</html>