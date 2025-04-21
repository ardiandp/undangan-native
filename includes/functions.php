<?php
require_once __DIR__ . '/../config/database.php';

function getUndanganByUnikId($id_unik) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM undangan WHERE id_unik = ?");
    $stmt->bind_param("s", $id_unik);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getTamuByUndanganId($undangan_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM tamu WHERE undangan_id = ?");
    $stmt->bind_param("i", $undangan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUcapanByUndanganId($undangan_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM ucapan WHERE undangan_id = ? ORDER BY waktu_kirim DESC");
    $stmt->bind_param("i", $undangan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getGaleriByUndanganId($undangan_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM galeri WHERE undangan_id = ? ORDER BY urutan");
    $stmt->bind_param("i", $undangan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addHitCounter($undangan_id, $ip_address) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO hit_counter (undangan_id, ip_address) VALUES (?, ?)");
    $stmt->bind_param("is", $undangan_id, $ip_address);
    $stmt->execute();
}

function getTotalHits($undangan_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hit_counter WHERE undangan_id = ?");
    $stmt->bind_param("i", $undangan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

function konfirmasiKehadiran($undangan_id, $nama_tamu, $status, $jumlah_hadir, $pesan) {
    global $conn;
    $stmt = $conn->prepare("UPDATE tamu SET status_konfirmasi = ?, jumlah_hadir = ?, pesan = ?, waktu_konfirmasi = NOW() WHERE undangan_id = ? AND nama_tamu = ?");
    $stmt->bind_param("sissi", $status, $jumlah_hadir, $pesan, $undangan_id, $nama_tamu);
    return $stmt->execute();
}

function tambahUcapan($undangan_id, $nama_pengirim, $pesan) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO ucapan (undangan_id, nama_pengirim, pesan) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $undangan_id, $nama_pengirim, $pesan);
    return $stmt->execute();
}
?>