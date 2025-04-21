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
        header("Location: galeri.php");
        exit();
    }
    
    $data = [
        'undangan_id' => $_POST['undangan_id'] ?? 0,
        'keterangan' => $_POST['keterangan'] ?? '',
        'urutan' => $_POST['urutan'] ?? 0
    ];
    
    // Validasi
    $errors = [];
    if (empty($data['undangan_id'])) $errors[] = "Undangan harus dipilih";
    
    if (empty($errors)) {
        // Upload file
        $nama_file = uploadFile('nama_file', ['jpg', 'jpeg', 'png', 'gif']);
        
        if ($nama_file) {
            if ($action === 'tambah') {
                $stmt = $conn->prepare("INSERT INTO galeri (
                    undangan_id, nama_file, keterangan, urutan
                ) VALUES (?, ?, ?, ?)");
                
                $stmt->bind_param("issi", 
                    $data['undangan_id'], $nama_file, $data['keterangan'], $data['urutan']
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Foto berhasil ditambahkan ke galeri";
                    header("Location: galeri.php");
                    exit();
                }
            } elseif ($action === 'edit' && $id > 0) {
                // Jika ada file baru diupload
                if ($nama_file) {
                    // Hapus file lama
                    $file_lama = $conn->query("SELECT nama_file FROM galeri WHERE id = $id")->fetch_row()[0];
                    if ($file_lama) {
                        unlink(__DIR__ . '/../assets/images/uploads/' . $file_lama);
                    }
                    
                    $stmt = $conn->prepare("UPDATE galeri SET 
                        undangan_id = ?, nama_file = ?, keterangan = ?, urutan = ?
                        WHERE id = ?");
                    
                    $stmt->bind_param("issii", 
                        $data['undangan_id'], $nama_file, $data['keterangan'], 
                        $data['urutan'], $id
                    );
                } else {
                    // Jika tidak ada file baru diupload
                    $stmt = $conn->prepare("UPDATE galeri SET 
                        undangan_id = ?, keterangan = ?, urutan = ?
                        WHERE id = ?");
                    
                    $stmt->bind_param("isii", 
                        $data['undangan_id'], $data['keterangan'], 
                        $data['urutan'], $id
                    );
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Foto galeri berhasil diperbarui";
                    header("Location: galeri.php");
                    exit();
                }
            }
        } else {
            if ($action === 'edit' && $id > 0 && empty($_FILES['nama_file']['name'])) {
                // Jika edit dan tidak ada file baru diupload
                $stmt = $conn->prepare("UPDATE galeri SET 
                    undangan_id = ?, keterangan = ?, urutan = ?
                    WHERE id = ?");
                
                $stmt->bind_param("isii", 
                    $data['undangan_id'], $data['keterangan'], 
                    $data['urutan'], $id
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Foto galeri berhasil diperbarui";
                    header("Location: galeri.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Gagal mengupload file atau format tidak didukung";
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Proses Hapus
if ($action === 'hapus' && $id > 0) {
    // Hapus file dari server
    $file = $conn->query("SELECT nama_file FROM galeri WHERE id = $id")->fetch_row()[0];
    if ($file) {
        unlink(__DIR__ . '/../assets/images/uploads/' . $file);
    }
    
    $stmt = $conn->prepare("DELETE FROM galeri WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Foto galeri berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus foto galeri";
    }
    
    header("Location: galeri.php");
    exit();
}

// Ambil data untuk edit
$edit_data = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM galeri WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Ambil semua galeri dengan join undangan
$galeri = $conn->query("
    SELECT g.*, u.nama_pria, u.nama_wanita, u.id_unik 
    FROM galeri g
    JOIN undangan u ON g.undangan_id = u.id
    ORDER BY g.urutan, g.id DESC
")->fetch_all(MYSQLI_ASSOC);

// Ambil daftar undangan untuk dropdown
$undangan_list = $conn->query("SELECT id, id_unik, nama_pria, nama_wanita FROM undangan ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Fungsi upload file
function uploadFile($field, $allowed_extensions) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file = $_FILES[$field];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        return false;
    }
    
    $filename = uniqid() . '.' . $extension;
    $destination = __DIR__ . '/../assets/images/uploads/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Galeri</title>
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
        .gallery-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
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
                <h2 class="h3 mb-4">Manajemen Galeri</h2>
                
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
                            <h5 class="mb-0"><?= $action === 'tambah' ? 'Tambah' : 'Edit' ?> Foto Galeri</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
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
                                    <label class="form-label">Foto</label>
                                    <input type="file" class="form-control" name="nama_file" accept="image/*" <?= $action === 'tambah' ? 'required' : '' ?>>
                                    <?php if ($action === 'edit' && !empty($edit_data['nama_file'])): ?>
                                        <img src="../assets/images/uploads/<?= $edit_data['nama_file'] ?>" class="preview-image">
                                        <p class="text-muted">Biarkan kosong jika tidak ingin mengganti foto</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Keterangan (Opsional)</label>
                                    <input type="text" class="form-control" name="keterangan" 
                                        value="<?= htmlspecialchars($edit_data['keterangan'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Urutan</label>
                                    <input type="number" class="form-control" name="urutan" 
                                        value="<?= $edit_data['urutan'] ?? 0 ?>">
                                    <small class="text-muted">Urutan untuk menampilkan foto (angka lebih kecil ditampilkan lebih awal)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="galeri.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Daftar Galeri -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Galeri</h5>
                            <div>
                                <a href="galeri.php?action=tambah" class="btn btn-sm btn-primary">
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
                                            <th>Foto</th>
                                            <th>Undangan</th>
                                            <th>Keterangan</th>
                                            <th>Urutan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($galeri as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <img src="../assets/images/uploads/<?= $item['nama_file'] ?>" class="gallery-thumbnail">
                                            </td>
                                            <td>
                                                <a href="../undangan/index.php?to=<?= $item['id_unik'] ?>" target="_blank">
                                                    <?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($item['keterangan']) ?></td>
                                            <td><?= $item['urutan'] ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="galeri.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="galeri.php?action=hapus&id=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus foto ini?')">
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
    <script>
        // Preview image sebelum upload
        document.querySelector('input[name="nama_file"]')?.addEventListener('change', function() {
            const preview = document.querySelector('.preview-image');
            const file = this.files[0];
            const reader = new FileReader();
            
            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else if (preview) {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>