<?php
require_once __DIR__ . '/admin_auth.php';

// Query statistik
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM undangan) as total_undangan,
        (SELECT COUNT(*) FROM tamu) as total_tamu,
        (SELECT COUNT(*) FROM ucapan) as total_ucapan,
        (SELECT COUNT(*) FROM hit_counter) as total_kunjungan,
        (SELECT COUNT(*) FROM tamu WHERE status_konfirmasi = 'hadir') as tamu_hadir
")->fetch_assoc();

// Undangan terbaru
$recent_undangan = $conn->query("
    SELECT * FROM undangan 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Ucapan terbaru
$recent_ucapan = $conn->query("
    SELECT u.*, un.nama_pria, un.nama_wanita 
    FROM ucapan u
    JOIN undangan un ON u.undangan_id = un.id
    ORDER BY u.waktu_kirim DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
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
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar .nav-link { color: rgba(255,255,255,.5); border-radius: 5px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .sidebar .nav-link i { margin-right: 10px; }
        .stat-card { transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/admin_sidebar.php'; ?>

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
                                        <h2 class="mb-0"><?= $stats['total_undangan'] ?></h2>
                                    </div>
                                    <i class="fas fa-envelope fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tambahkan statistik lainnya -->
                </div>
                
                <!-- Recent Undangan -->
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
                                    <?php foreach ($recent_undangan as $item): ?>
                                    <tr>
                                        <td><?= $item['id_unik'] ?></td>
                                        <td><?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?></td>
                                        <td><?= date('d M Y', strtotime($item['tanggal_akad'])) ?></td>
                                        <td>
                                            <a href="undangan.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Ucapan -->
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
                                    <?php foreach ($recent_ucapan as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nama_pengirim']) ?></td>
                                        <td><?= htmlspecialchars(substr($item['pesan'], 0, 50)) ?>...</td>
                                        <td><?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?></td>
                                        <td><?= date('d M H:i', strtotime($item['waktu_kirim'])) ?></td>
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