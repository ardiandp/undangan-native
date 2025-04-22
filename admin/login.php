<?php
// login.php
session_start();
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['level'] = $user['level'];

        $update = $conn->prepare("UPDATE admin SET terakhir_login = NOW() WHERE id = ?");
        $update->bind_param("i", $user['id']);
        $update->execute();

        header("Location: dashboard.php");
        exit();
    }
}

header("Location: index.php?error=1");
exit();
?>