<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /magang-last/login");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id_mitra = $_POST['id_mitra'] ?? '';
$nama = trim($_POST['nama'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$provinsi = trim($_POST['provinsi'] ?? '');
$kota = trim($_POST['kota'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$kode_pos = trim($_POST['kode_pos'] ?? '');

if (empty($id_mitra) || empty($nama) || empty($alamat) || empty($provinsi) || empty($kota) || empty($kecamatan) || empty($kode_pos)) {
    $_SESSION['error'] = "Semua field bertanda bintang wajib diisi.";
    header("Location: mitra.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE mitra
        SET nama = ?,
            alamat = ?,
            provinsi = ?,
            kota = ?,
            kecamatan = ?,
            kode_pos = ?
        WHERE id_mitra = ?
    ");
    $stmt->execute([$nama, $alamat, $provinsi, $kota, $kecamatan, $kode_pos, $id_mitra]);
    
    $_SESSION['success'] = "Data mitra perusahaan berhasil diperbarui.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal memperbarui data mitra: " . $e->getMessage();
}

header("Location: mitra.php");
exit;
