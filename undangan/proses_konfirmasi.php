<?php
require_once __DIR__ . '/../includes/functions.php';

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Validasi CSRF token
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token CSRF tidak valid";
    header("Location: index.php");
    exit();
}

// Ambil data dari form
$undangan_id = $_POST['undangan_id'] ?? 0;
$nama_tamu = $_POST['nama_tamu'] ?? '';
$status = $_POST['status'] ?? 'menunggu';
$jumlah_hadir = $_POST['jumlah_hadir'] ?? 0;
$pesan = $_POST['pesan'] ?? '';

// Validasi data
if (empty($undangan_id) || empty($nama_tamu)) {
    $_SESSION['error'] = "Data yang diperlukan tidak lengkap";
    header("Location: index.php");
    exit();
}

// Proses konfirmasi
if (konfirmasiKehadiran($undangan_id, $nama_tamu, $status, $jumlah_hadir, $pesan)) {
    $_SESSION['success'] = "Konfirmasi kehadiran berhasil disimpan";
} else {
    $_SESSION['error'] = "Gagal menyimpan konfirmasi kehadiran";
}

// Redirect kembali ke halaman undangan
$undangan = getUndanganById($undangan_id);
header("Location: index.php?to=" . $undangan['id_unik'] . "&nama=" . urlencode($nama_tamu));
exit();
?>