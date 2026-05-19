<?php
session_start();

require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /magang-last/login");
    exit;
}

$user = $_SESSION['user'];

$db = new Database();
$conn = $db->getConnection();

$page = "riwayat";

$stmtMhs = $conn->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmtMhs->execute([$user['id']]);
$mahasiswa = $stmtMhs->fetch(PDO::FETCH_ASSOC);

$nama_mahasiswa = $mahasiswa['nama'];
$nim = $mahasiswa['NIM'];
$prodi = $mahasiswa['prodi'];
$email = $mahasiswa['email'];

// Fetch logbook harian
$stmtRiwayat = $conn->prepare("
    SELECT id_laporan_harian, kegiatan, status, tanggal_submit, minggu_ke, catatan_dosen
    FROM laporan_harian
    WHERE id_mahasiswa = ?
    ORDER BY tanggal_submit DESC
");
$stmtRiwayat->execute([$user['id']]);
$data_logbook = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

// Fetch laporan akhir
$stmtLaporan = $conn->prepare("SELECT * FROM laporan_akhir WHERE id_mahasiswa = ? ORDER BY id_laporan_akhir DESC");
$stmtLaporan->execute([$user['id']]);
$data_laporan_akhir = $stmtLaporan->fetchAll(PDO::FETCH_ASSOC);

// Fetch pengajuan disetujui
$stmtPengajuan = $conn->prepare("SELECT * FROM pengajuan WHERE id_mahasiswa = ? AND status = 'Disetujui' ORDER BY id_pengajuan DESC LIMIT 1");
$stmtPengajuan->execute([$user['id']]);
$pengajuan = $stmtPengajuan->fetch(PDO::FETCH_ASSOC);

// Build unified timeline
$timeline = [];

// Add logbook entries
foreach ($data_logbook as $item) {
    $timeline[] = [
        'type' => 'logbook',
        'date' => $item['tanggal_submit'],
        'title' => $item['kegiatan'],
        'status' => $item['status'],
        'minggu_ke' => $item['minggu_ke'],
        'catatan_dosen' => $item['catatan_dosen'] ?? null,
    ];
}

// Add laporan akhir entries
foreach ($data_laporan_akhir as $lap) {
    $status_review = $lap['status_review'] ?? 'Menunggu';
    $timeline[] = [
        'type' => 'laporan_akhir',
        'date' => $lap['created_at'] ?? $lap['tanggal_submit'] ?? date('Y-m-d H:i:s'),
        'title' => $lap['judul_laporan'],
        'status' => $status_review,
        'catatan_dosen' => $lap['catatan_dosen'] ?? null,
        'file' => $lap['file_laporan'] ?? null,
    ];
}

// Sort by date DESC
usort($timeline, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Magang - SIMMAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/mahasiswa_navbar.css">
    <link rel="stylesheet" href="assets/css/riwayat.css">
    <link rel="stylesheet" href="assets/css/mahasiswa.css">
</head>
<body>

    <!-- Top Navigation -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <img src="assets/logo.png" alt="SIMMAG">
        </div>
        <div class="nav-center">
            <a href="magang_aktif.php" class="<?= ($page == 'magang_aktif') ? 'active' : '' ?>">Beranda</a>
            <a href="laporan_harian.php" class="<?= ($page == 'laporan_harian') ? 'active' : '' ?>">Logbook Harian</a>
            <a href="laporan_akhir.php" class="<?= ($page == 'laporan_akhir') ? 'active' : '' ?>">Laporan Akhir</a>
            <a href="riwayat.php" class="<?= ($page == 'riwayat') ? 'active' : '' ?>">Riwayat</a>
        </div>
        <div class="nav-right">
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                        <div class="profile-avatar"><i class="bi bi-person"></i></div>
                        <div class="profile-text">
                            <div class="name"><?= $nama_mahasiswa ?></div>
                            <div class="role">Mahasiswa</div>
                        </div>
                        <i class="bi bi-chevron-down ms-2 text-muted" style="font-size: 12px;"></i>
                    </div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">Akun Saya</div>
                        <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                        <div class="dropdown-divider my-1"></div>
                        <a href="/magang-last/logout" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="mx-auto" style="max-width: 800px;">

            <div class="d-flex align-items-center mb-4">
                <div class="bg-white rounded-circle shadow-sm me-3 text-primary d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold" style="color: #1e293b;">Riwayat Aktivitas</h4>
                    <p class="text-muted mb-0" style="font-size: 14px;">Pantau seluruh aktivitas magang Anda di sini</p>
                </div>
            </div>

            <?php if ($pengajuan || count($timeline) > 0): ?>
            <div class="timeline-container">

                <?php if ($pengajuan): ?>
                <div class="timeline-item">
                    <div class="timeline-icon success"><i class="bi bi-file-earmark-check"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date"><i class="bi bi-calendar3"></i> <?= date('d F Y, H:i', strtotime($pengajuan['created_at'])) ?></div>
                        <h6 class="timeline-title text-success">Pengajuan Proposal Disetujui</h6>
                        <p class="timeline-desc">Pengajuan proposal magang Anda di <?= htmlspecialchars($pengajuan['nama_perusahaan']) ?> telah disetujui.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon primary"><i class="bi bi-play-circle"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($pengajuan['tanggal_mulai'])) ?></div>
                        <h6 class="timeline-title">Mulai Program Magang</h6>
                        <p class="timeline-desc">Hari pertama pelaksanaan program magang di <?= htmlspecialchars($pengajuan['nama_perusahaan']) ?>.</p>
                    </div>
                </div>
                <?php endif; ?>

                <?php foreach ($timeline as $item): ?>
                <div class="timeline-item">
                    <?php
                        // Determine icon class & icon
                        if ($item['status'] == 'Disetujui') {
                            $iconClass = 'success';
                            $iconName = 'bi-check-circle';
                        } elseif ($item['status'] == 'Ditolak') {
                            $iconClass = 'danger';
                            $iconName = 'bi-x-circle';
                        } else {
                            $iconClass = 'warning';
                            $iconName = 'bi-clock';
                        }

                        // Determine type badge
                        if ($item['type'] === 'laporan_akhir') {
                            $typeBadge = '<span class="badge rounded-pill" style="background:rgba(124,58,237,0.1);color:#7c3aed;font-size:11px;font-weight:600;padding:4px 10px;"><i class="bi bi-file-earmark-text me-1"></i>Laporan Akhir</span>';
                        } else {
                            $typeBadge = '<span class="badge rounded-pill" style="background:rgba(37,99,235,0.1);color:#2563eb;font-size:11px;font-weight:600;padding:4px 10px;"><i class="bi bi-journal-text me-1"></i>Logbook Harian</span>';
                        }

                        // Title color
                        $titleClass = '';
                        if ($item['status'] == 'Disetujui') $titleClass = 'text-success';
                        elseif ($item['status'] == 'Ditolak') $titleClass = 'text-danger';
                    ?>
                    <div class="timeline-icon <?= $iconClass ?>"><i class="bi <?= $iconName ?>"></i></div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="timeline-date mb-0"><i class="bi bi-calendar3"></i> <?= date('d F Y, H:i', strtotime($item['date'])) ?></div>
                            <?= $typeBadge ?>
                        </div>
                        <h6 class="timeline-title <?= $titleClass ?>"><?= htmlspecialchars($item['title']) ?></h6>
                        <p class="timeline-desc">
                            <?php if ($item['type'] === 'logbook'): ?>
                                Status logbook: <strong><?= $item['status'] ?></strong> untuk minggu ke-<?= $item['minggu_ke'] ?>.
                            <?php else: ?>
                                Status laporan akhir: <strong><?= $item['status'] ?></strong>.
                                <?php if (!empty($item['file'])): ?>
                                    <a href="../../uploads/<?= htmlspecialchars($item['file']) ?>" target="_blank" class="ms-1 text-decoration-none" style="font-size:13px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Lihat File
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($item['status'] == 'Ditolak' && !empty($item['catatan_dosen'])): ?>
                                <span class="d-block text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>Catatan Dosen: <?= htmlspecialchars($item['catatan_dosen']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted opacity-25"></i>
                <p class="text-muted mt-2">Belum ada riwayat aktivitas.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal-overlay" id="profileModal">
        <div class="profile-modal-box">
            <div class="profile-modal-header">
                <h5>Info Profil</h5>
                <button class="profile-modal-close" id="btnTutupProfil">&times;</button>
            </div>
            <div class="profile-modal-avatar"><i class="bi bi-person"></i></div>
            <div class="profile-modal-name"><?= $nama_mahasiswa ?></div>
            <div class="profile-modal-role">Mahasiswa</div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-credit-card-2-front"></i></div>
                <div>
                    <div class="profile-detail-label">NIM</div>
                    <div class="profile-detail-value"><?= $nim ?></div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-book"></i></div>
                <div>
                    <div class="profile-detail-label">Program Studi</div>
                    <div class="profile-detail-value"><?= $prodi ?></div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="profile-detail-label">Surel</div>
                    <div class="profile-detail-value"><?= $email ?></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/mahasiswa.js"></script>
</body>
</html>


