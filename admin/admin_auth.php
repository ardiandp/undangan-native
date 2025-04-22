<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Ambil data admin dari database
$admin_id = $_SESSION['admin_id'];
$query = $conn->query("SELECT * FROM admin WHERE id = $admin_id");
$admin = $query->fetch_assoc();

// Jika admin tidak ditemukan, logout
if (!$admin) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Update waktu aktivitas terakhir
$conn->query("UPDATE admin SET terakhir_login = NOW() WHERE id = $admin_id");
?>