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

if (empty($id_mitra)) {
    $_SESSION['error'] = "ID Mitra tidak valid.";
    header("Location: mitra.php");
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM mitra WHERE id_mitra = ?");
    $stmt->execute([$id_mitra]);
    
    $_SESSION['success'] = "Data mitra perusahaan berhasil dihapus.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Gagal menghapus data mitra: " . $e->getMessage();
}

header("Location: mitra.php");
exit;
