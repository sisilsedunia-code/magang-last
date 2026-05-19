<?php
session_start();

require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /magang-last/login");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id_mahasiswa = $_POST['id_mahasiswa'];
$judul_laporan = $_POST['judul_laporan'];

$file_laporan = null;

if (
    isset($_FILES['file_laporan']) &&
    $_FILES['file_laporan']['error'] == 0
) {

    $upload_dir = "../../uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir);
    }

    $filename =
        time() . "_" .
        basename($_FILES['file_laporan']['name']);

    move_uploaded_file(
        $_FILES['file_laporan']['tmp_name'],
        $upload_dir . $filename
    );

    $file_laporan = $filename;
}

// Check if final report already exists for this student
$stmtCheck = $conn->prepare("SELECT id_laporan_akhir, file_laporan FROM laporan_akhir WHERE id_mahasiswa = ?");
$stmtCheck->execute([$id_mahasiswa]);
$existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // If a new file is uploaded and we already have an old one, delete the old file
    if ($file_laporan && !empty($existing['file_laporan'])) {
        $old_file_path = "../../uploads/" . $existing['file_laporan'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }
    
    // Determine which file to save (use new uploaded file, or keep the existing one if none uploaded)
    $file_to_save = $file_laporan ? $file_laporan : $existing['file_laporan'];

    $stmt = $conn->prepare("
        UPDATE laporan_akhir
        SET judul_laporan = ?,
            file_laporan = ?,
            status_review = 'Menunggu',
            catatan_dosen = NULL,
            tanggal_upload = NOW()
        WHERE id_mahasiswa = ?
    ");
    $stmt->execute([
        $judul_laporan,
        $file_to_save,
        $id_mahasiswa
    ]);
} else {
    $stmt = $conn->prepare("
        INSERT INTO laporan_akhir
        (
            id_mahasiswa,
            judul_laporan,
            file_laporan,
            status_review,
            tanggal_upload
        )
        VALUES
        (
            ?, ?, ?, 'Menunggu', NOW()
        )
    ");
    $stmt->execute([
        $id_mahasiswa,
        $judul_laporan,
        $file_laporan
    ]);
}

header("Location: laporan_akhir.php");
exit;

