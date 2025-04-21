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
$nama_pengirim = $_POST['nama_pengirim'] ?? '';
$pesan = $_POST['pesan'] ?? '';

// Validasi data
if (empty($undangan_id) || empty($nama_pengirim) || empty($pesan)) {
    $_SESSION['error'] = "Harap isi semua field yang diperlukan";
    header("Location: index.php");
    exit();
}

// Filter input
$nama_pengirim = htmlspecialchars(trim($nama_pengirim));
$pesan = htmlspecialchars(trim($pesan));

// Proses penyimpanan ucapan
if (tambahUcapan($undangan_id, $nama_pengirim, $pesan)) {
    $_SESSION['success'] = "Ucapan Anda berhasil dikirim";
} else {
    $_SESSION['error'] = "Gagal mengirim ucapan";
}

// Redirect kembali ke halaman undangan
$undangan = getUndanganById($undangan_id);
header("Location: index.php?to=" . $undangan['id_unik'] . "#wishes");
exit();
?>