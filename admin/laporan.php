<?php
require_once __DIR__ . '/admin_auth.php';

// Set default periode (bulan ini)
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

// Filter berdasarkan tanggal jika ada input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? $start_date;
    $end_date = $_POST['end_date'] ?? $end_date;
}

// Query untuk statistik umum
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM undangan) as total_undangan,
        (SELECT COUNT(*) FROM tamu) as total_tamu,
        (SELECT COUNT(*) FROM ucapan) as total_ucapan,
        (SELECT COUNT(*) FROM hit_counter) as total_kunjungan,
        (SELECT COUNT(*) FROM tamu WHERE status_konfirmasi = 'hadir') as tamu_hadir,
        (SELECT COUNT(*) FROM tamu WHERE status_konfirmasi = 'tidak_hadir') as tamu_tidak_hadir,
        (SELECT COUNT(*) FROM tamu WHERE status_konfirmasi = 'menunggu') as tamu_menunggu
")->fetch_assoc();

// Data untuk chart konfirmasi tamu
$konfirmasiData = $conn->query("
    SELECT status_konfirmasi, COUNT(*) as jumlah 
    FROM tamu 
    GROUP BY status_konfirmasi
")->fetch_all(MYSQLI_ASSOC);

// Data kunjungan per undangan
$kunjunganData = $conn->query("
    SELECT u.id_unik, u.nama_pria, u.nama_wanita, COUNT(h.id) as jumlah
    FROM undangan u
    LEFT JOIN hit_counter h ON u.id = h.undangan_id
    GROUP BY u.id
    ORDER BY jumlah DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Data aktivitas terbaru
$aktivitasTerbaru = $conn->query("
    (SELECT 'tamu' as tipe, nama_tamu as nama, waktu_konfirmasi as waktu 
     FROM tamu WHERE waktu_konfirmasi IS NOT NULL
     ORDER BY waktu_konfirmasi DESC LIMIT 5)
    UNION
    (SELECT 'ucapan' as tipe, nama_pengirim as nama, waktu_kirim as waktu 
     FROM ucapan 
     ORDER BY waktu_kirim DESC LIMIT 5)
    ORDER BY waktu DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-chart {
            height: 100%;
            min-height: 300px;
        }
        .stat-card {
            transition: transform 0.3s;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card-undangan {
            border-left-color: #0d6efd;
        }
        .stat-card-tamu {
            border-left-color: #198754;
        }
        .stat-card-ucapan {
            border-left-color: #6f42c1;
        }
        .stat-card-kunjungan {
            border-left-color: #fd7e14;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/admin_navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="fas fa-chart-bar"></i> Laporan Sistem</h2>
            </div>
            <div class="col-md-6">
                <form method="post" class="row g-3 justify-content-end">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="col-auto">
                        <label for="start_date" class="col-form-label">Dari:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="col-auto">
                        <label for="end_date" class="col-form-label">Sampai:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistik Utama -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-undangan">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Undangan</h5>
                                <h2 class="mb-0"><?= $stats['total_undangan'] ?></h2>
                            </div>
                            <i class="fas fa-envelope fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-tamu">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Tamu</h5>
                                <h2 class="mb-0"><?= $stats['total_tamu'] ?></h2>
                                <small class="text-muted">
                                    Hadir: <?= $stats['tamu_hadir'] ?>, 
                                    Tidak: <?= $stats['tamu_tidak_hadir'] ?>
                                </small>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-ucapan">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Ucapan</h5>
                                <h2 class="mb-0"><?= $stats['total_ucapan'] ?></h2>
                            </div>
                            <i class="fas fa-comments fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-kunjungan">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Kunjungan</h5>
                                <h2 class="mb-0"><?= $stats['total_kunjungan'] ?></h2>
                            </div>
                            <i class="fas fa-eye fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik dan Laporan -->
        <div class="row mb-4">
            <!-- Grafik Konfirmasi Tamu -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Status Konfirmasi Tamu</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartKonfirmasi" class="card-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 10 Undangan Terpopuler -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">10 Undangan Terpopuler</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartKunjungan" class="card-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Tipe</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aktivitasTerbaru as $aktivitas): ?>
                                    <tr>
                                        <td><?= date('d M Y H:i', strtotime($aktivitas['waktu'])) ?></td>
                                        <td>
                                            <?php if ($aktivitas['tipe'] == 'tamu'): ?>
                                                <span class="badge bg-success">Konfirmasi</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Ucapan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($aktivitas['nama']) ?></td>
                                        <td>
                                            <?php if ($aktivitas['tipe'] == 'tamu'): ?>
                                                <a href="tamu.php" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-search"></i> Detail
                                                </a>
                                            <?php else: ?>
                                                <a href="ucapan.php" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-search"></i> Detail
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart Konfirmasi Tamu (Pie Chart)
        const ctxKonfirmasi = document.getElementById('chartKonfirmasi').getContext('2d');
        new Chart(ctxKonfirmasi, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_map(function($item) {
                    return ucfirst(str_replace('_', ' ', $item['status_konfirmasi']));
                }, $konfirmasiData)) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($konfirmasiData, 'jumlah')) ?>,
                    backgroundColor: [
                        '#28a745', // Hadir
                        '#dc3545', // Tidak Hadir
                        '#6c757d'  // Menunggu
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Chart Kunjungan (Bar Chart)
        const ctxKunjungan = document.getElementById('chartKunjungan').getContext('2d');
        new Chart(ctxKunjungan, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($item) {
                    return $item['nama_pria'] + ' & ' + $item['nama_wanita'];
                }, $kunjunganData)) ?>,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: <?= json_encode(array_column($kunjunganData, 'jumlah')) ?>,
                    backgroundColor: '#0d6efd',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>