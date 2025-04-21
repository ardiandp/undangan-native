<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Ambil data admin saat ini
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();

if (!$current_admin) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>