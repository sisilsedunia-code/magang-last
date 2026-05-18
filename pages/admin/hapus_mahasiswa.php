<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mahasiswa.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id_mahasiswa = $_POST['id_mahasiswa'] ?? '';

if (!empty($id_mahasiswa)) {
    try {
        $conn->beginTransaction();

        // Hapus data berelasi terlebih dahulu untuk menghindari foreign key constraint error
        $conn->prepare("DELETE FROM nilai_akhir WHERE id_mahasiswa = :id")->execute([':id' => $id_mahasiswa]);
        $conn->prepare("DELETE FROM laporan_akhir WHERE id_mahasiswa = :id")->execute([':id' => $id_mahasiswa]);
        $conn->prepare("DELETE FROM laporan_harian WHERE id_mahasiswa = :id")->execute([':id' => $id_mahasiswa]);
        $conn->prepare("DELETE FROM pengajuan WHERE id_mahasiswa = :id")->execute([':id' => $id_mahasiswa]);
        $conn->prepare("DELETE FROM pendaftaran_magang WHERE id_mahasiswa = :id")->execute([':id' => $id_mahasiswa]);
        
        // Terakhir, hapus data utama mahasiswa
        $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = :id");
        $stmt->bindParam(':id', $id_mahasiswa);
        $stmt->execute();

        $conn->commit();
    } catch(PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error menghapus data: " . $e->getMessage());
    }
}

header("Location: mahasiswa.php");
exit;
?>
