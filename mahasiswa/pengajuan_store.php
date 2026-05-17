<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /login");
    exit;
}

$user = $_SESSION['user'];

$db = new Database();
$conn = $db->getConnection();

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = $_POST;

$stmtCheck = $conn->prepare("
    SELECT COUNT(*)
    FROM pengajuan
    WHERE id_mahasiswa = ? AND status IN ('Menunggu', 'Disetujui')
");
$stmtCheck->execute([$user['id']]);
$existing = $stmtCheck->fetchColumn();

if ($existing > 0) {
    $_SESSION['error'] = "Anda masih memiliki pengajuan aktif.";
    header("Location: pengajuan.php");
    exit;
}

$judul_proposal = trim($data['judul_proposal'] ?? '');
$bidang = trim($data['bidang'] ?? '');

if (empty($judul_proposal) || empty($bidang)) {
    $_SESSION['error'] = "Semua field wajib harus diisi.";
    header("Location: pengajuan.php");
    exit;
}

$tanggal_mulai = strtotime($data['tanggal_mulai']);
$tanggal_selesai = strtotime($data['tanggal_selesai']);
$today = strtotime(date('Y-m-d'));

if ($tanggal_mulai < $today) {
    $_SESSION['error'] = "Tanggal mulai tidak boleh kurang dari hari ini.";
    header("Location: pengajuan.php");
    exit;
}

if ($tanggal_selesai <= $tanggal_mulai) {
    $_SESSION['error'] = "Tanggal selesai harus lebih besar dari tanggal mulai.";
    header("Location: pengajuan.php");
    exit;
}

if (!isset($_FILES['file_proposal']) || $_FILES['file_proposal']['error'] !== 0) {
    $_SESSION['error'] = "File proposal wajib diupload.";
    header("Location: pengajuan.php");
    exit;
}

$file = $_FILES['file_proposal'];
$allowed = ['pdf'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed)) {
    $_SESSION['error'] = "File proposal harus berupa PDF.";
    header("Location: pengajuan.php");
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    $_SESSION['error'] = "Ukuran file maksimal 5MB.";
    header("Location: pengajuan.php");
    exit;
}

$upload_dir = "../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir);
}

$filename = time() . "_" . uniqid() . "." . $extension;

if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
    $_SESSION['error'] = "Gagal upload file proposal.";
    header("Location: pengajuan.php");
    exit;
}

if (!empty($_POST['id_mitra'])) {
    $stmtMitra = $conn->prepare("
        SELECT *
        FROM mitra
        WHERE id_mitra = ?
    ");
    $stmtMitra->execute([$_POST['id_mitra']]);
    $mitra = $stmtMitra->fetch(PDO::FETCH_ASSOC);

    if (!$mitra) {
        $_SESSION['error'] = "Mitra tidak ditemukan.";
        header("Location: pengajuan.php");
        exit;
    }

    $nama_perusahaan = $mitra['nama'];
    $alamat = $mitra['alamat'];
    $provinsi = $mitra['provinsi'];
    $kota = $mitra['kota'];
    $kecamatan = $mitra['kecamatan'];
    $kode_pos = $mitra['kode_pos'];
} else {
    $nama_perusahaan = trim($_POST['nama_perusahaan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $provinsi = trim($_POST['provinsi'] ?? '');
    $kota = trim($_POST['kota'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $kode_pos = trim($_POST['kode_pos'] ?? '');

    if (empty($nama_perusahaan) || empty($alamat) || empty($provinsi) || empty($kota)) {
        $_SESSION['error'] = "Data perusahaan wajib diisi.";
        header("Location: pengajuan.php");
        exit;
    }
}

$stmt = $conn->prepare("
    INSERT INTO pengajuan (
        id_mahasiswa,
        jenis_perusahaan,
        nama_perusahaan,
        alamat,
        provinsi,
        kota,
        kecamatan,
        kode_pos,
        judul_proposal,
        bidang,
        tanggal_mulai,
        tanggal_selesai,
        catatan,
        file_proposal,
        status
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu'
    )
");

try {
    $stmt->execute([
        $user['id'],
        $data['jenis_perusahaan'] ?? null,
        $nama_perusahaan,
        $alamat,
        $provinsi,
        $kota,
        $kecamatan,
        $kode_pos,
        $judul_proposal,
        $bidang,
        $data['tanggal_mulai'],
        $data['tanggal_selesai'],
        $data['catatan'] ?? null,
        $filename
    ]);

    $_SESSION['success'] = "Pengajuan berhasil dikirim.";
    header("Location: pengajuan.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem.";
    header("Location: pengajuan.php");
    exit;
}
