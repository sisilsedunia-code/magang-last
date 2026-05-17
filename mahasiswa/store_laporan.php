<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id_mahasiswa = $_POST['id_mahasiswa'];
$id_pendaftaran = $_POST['id_pendaftaran'];
$tanggal_submit = $_POST['tanggal_submit'];
$jam_masuk = $_POST['jam_masuk'];
$jam_keluar = $_POST['jam_keluar'];
$kegiatan = trim($_POST['kegiatan']);

$stmtPendaftaran = $conn->prepare("
    SELECT tanggal_mulai
    FROM pendaftaran_magang
    WHERE id_pendaftaran = ?
");
$stmtPendaftaran->execute([$id_pendaftaran]);
$pendaftaran = $stmtPendaftaran->fetch(PDO::FETCH_ASSOC);

if (!$pendaftaran) {
    $_SESSION['error'] = "Data magang tidak ditemukan.";
    header("Location: laporan_harian.php");
    exit;
}

$tanggal_mulai = strtotime($pendaftaran['tanggal_mulai']);
$tanggal_logbook = strtotime($tanggal_submit);
$today = strtotime(date('Y-m-d'));

if ($tanggal_logbook > $today) {
    $_SESSION['error'] = "Tanggal logbook tidak boleh melebihi hari ini.";
    header("Location: laporan_harian.php");
    exit;
}

if ($tanggal_logbook < $tanggal_mulai) {
    $_SESSION['error'] = "Tanggal logbook tidak valid.";
    header("Location: laporan_harian.php");
    exit;
}

if (empty($kegiatan)) {
    $_SESSION['error'] = "Kegiatan tidak boleh kosong.";
    header("Location: laporan_harian.php");
    exit;
}

if ($jam_keluar <= $jam_masuk) {
    $_SESSION['error'] = "Jam keluar harus lebih besar dari jam masuk.";
    header("Location: laporan_harian.php");
    exit;
}

$stmtCek = $conn->prepare("
    SELECT COUNT(*) 
    FROM laporan_harian
    WHERE id_mahasiswa = ? AND tanggal_submit = ?
");
$stmtCek->execute([$id_mahasiswa, $tanggal_submit]);
$already_exist = $stmtCek->fetchColumn();

if ($already_exist > 0) {
    $_SESSION['error'] = "Logbook untuk tanggal tersebut sudah ada.";
    header("Location: laporan_harian.php");
    exit;
}

$selisih_hari = floor(($tanggal_logbook - $tanggal_mulai) / (60 * 60 * 24));
$minggu_ke = floor($selisih_hari / 7) + 1;

$file_pendukung = null;

if (isset($_FILES['file_pendukung']) && $_FILES['file_pendukung']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($_FILES['file_pendukung']['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed)) {
        $_SESSION['error'] = "Format file tidak didukung.";
        header("Location: laporan_harian.php");
        exit;
    }

    if ($_FILES['file_pendukung']['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran file maksimal 2MB.";
        header("Location: laporan_harian.php");
        exit;
    }

    $upload_dir = "../uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir);
    }

    $filename = time() . "_" . uniqid() . "." . $extension;
    move_uploaded_file($_FILES['file_pendukung']['tmp_name'], $upload_dir . $filename);
    $file_pendukung = $filename;
}

$stmt = $conn->prepare("
    INSERT INTO laporan_harian (
        id_mahasiswa,
        id_pendaftaran,
        tanggal_submit,
        minggu_ke,
        kegiatan,
        jam_masuk,
        jam_keluar,
        file_pendukung,
        status
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu'
    )
");

$stmt->execute([
    $id_mahasiswa,
    $id_pendaftaran,
    $tanggal_submit,
    $minggu_ke,
    $kegiatan,
    $jam_masuk,
    $jam_keluar,
    $file_pendukung
]);

$_SESSION['success'] = "Logbook berhasil dikirim.";

header("Location: laporan_harian.php");
exit;