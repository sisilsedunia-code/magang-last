<?php
session_start();

require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /magang-last/login");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'];

// Fetch dosen details
$stmt = $conn->prepare("
    SELECT *
    FROM dosen
    WHERE id_dosen = ?
");
$stmt->execute([$id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
    die("Dosen tidak ditemukan!");
}

// Fetch mahasiswa bimbingan
$stmtMhs = $conn->prepare("
    SELECT m.id_mahasiswa, m.nama, m.NIM, pm.tempat_magang, pm.status_pendaftaran
    FROM mahasiswa m
    JOIN pendaftaran_magang pm ON m.id_mahasiswa = pm.id_mahasiswa
    WHERE pm.id_dosen = ?
    ORDER BY m.nama ASC
");
$stmtMhs->execute([$id]);
$data_mahasiswa = $stmtMhs->fetchAll(PDO::FETCH_ASSOC);

$stmtAdmin = $conn->prepare("
    SELECT *
    FROM admin
    WHERE id_admin = ?
");
$stmtAdmin->execute([$_SESSION['user']['id']]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

$page = "dosen";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dosen - Sistem Magang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .icon-box {
            width: 38px;
            height: 38px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }
        .table-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 20px;
            background: white;
        }
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-aktif { background-color: #dcfce7; color: #15803d; }
        .status-selesai { background-color: #dbeafe; color: #1d4ed8; }
        .status-menunggu { background-color: #fef08a; color: #a16207; }
        .status-belum { background-color: #f1f5f9; color: #64748b; }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- SIDEBAR -->
        <div class="sidebar d-flex flex-column">
            <div>
                <div class="logo-container">
                    <img src="assets/logo.png" class="logo-sidebar" alt="Logo">
                </div>

                <a href="beranda.php" class="<?= ($page == 'beranda') ? 'active' : '' ?>">
                    <i class="bi bi-grid me-2"></i> Beranda
                </a>

                <a href="mahasiswa.php" class="<?= ($page == 'mahasiswa') ? 'active' : '' ?>">
                    <i class="bi bi-people me-2"></i> Mahasiswa
                </a>

                <a href="dosen.php" class="<?= ($page == 'dosen') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge me-2"></i> Dosen
                </a>

                <a href="pengajuan.php" class="<?= ($page == 'pengajuan') ? 'active' : '' ?>">
                    <i class="bi bi-send me-2"></i> Pengajuan
                </a>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="p-4 w-100">
            <!-- HEADER -->
            <div class="header-top">
                <form action="search.php" method="GET" class="search-box m-0">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" placeholder="Cari mahasiswa, dosen, atau pengajuan..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" required>
                    <button type="submit" style="display: none;"></button>
                </form>

                <div class="profile-section">
                    <div class="profile-wrapper">
                        <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                            <div class="profile-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="profile-text">
                                <div class="name">Admin Sistem</div>
                                <div class="email">admin@magang.ac.id</div>
                            </div>
                        </div>

                        <div class="profile-dropdown" id="profileDropdown">
                            <div class="dropdown-header">Akun Saya</div>
                            <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                            <a href="/magang-last/logout" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TITLE -->
            <div class="mb-4">
                <a href="dosen.php" class="btn btn-light mb-3" style="border-radius: 10px;">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 style="color:#2d6cdf;">Detail Dosen Pembimbing</h4>
                <p class="text-muted mb-0" style="font-size:14px;">Informasi lengkap dan mahasiswa bimbingan.</p>
            </div>

            <div class="row g-4">
                <!-- LEFT COLUMN: PROFILE -->
                <div class="col-lg-4">
                    <div class="table-card p-0 overflow-hidden">
                        <div class="p-4 text-center" style="background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);">
                            <div class="profile-avatar mx-auto mb-3" style="width:90px; height:90px; font-size:32px; background:#2563eb; color:white; display: flex; align-items: center; justify-content: center; border-radius: 24px; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);">
                                <?= substr($dosen['nama'], 0, 1) ?>
                            </div>
                            <h5 class="fw-bold mb-1" style="color: #1e293b;"><?= htmlspecialchars($dosen['nama']) ?></h5>
                            <p class="text-muted small mb-0">NIDN: <?= htmlspecialchars($dosen['NIDN']) ?></p>
                        </div>

                        <div class="p-4 pt-0">
                            <div class="list-group list-group-flush border-top-0">
                                <div class="list-group-item px-0 border-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3">
                                            <i class="bi bi-credit-card-2-front"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">NIP</small>
                                            <span class="fw-semibold" style="color: #334155;"><?= htmlspecialchars($dosen['NIP'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item px-0 border-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <div class="text-truncate">
                                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Email</small>
                                            <span class="fw-semibold" style="color: #334155;"><?= htmlspecialchars($dosen['email'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item px-0 border-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3">
                                            <i class="bi bi-telephone"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">No. HP</small>
                                            <span class="fw-semibold" style="color: #334155;"><?= htmlspecialchars($dosen['no_hp'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item px-0 border-0 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3">
                                            <i class="bi bi-toggle-on"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Status</small>
                                            <span class="badge-status status-<?= strtolower($dosen['status']) ?>"><?= $dosen['status'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: MAHASISWA BIMBINGAN -->
                <div class="col-lg-8">
                    <div class="table-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0" style="color: #1e293b;">Mahasiswa Bimbingan</h5>
                            <span class="badge bg-light text-primary rounded-pill px-3">Total: <?= count($data_mahasiswa) ?> Mahasiswa</span>
                        </div>

                        <?php if ($data_mahasiswa): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Tempat Magang</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_mahasiswa as $mhs): ?>
                                            <tr>
                                                <td><?= $mhs['NIM'] ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($mhs['nama']) ?></td>
                                                <td><?= htmlspecialchars($mhs['tempat_magang'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <span class="badge-status status-<?= strtolower($mhs['status_pendaftaran'] ?? 'belum') ?>">
                                                        <?= $mhs['status_pendaftaran'] ?? 'Belum Magang' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="detail_mahasiswa.php?id=<?= $mhs['id_mahasiswa'] ?>" class="btn btn-sm btn-light text-primary" title="Detail Mahasiswa">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people fs-1 text-muted opacity-25"></i>
                                <p class="text-muted mt-2">Belum ada mahasiswa bimbingan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOGOUT MODAL -->
    <div class="modal-overlay" id="logoutModal">
        <div class="modal-box">
            <div class="modal-icon text-danger">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <h5>Keluar dari Akun?</h5>
            <p>Apakah Anda yakin ingin keluar dari sistem?</p>
            <div class="modal-actions">
                <button class="btn-cancel" id="btnBatal">Batal</button>
                <a href="/magang-last/logout" class="btn-logout" style="flex:1; text-align:center; text-decoration:none;">Ya, Keluar</a>
            </div>
        </div>
    </div>

    <!-- PROFILE MODAL -->
    <div class="modal-overlay" id="profileModal">
        <div class="profile-modal-box">
            <div class="profile-modal-header">
                <h5>Info Profil</h5>
                <button class="profile-modal-close" id="btnTutupProfil">&times;</button>
            </div>
            <div class="profile-modal-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="profile-modal-name">Admin Sistem</div>
            <div class="profile-modal-role">Admin</div>

            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="profile-detail-label">Surel</div>
                    <div class="profile-detail-value"><?= $admin['email'] ?></div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-telephone"></i></div>
                <div>
                    <div class="profile-detail-label">No. Telepon</div>
                    <div class="profile-detail-value"><?= $admin['no_hp'] ?></div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="profile-detail-label">Peran</div>
                    <div class="profile-detail-value">Admin Utama</div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>

</html>
