<?php
require_once __DIR__ . '/admin_auth.php';

// Proses Hapus
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'hapus' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM ucapan WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Ucapan berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus ucapan";
    }
    
    header("Location: ucapan.php");
    exit();
}

// Ambil semua ucapan dengan join undangan
$ucapan = $conn->query("
    SELECT u.*, un.nama_pria, un.nama_wanita, un.id_unik 
    FROM ucapan u
    JOIN undangan un ON u.undangan_id = un.id
    ORDER BY u.waktu_kirim DESC
")->fetch_all(MYSQLI_ASSOC);

// Generate CSRF token untuk aksi hapus
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ucapan</title>
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
        .message-content {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h3 mb-4">Manajemen Ucapan</h2>
                
                <!-- Notifikasi -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Daftar Ucapan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Ucapan dari Tamu</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Pengirim</th>
                                        <th>Undangan</th>
                                        <th>Pesan</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ucapan as $index => $item): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($item['nama_pengirim']) ?></td>
                                        <td>
                                            <a href="../undangan/index.php?to=<?= $item['id_unik'] ?>" target="_blank">
                                                <?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="message-content" title="<?= htmlspecialchars($item['pesan']) ?>">
                                                <?= htmlspecialchars($item['pesan']) ?>
                                            </div>
                                        </td>
                                        <td><?= date('d M Y H:i', strtotime($item['waktu_kirim'])) ?></td>
                                        <td>
                                            <a href="ucapan.php?action=hapus&id=<?= $item['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus ucapan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
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
</body>
</html>