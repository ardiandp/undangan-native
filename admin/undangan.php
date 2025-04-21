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
        header("Location: undangan.php");
        exit();
    }
    
    $data = [
        'nama_pria' => $_POST['nama_pria'] ?? '',
        'nama_wanita' => $_POST['nama_wanita'] ?? '',
        'nama_ortu_pria' => $_POST['nama_ortu_pria'] ?? '',
        'nama_ortu_wanita' => $_POST['nama_ortu_wanita'] ?? '',
        'tanggal_akad' => $_POST['tanggal_akad'] ?? '',
        'tempat_akad' => $_POST['tempat_akad'] ?? '',
        'tanggal_resepsi' => $_POST['tanggal_resepsi'] ?? '',
        'tempat_resepsi' => $_POST['tempat_resepsi'] ?? '',
        'alamat_lengkap' => $_POST['alamat_lengkap'] ?? '',
        'google_maps' => $_POST['google_maps'] ?? '',
        'judul_undangan' => $_POST['judul_undangan'] ?? '',
        'pesan_pembuka' => $_POST['pesan_pembuka'] ?? '',
        'tema' => $_POST['tema'] ?? 'classic'
    ];
    
    // Validasi
    $errors = [];
    if (empty($data['nama_pria'])) $errors[] = "Nama pria harus diisi";
    if (empty($data['nama_wanita'])) $errors[] = "Nama wanita harus diisi";
    if (empty($data['tanggal_akad'])) $errors[] = "Tanggal akad harus diisi";
    if (empty($data['tempat_akad'])) $errors[] = "Tempat akad harus diisi";
    
    if (empty($errors)) {
        if ($action === 'tambah') {
            // Generate ID unik
            $id_unik = generateUniqueId();
            
            // Upload foto
            $foto_pasangan = uploadFile('foto_pasangan', ['jpg', 'jpeg', 'png']);
            $foto_cover = uploadFile('foto_cover', ['jpg', 'jpeg', 'png']);
            
            if ($foto_pasangan && $foto_cover) {
                $stmt = $conn->prepare("INSERT INTO undangan (
                    id_unik, nama_pria, nama_wanita, nama_ortu_pria, nama_ortu_wanita,
                    tanggal_akad, tempat_akad, tanggal_resepsi, tempat_resepsi,
                    alamat_lengkap, google_maps, foto_pasangan, foto_cover,
                    judul_undangan, pesan_pembuka, tema
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("ssssssssssssssss", 
                    $id_unik, $data['nama_pria'], $data['nama_wanita'], 
                    $data['nama_ortu_pria'], $data['nama_ortu_wanita'],
                    $data['tanggal_akad'], $data['tempat_akad'], 
                    $data['tanggal_resepsi'], $data['tempat_resepsi'],
                    $data['alamat_lengkap'], $data['google_maps'],
                    $foto_pasangan, $foto_cover,
                    $data['judul_undangan'], $data['pesan_pembuka'], $data['tema']
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Undangan berhasil ditambahkan";
                    header("Location: undangan.php");
                    exit();
                }
            }
            $_SESSION['error'] = "Gagal mengupload foto";
        } elseif ($action === 'edit' && $id > 0) {
            // Update data
            $update_fields = [];
            $params = [];
            $types = '';
            
            foreach ($data as $field => $value) {
                $update_fields[] = "$field = ?";
                $params[] = $value;
                $types .= 's';
            }
            
            // Handle file upload jika ada
            if (!empty($_FILES['foto_pasangan']['name'])) {
                $foto_pasangan = uploadFile('foto_pasangan', ['jpg', 'jpeg', 'png']);
                if ($foto_pasangan) {
                    $update_fields[] = "foto_pasangan = ?";
                    $params[] = $foto_pasangan;
                    $types .= 's';
                }
            }
            
            if (!empty($_FILES['foto_cover']['name'])) {
                $foto_cover = uploadFile('foto_cover', ['jpg', 'jpeg', 'png']);
                if ($foto_cover) {
                    $update_fields[] = "foto_cover = ?";
                    $params[] = $foto_cover;
                    $types .= 's';
                }
            }
            
            $params[] = $id;
            $types .= 'i';
            
            $sql = "UPDATE undangan SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Undangan berhasil diperbarui";
                header("Location: undangan.php");
                exit();
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Proses Hapus
if ($action === 'hapus' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM undangan WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Undangan berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus undangan";
    }
    
    header("Location: undangan.php");
    exit();
}

// Ambil data untuk edit
$edit_data = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM undangan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}

// Ambil semua undangan
$undangan = $conn->query("SELECT * FROM undangan ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

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
    <title>Manajemen Undangan</title>
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
                <h2 class="h3 mb-4">Manajemen Undangan</h2>
                
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
                            <h5 class="mb-0"><?= $action === 'tambah' ? 'Tambah' : 'Edit' ?> Undangan</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Pria</label>
                                        <input type="text" class="form-control" name="nama_pria" 
                                            value="<?= htmlspecialchars($edit_data['nama_pria'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Wanita</label>
                                        <input type="text" class="form-control" name="nama_wanita" 
                                            value="<?= htmlspecialchars($edit_data['nama_wanita'] ?? '') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Orang Tua Pria</label>
                                        <input type="text" class="form-control" name="nama_ortu_pria" 
                                            value="<?= htmlspecialchars($edit_data['nama_ortu_pria'] ?? '') ?>" 
                                            placeholder="Contoh: Budi Santoso & Ani Wijaya">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Orang Tua Wanita</label>
                                        <input type="text" class="form-control" name="nama_ortu_wanita" 
                                            value="<?= htmlspecialchars($edit_data['nama_ortu_wanita'] ?? '') ?>" 
                                            placeholder="Contoh: Dedi Prabowo & Rina Hartati">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Akad Nikah</label>
                                        <input type="datetime-local" class="form-control" name="tanggal_akad" 
                                            value="<?= isset($edit_data['tanggal_akad']) ? str_replace(' ', 'T', substr($edit_data['tanggal_akad'], 0, 16)) : '' ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tempat Akad Nikah</label>
                                        <input type="text" class="form-control" name="tempat_akad" 
                                            value="<?= htmlspecialchars($edit_data['tempat_akad'] ?? '') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Resepsi (Opsional)</label>
                                        <input type="datetime-local" class="form-control" name="tanggal_resepsi" 
                                            value="<?= isset($edit_data['tanggal_resepsi']) ? str_replace(' ', 'T', substr($edit_data['tanggal_resepsi'], 0, 16)) : '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tempat Resepsi (Opsional)</label>
                                        <input type="text" class="form-control" name="tempat_resepsi" 
                                            value="<?= htmlspecialchars($edit_data['tempat_resepsi'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" name="alamat_lengkap" rows="3"><?= htmlspecialchars($edit_data['alamat_lengkap'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Google Maps Embed URL</label>
                                    <input type="text" class="form-control" name="google_maps" 
                                        value="<?= htmlspecialchars($edit_data['google_maps'] ?? '') ?>" 
                                        placeholder="Contoh: https://maps.google.com/maps?q=Masjid+Agung+Al-Azhar&z=15">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Foto Pasangan</label>
                                        <input type="file" class="form-control" name="foto_pasangan" accept="image/*" <?= $action === 'tambah' ? 'required' : '' ?>>
                                        <?php if ($action === 'edit' && !empty($edit_data['foto_pasangan'])): ?>
                                            <img src="../assets/images/uploads/<?= $edit_data['foto_pasangan'] ?>" class="preview-image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Foto Cover</label>
                                        <input type="file" class="form-control" name="foto_cover" accept="image/*" <?= $action === 'tambah' ? 'required' : '' ?>>
                                        <?php if ($action === 'edit' && !empty($edit_data['foto_cover'])): ?>
                                            <img src="../assets/images/uploads/<?= $edit_data['foto_cover'] ?>" class="preview-image">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Judul Undangan</label>
                                    <input type="text" class="form-control" name="judul_undangan" 
                                        value="<?= htmlspecialchars($edit_data['judul_undangan'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pesan Pembuka</label>
                                    <textarea class="form-control" name="pesan_pembuka" rows="3" required><?= htmlspecialchars($edit_data['pesan_pembuka'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tema</label>
                                    <select class="form-select" name="tema">
                                        <option value="classic" <?= ($edit_data['tema'] ?? '') === 'classic' ? 'selected' : '' ?>>Classic</option>
                                        <option value="modern" <?= ($edit_data['tema'] ?? '') === 'modern' ? 'selected' : '' ?>>Modern</option>
                                        <option value="elegant" <?= ($edit_data['tema'] ?? '') === 'elegant' ? 'selected' : '' ?>>Elegant</option>
                                        <option value="minimalist" <?= ($edit_data['tema'] ?? '') === 'minimalist' ? 'selected' : '' ?>>Minimalist</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="undangan.php" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Daftar Undangan -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Undangan</h5>
                            <a href="undangan.php?action=tambah" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Tambah
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pasangan</th>
                                            <th>Tanggal Akad</th>
                                            <th>Tema</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($undangan as $item): ?>
                                        <tr>
                                            <td><?= $item['id_unik'] ?></td>
                                            <td><?= htmlspecialchars($item['nama_pria']) ?> & <?= htmlspecialchars($item['nama_wanita']) ?></td>
                                            <td><?= date('d M Y', strtotime($item['tanggal_akad'])) ?></td>
                                            <td><?= ucfirst($item['tema']) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="undangan.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../undangan/index.php?to=<?= $item['id_unik'] ?>" target="_blank" class="btn btn-success">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="undangan.php?action=hapus&id=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
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
        function previewImage(input, previewId) {
            const preview = document.querySelector(previewId);
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
                preview.style.display = 'none';
            }
        }
        
        // Event listener untuk preview image
        document.querySelector('input[name="foto_pasangan"]')?.addEventListener('change', function() {
            previewImage(this, '.preview-image:first-of-type');
        });
        
        document.querySelector('input[name="foto_cover"]')?.addEventListener('change', function() {
            previewImage(this, '.preview-image:last-of-type');
        });
    </script>
</body>
</html>