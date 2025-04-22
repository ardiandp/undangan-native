<?php
require_once __DIR__ . '/admin_auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/admin_navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?></h2>
        <div class="card mt-4">
            <div class="card-header">
                Dashboard Admin
            </div>
            <div class="card-body">
                <p>Anda login sebagai: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
                <p>Terakhir login: <?php echo $admin['terakhir_login'] ?? 'Belum pernah login'; ?></p>
                
                <div class="mt-4">
                    <a href="undangan.php" class="btn btn-primary">Kelola Undangan</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>