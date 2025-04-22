<?php
// dashboard.php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
<h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
<p>Level Anda: <?php echo $_SESSION['level']; ?></p>
<a href="logout.php">Logout</a>
</body>
</html>