<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: /magang-last/login");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$page = "mitra";
$search = $_GET['q'] ?? '';

$query = "SELECT * FROM mitra WHERE 1=1";
$params = [];
if (!empty($search)) {
    $query .= " AND (nama LIKE ? OR alamat LIKE ? OR kota LIKE ? OR provinsi LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}
$query .= " ORDER BY nama ASC";

$stmtMitra = $conn->prepare($query);
$stmtMitra->execute($params);
$data_mitra = $stmtMitra->fetchAll(PDO::FETCH_ASSOC);

$stmtAdmin = $conn->prepare("SELECT * FROM admin WHERE id_admin = ?");
$stmtAdmin->execute([$_SESSION['user']['id']]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mitra - Sistem Magang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="d-flex">
    <div class="sidebar d-flex flex-column">
        <div>
            <div class="logo-container">
                <img src="assets/logo.png" class="logo-sidebar" alt="Logo">
            </div>
            <a href="beranda.php"><i class="bi bi-grid me-2"></i> Beranda</a>
            <a href="mahasiswa.php"><i class="bi bi-people me-2"></i> Mahasiswa</a>
            <a href="dosen.php"><i class="bi bi-person-badge me-2"></i> Dosen</a>
            <a href="mitra.php" class="active"><i class="bi bi-building me-2"></i> Mitra</a>
            <a href="pengajuan.php"><i class="bi bi-send me-2"></i> Pengajuan</a>
        </div>
    </div>

    <div class="p-4 w-100">
        <div class="header-top">
            <form action="mitra.php" method="GET" class="search-box m-0">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="Cari nama perusahaan atau kota..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" style="display:none;"></button>
            </form>
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                        <div class="profile-avatar"><i class="bi bi-person"></i></div>
                        <div class="profile-text">
                            <div class="name">Admin Sistem</div>
                            <div class="email"><?= htmlspecialchars($admin['email'] ?? 'admin@magang.ac.id') ?></div>
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

        <div class="mb-4">
            <h4 style="color:#2d6cdf;">Manajemen Mitra Perusahaan</h4>
            <p class="text-muted mb-0" style="font-size:14px;">Kelola data perusahaan mitra yang tersedia untuk mahasiswa magang.</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header">
                <h5 class="m-0" style="color:#1e293b;">Daftar Perusahaan Mitra</h5>
                <button class="btn-add" id="btnTambahMitra">
                    <i class="bi bi-plus-lg me-2"></i> Tambah Perusahaan
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead>
                        <tr>
                            <th>Nama Perusahaan</th>
                            <th>Kota</th>
                            <th>Provinsi</th>
                            <th>Alamat</th>
                            <th>Kode Pos</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_mitra) > 0): ?>
                            <?php foreach ($data_mitra as $m): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="profile-avatar me-2" style="background:#eef2ff;color:#2563eb;font-size:14px;font-weight:700;">
                                            <?= strtoupper(substr($m['nama'], 0, 1)) ?>
                                        </div>
                                        <span style="font-weight:600;"><?= htmlspecialchars($m['nama']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($m['kota']) ?></td>
                                <td><?= htmlspecialchars($m['provinsi']) ?></td>
                                <td style="max-width:220px;font-size:13px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($m['alamat']) ?>">
                                    <?= htmlspecialchars($m['alamat']) ?>
                                </td>
                                <td><code><?= htmlspecialchars($m['kode_pos']) ?></code></td>
                                <td class="text-center">
                                    <a href="#" class="btn-action btn-view"
                                        data-nama="<?= htmlspecialchars($m['nama']) ?>"
                                        data-alamat="<?= htmlspecialchars($m['alamat']) ?>"
                                        data-provinsi="<?= htmlspecialchars($m['provinsi']) ?>"
                                        data-kota="<?= htmlspecialchars($m['kota']) ?>"
                                        data-kecamatan="<?= htmlspecialchars($m['kecamatan']) ?>"
                                        data-kodepos="<?= htmlspecialchars($m['kode_pos']) ?>"
                                        title="Lihat Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="#" class="btn-action btn-edit"
                                        data-id="<?= $m['id_mitra'] ?>"
                                        data-nama="<?= htmlspecialchars($m['nama']) ?>"
                                        data-alamat="<?= htmlspecialchars($m['alamat']) ?>"
                                        data-provinsi="<?= htmlspecialchars($m['provinsi']) ?>"
                                        data-kota="<?= htmlspecialchars($m['kota']) ?>"
                                        data-kecamatan="<?= htmlspecialchars($m['kecamatan']) ?>"
                                        data-kodepos="<?= htmlspecialchars($m['kode_pos']) ?>"
                                        title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="#" class="btn-action btn-delete" data-id="<?= $m['id_mitra'] ?>" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                                    Belum ada data perusahaan mitra.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Detail -->
<div class="modal-overlay" id="viewModal">
    <div class="modal-box" style="width:480px;text-align:left;padding:28px;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h5 class="m-0 fw-bold" style="color:#1e293b;"><i class="bi bi-building me-2 text-primary"></i>Detail Perusahaan</h5>
            <button class="profile-modal-close" id="btnTutupView">&times;</button>
        </div>
        <div class="text-center mb-4">
            <div class="profile-modal-avatar" id="view-initial" style="font-size:28px;font-weight:700;width:70px;height:70px;margin:0 auto 12px;"></div>
            <div class="profile-modal-name" id="view-nama"></div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <div>
                <div class="profile-detail-label">Alamat Lengkap</div>
                <div class="profile-detail-value" id="view-alamat"></div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-map-fill"></i></div>
            <div>
                <div class="profile-detail-label">Kecamatan</div>
                <div class="profile-detail-value" id="view-kecamatan"></div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-buildings-fill"></i></div>
            <div>
                <div class="profile-detail-label">Kota / Kabupaten</div>
                <div class="profile-detail-value" id="view-kota"></div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-flag-fill"></i></div>
            <div>
                <div class="profile-detail-label">Provinsi</div>
                <div class="profile-detail-value" id="view-provinsi"></div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-mailbox2-flag"></i></div>
            <div>
                <div class="profile-detail-label">Kode Pos</div>
                <div class="profile-detail-value" id="view-kodepos"></div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <button class="btn-cancel" id="btnTutupView2" style="border:none;padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box" style="width:520px;text-align:left;padding:28px;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h5 class="m-0 fw-bold" style="color:#1e293b;"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Perusahaan Mitra</h5>
            <button class="profile-modal-close" id="btnTutupAdd">&times;</button>
        </div>
        <form action="tambah_mitra_action.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Nama Perusahaan <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" placeholder="Contoh: PT Semen Indonesia" required style="border-radius:8px;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea name="alamat" class="form-control" rows="2" placeholder="Nama Jalan, Gedung, No." required style="border-radius:8px;resize:none;"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Provinsi <span class="text-danger">*</span></label>
                    <input type="text" name="provinsi" class="form-control" placeholder="Provinsi" required style="border-radius:8px;">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kota / Kabupaten <span class="text-danger">*</span></label>
                    <input type="text" name="kota" class="form-control" placeholder="Kota/Kabupaten" required style="border-radius:8px;">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kecamatan <span class="text-danger">*</span></label>
                    <input type="text" name="kecamatan" class="form-control" placeholder="Kecamatan" required style="border-radius:8px;">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kode Pos <span class="text-danger">*</span></label>
                    <input type="text" name="kode_pos" class="form-control" placeholder="Kode Pos" required style="border-radius:8px;">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn-cancel" id="btnBatalAdd" style="border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Batal</button>
                <button type="submit" class="btn-add">Simpan Perusahaan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box" style="width:520px;text-align:left;padding:28px;">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h5 class="m-0 fw-bold" style="color:#1e293b;"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Perusahaan Mitra</h5>
            <button class="profile-modal-close" id="btnTutupEdit">&times;</button>
        </div>
        <form action="edit_mitra_action.php" method="POST">
            <input type="hidden" name="id_mitra" id="edit-id">
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Nama Perusahaan <span class="text-danger">*</span></label>
                <input type="text" name="nama" id="edit-nama" class="form-control" required style="border-radius:8px;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea name="alamat" id="edit-alamat" class="form-control" rows="2" required style="border-radius:8px;resize:none;"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Provinsi <span class="text-danger">*</span></label>
                    <input type="text" name="provinsi" id="edit-provinsi" class="form-control" required style="border-radius:8px;">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kota / Kabupaten <span class="text-danger">*</span></label>
                    <input type="text" name="kota" id="edit-kota" class="form-control" required style="border-radius:8px;">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kecamatan <span class="text-danger">*</span></label>
                    <input type="text" name="kecamatan" id="edit-kecamatan" class="form-control" required style="border-radius:8px;">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label fw-semibold" style="font-size:13px;color:#475569;">Kode Pos <span class="text-danger">*</span></label>
                    <input type="text" name="kode_pos" id="edit-kodepos" class="form-control" required style="border-radius:8px;">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn-cancel" id="btnBatalEdit" style="border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;">Batal</button>
                <button type="submit" class="btn-add">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-exclamation-triangle"></i></div>
        <h5>Hapus Perusahaan?</h5>
        <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
        <form action="hapus_mitra_action.php" method="POST">
            <input type="hidden" name="id_mitra" id="delete-id">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="btnBatalDelete">Batal</button>
                <button type="submit" class="btn-logout">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Logout -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-box-arrow-right"></i></div>
        <h5>Keluar dari Akun?</h5>
        <p>Apakah Anda yakin ingin keluar dari sistem?</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatal">Batal</button>
            <a href="/magang-last/logout" class="btn-logout" style="flex:1;text-align:center;text-decoration:none;padding:10px;border-radius:8px;font-weight:600;">Ya, Keluar</a>
        </div>
    </div>
</div>

<!-- Modal Profil -->
<div class="modal-overlay" id="profileModal">
    <div class="profile-modal-box">
        <div class="profile-modal-header">
            <h5>Info Profil</h5>
            <button class="profile-modal-close" id="btnTutupProfil">&times;</button>
        </div>
        <div class="profile-modal-avatar"><i class="bi bi-person"></i></div>
        <div class="profile-modal-name">Admin Sistem</div>
        <div class="profile-modal-role">Admin</div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
            <div>
                <div class="profile-detail-label">Surel</div>
                <div class="profile-detail-value"><?= htmlspecialchars($admin['email'] ?? '-') ?></div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-telephone"></i></div>
            <div>
                <div class="profile-detail-label">No. Telepon</div>
                <div class="profile-detail-value"><?= htmlspecialchars($admin['no_hp'] ?? '-') ?></div>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Profile
    const profileToggle = document.getElementById("profileToggle");
    const profileDropdown = document.getElementById("profileDropdown");
    profileToggle.addEventListener("click", e => { e.stopPropagation(); profileDropdown.classList.toggle("show"); });
    document.addEventListener("click", () => profileDropdown.classList.remove("show"));

    document.getElementById("btnProfil").addEventListener("click", e => { e.preventDefault(); document.getElementById("profileModal").classList.add("show"); });
    document.getElementById("btnTutupProfil").addEventListener("click", () => document.getElementById("profileModal").classList.remove("show"));
    document.getElementById("btnKeluar").addEventListener("click", e => { e.preventDefault(); document.getElementById("logoutModal").classList.add("show"); });
    document.getElementById("btnBatal").addEventListener("click", () => document.getElementById("logoutModal").classList.remove("show"));

    // Tambah
    document.getElementById("btnTambahMitra").addEventListener("click", () => document.getElementById("addModal").classList.add("show"));
    document.getElementById("btnTutupAdd").addEventListener("click", () => document.getElementById("addModal").classList.remove("show"));
    document.getElementById("btnBatalAdd").addEventListener("click", () => document.getElementById("addModal").classList.remove("show"));

    // View
    document.querySelectorAll(".btn-view").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const nama = this.dataset.nama;
            document.getElementById("view-initial").textContent = nama.charAt(0).toUpperCase();
            document.getElementById("view-nama").textContent = nama;
            document.getElementById("view-alamat").textContent = this.dataset.alamat;
            document.getElementById("view-kecamatan").textContent = this.dataset.kecamatan;
            document.getElementById("view-kota").textContent = this.dataset.kota;
            document.getElementById("view-provinsi").textContent = this.dataset.provinsi;
            document.getElementById("view-kodepos").textContent = this.dataset.kodepos;
            document.getElementById("viewModal").classList.add("show");
        });
    });
    document.getElementById("btnTutupView").addEventListener("click", () => document.getElementById("viewModal").classList.remove("show"));
    document.getElementById("btnTutupView2").addEventListener("click", () => document.getElementById("viewModal").classList.remove("show"));

    // Edit
    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("edit-id").value = this.dataset.id;
            document.getElementById("edit-nama").value = this.dataset.nama;
            document.getElementById("edit-alamat").value = this.dataset.alamat;
            document.getElementById("edit-provinsi").value = this.dataset.provinsi;
            document.getElementById("edit-kota").value = this.dataset.kota;
            document.getElementById("edit-kecamatan").value = this.dataset.kecamatan;
            document.getElementById("edit-kodepos").value = this.dataset.kodepos;
            document.getElementById("editModal").classList.add("show");
        });
    });
    document.getElementById("btnTutupEdit").addEventListener("click", () => document.getElementById("editModal").classList.remove("show"));
    document.getElementById("btnBatalEdit").addEventListener("click", () => document.getElementById("editModal").classList.remove("show"));

    // Delete
    document.querySelectorAll(".btn-delete").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("delete-id").value = this.dataset.id;
            document.getElementById("deleteModal").classList.add("show");
        });
    });
    document.getElementById("btnBatalDelete").addEventListener("click", () => document.getElementById("deleteModal").classList.remove("show"));
});
</script>
</body>
</html>
