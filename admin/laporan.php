<?php
require_once __DIR__ . '/admin_auth.php';

// ... [kode sebelumnya tetap sama]

// Data untuk chart konfirmasi tamu (diperbaiki)
$konfirmasiData = $conn->query("
    SELECT 
        SUM(CASE WHEN status_konfirmasi = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status_konfirmasi = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir,
        SUM(CASE WHEN status_konfirmasi = 'menunggu' THEN 1 ELSE 0 END) as menunggu
    FROM tamu
")->fetch_assoc();

// Data untuk chart undangan terpopuler (diperbaiki)
$kunjunganData = $conn->query("
    SELECT 
        CONCAT(u.nama_pria, ' & ', u.nama_wanita) as pasangan,
        COUNT(h.id) as jumlah
    FROM undangan u
    LEFT JOIN hit_counter h ON u.id = h.undangan_id
    GROUP BY u.id
    ORDER BY jumlah DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// ... [kode sebelumnya tetap sama]
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ... [head sebelumnya tetap sama] ... -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        /* ... [style sebelumnya tetap sama] ... */
    </style>
</head>
<body>
    <?php include __DIR__ . '/admin_navbar.php'; ?>

    <!-- ... [konten sebelumnya sampai sebelum chart] ... -->

    <!-- Grafik Konfirmasi Tamu -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Status Konfirmasi Tamu</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartKonfirmasi"></canvas>
                </div>
                <div class="mt-3 text-center">
                    <span class="badge bg-success me-2"><i class="fas fa-check-circle"></i> Hadir: <?= $konfirmasiData['hadir'] ?></span>
                    <span class="badge bg-danger me-2"><i class="fas fa-times-circle"></i> Tidak Hadir: <?= $konfirmasiData['tidak_hadir'] ?></span>
                    <span class="badge bg-secondary"><i class="fas fa-clock"></i> Menunggu: <?= $konfirmasiData['menunggu'] ?></span>
                </div>
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
                <div class="chart-container">
                    <canvas id="chartKunjungan"></canvas>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Berdasarkan jumlah kunjungan halaman undangan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ... [kode setelah chart tetap sama] ... -->

    <script>
        // Inisialisasi Chart Konfirmasi Tamu (Pie Chart)
        document.addEventListener('DOMContentLoaded', function() {
            const ctxKonfirmasi = document.getElementById('chartKonfirmasi');
            new Chart(ctxKonfirmasi, {
                type: 'pie',
                data: {
                    labels: ['Hadir', 'Tidak Hadir', 'Menunggu'],
                    datasets: [{
                        data: [
                            <?= $konfirmasiData['hadir'] ?>,
                            <?= $konfirmasiData['tidak_hadir'] ?>,
                            <?= $konfirmasiData['menunggu'] ?>
                        ],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(108, 117, 125, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(108, 117, 125, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Inisialisasi Chart Kunjungan (Bar Chart)
            const ctxKunjungan = document.getElementById('chartKunjungan');
            new Chart(ctxKunjungan, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($kunjunganData, 'pasangan')) ?>,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: <?= json_encode(array_column($kunjunganData, 'jumlah')) ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const undangan = <?= json_encode($kunjunganData) ?>[context.dataIndex];
                                    return `${undangan.pasangan}`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>