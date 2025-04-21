<div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <img src="../assets/images/admin/default-profile.jpg" class="rounded-circle mb-2" width="80" height="80">
            <h6 class="text-white mb-1"><?= htmlspecialchars($current_admin['nama_lengkap']) ?></h6>
            <small class="text-muted"><?= ucfirst($current_admin['level']) ?></small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'undangan.php' ? 'active' : '' ?>" href="undangan.php">
                    <i class="fas fa-envelope"></i> Undangan
                </a>
            </li>
            <!-- Tambahkan menu lainnya -->
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>