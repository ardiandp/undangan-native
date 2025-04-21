<?php
require_once __DIR__ . '/admin_auth.php';

// Query untuk statistik
$totalUndangan = $conn->query("SELECT COUNT(*) FROM undangan")->fetch_row()[0];
$totalTamu = $conn->query("SELECT COUNT(*) FROM tamu")->fetch_row()[0];
$totalUcapan = $conn->query("SELECT COUNT(*) FROM ucapan")->fetch_row()[0];
$totalKunjungan = $conn->query("SELECT COUNT(*) FROM hit_counter")->fetch_row()[0];

// Query untuk undangan terbaru
$recentUndangan = $conn->query("SELECT * FROM undangan ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Query untuk ucapan terbaru
$recentUcapan = $conn->query("SELECT u.*, un.judul_undangan FROM ucapan u JOIN undangan un ON u.undangan_id = un.id ORDER BY u.waktu_kirim DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.5);
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="undangan.php">
                                <i class="fas fa-envelope"></i> Undangan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tamu.php">
                                <i class="fas fa-users"></i> Tamu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="galeri.php">
                                <i class="fas fa-images"></i> Galeri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ucapan.php">
                                <i class="fas fa-comments"></i> Ucapan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laporan.php">
                                <i class="fas fa-chart-bar"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h3 mb-4">Dashboard</h2>
                
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-primary stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Undangan</h5>
                                        <h2 class="mb-0"><?= $totalUndangan ?></h2>
                                    </div>
                                    <i class="fas fa-envelope fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-success stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Tamu</h5>
                                        <h2 class="mb-0"><?= $totalTamu ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-info stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Ucapan</h5>
                                        <h2 class="mb-0"><?= $totalUcapan ?></h2>
                                    </div>
                                    <i class="fas fa-comments fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-warning stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Kunjungan</h5>
                                        <h2 class="mb-0"><?= $totalKunjungan ?></h2>
                                    </div>
                                    <i class="fas fa-eye fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Undangan Terbaru -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Undangan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pasangan</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUndangan as $undangan): ?>
                                    <tr>
                                        <td><?= $undangan['id_unik'] ?></td>
                                        <td><?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?></td>
                                        <td><?= date('d M Y', strtotime($undangan['tanggal_akad'])) ?></td>
                                        <td>
                                            <a href="undangan.php?action=edit&id=<?= $undangan['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../undangan/index.php?to=<?= $undangan['id_unik'] ?>" target="_blank" class="btn btn-sm btn-success">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Ucapan Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ucapan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pengirim</th>
                                        <th>Pesan</th>
                                        <th>Undangan</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUcapan as $ucapan): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ucapan['nama_pengirim']) ?></td>
                                        <td><?= htmlspecialchars(substr($ucapan['pesan'], 0, 50)) ?>...</td>
                                        <td><?= htmlspecialchars($ucapan['judul_undangan']) ?></td>
                                        <td><?= date('d M H:i', strtotime($ucapan['waktu_kirim'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>