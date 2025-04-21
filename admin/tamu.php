<?php
require_once __DIR__ . '/admin_auth.php';

// Proses CRUD
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Proses Tambah/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "Token CSRF tidak valid";
        header("Location: tamu.php");
        exit();
    }
    
    $data = [
        'undangan_id' => $_POST['undangan_id'] ?? 0,
        'nama_tamu' => $_POST['nama_tamu'] ?? '',
        'nomor_wa' => $_POST['nomor_wa'] ?? '',
        'status_konfirmasi' => $_POST['status_konfirmasi'] ?? 'menunggu',
        'jumlah_hadir' => $_POST['jumlah_hadir'] ?? 0,
        'pesan' => $_POST['pesan'] ?? ''
    ];
    
    // Validasi
    $errors = [];
    if (empty($data['undangan_id'])) $errors[] = "Undangan harus dipilih";
    if (empty($data['nama_tamu'])) $errors[] = "Nama tamu harus diisi";
    
    if (empty($errors)) {
        if ($action === 'tambah') {
            $stmt = $conn->prepare("INSERT INTO tamu (
                undangan_id, nama_tamu, nomor_wa, status_konfirmasi, jumlah_hadir, pesan
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("isssis", 
                $data['undangan_id'], $data['nama_tamu'], $data['nomor_wa'],
                $data['status_konfirmasi'], $data['jumlah_hadir'], $data['pesan']
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Tamu berhasil ditambahkan";
                header("Location: tamu.php");
                exit();
            }
        } elseif ($action === 'edit' && $id > 0) {
            $stmt = $conn->prepare("UPDATE tamu SET 
                undangan_id = ?, nama_tamu = ?, nomor_wa = ?,
                status_konfirmasi = ?, jumlah_hadir = ?, pesan = ?
                WHERE id = ?");
            
            $stmt->bind_param("isssisi", 
                $data['undangan_id'], $data['nama_tamu'], $data['nomor_wa'],
                $data['status_konfirmasi'], $data['jumlah_hadir'], $data['pesan'],
                $id
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Tamu berhasil diperbarui";
                header("Location: tamu.php");
                exit();
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Proses Hapus
if ($action === 'hapus' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM tamu WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tamu berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus tamu";
    }
    
    header("Location: tamu.php");
    exit();
}

// Ambil data untuk edit
$edit_data = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tamu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Ambil semua tamu dengan join undangan
$tamu = $conn->query("
    SELECT t.*, u.nama_pria, u.nama_wanita, u.id_unik 
    FROM tamu t
    JOIN undangan u ON t.undangan_id = u.id
    ORDER BY t.id DESC
")->fetch_all(MYSQLI_ASSOC);

// Ambil daftar undangan untuk dropdown
$undangan_list = $conn->query("SELECT id, id_unik, nama_pria, nama_wanita FROM undangan ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tamu</title>
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
        .badge-hadir {
            background-color: #28a745;
        }
        .badge-tidak-hadir {
            background-color: #dc3545;
        }
        .badge-menunggu {
            background-color: #6c757d;
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
                <h2 class="h3 mb-4">Manajemen Tamu</h2>
                
                <!-- Notifikasi -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Form Tambah/Edit -->
                <?php if ($action === 'tambah' || $action === 'edit'): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><?= $action === 'tambah' ? 'Tambah' : 'Edit' ?> Tamu</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Undangan</label>
                                    <select class="form-select" name="undangan_id" required>
                                        <option value="">Pilih Undangan</option>
                                        <?php foreach ($undangan_list as $undangan): ?>
                                        <option value="<?= $undangan['id'] ?>" 
                                            <?= ($edit_data['undangan_id'] ?? '') == $undangan['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($undangan['nama_pria']) ?> & <?= htmlspecialchars($undangan['nama_wanita']) ?> (<?= $undangan['id_unik'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Tamu</label>
                                    <input type="text" class="form-control" name="nama_tamu" 
                                        value="<?= htmlspecialchars($edit_data['nama_tamu'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nomor WhatsApp (Opsional)</label>
                                    <input type="text" class="form-control" name="nomor_wa" 
                                        value="<?= htmlspecialchars($edit_data['nomor_wa'] ?? '') ?>" 
                                        placeholder="Contoh: 6281234567890">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status Konfirmasi</label>
                                    <select class="form-select" name="status_konfirmasi">
                                        <option value="menunggu" <?= ($edit_data['status_konfirmasi'] ?? '') === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                        <option value="hadir" <?= ($edit_data['status_konfirmasi'] ?? '') === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                        <option value="tidak_hadir" <?= ($edit_data['status_konfirmasi'] ?? '') === 'tidak_hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Hadir</label>
                                    <input type="number" class="form-control" name="jumlah_hadir" 
                                        value="<?= $edit_data['jumlah_hadir'] ?? 1 ?>" min="0">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pesan (Opsional)</label>
                                    <textarea class="form-control" name="pesan" rows="3"><?= htmlspecialchars($edit_data['pesan'] ?? '') ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="tamu.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Daftar Tamu -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Tamu</h5>
                            <div>
                                <a href="tamu.php?action=tambah" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Tambah
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Tamu</th>
                                            <th>Undangan</th>
                                            <th>Status</th>
                                            <th>Jumlah Hadir</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tamu as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($item['nama_tamu']) ?></td>
                                            <td>
                                                <a href="../undangan/index.php?to=<?= $item['id_unik'] ?>" target="_blank">
                                                    <?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($item['status_konfirmasi'] === 'hadir'): ?>
                                                    <span class="badge badge-hadir text-white">Hadir</span>
                                                <?php elseif ($item['status_konfirmasi'] === 'tidak_hadir'): ?>
                                                    <span class="badge badge-tidak-hadir text-white">Tidak Hadir</span>
                                                <?php else: ?>
                                                    <span class="badge badge-menunggu text-white">Menunggu</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item['jumlah_hadir'] ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="tamu.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="tamu.php?action=hapus&id=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>