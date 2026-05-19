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

// Fetch student data
$stmtMhs = $conn->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmtMhs->execute([$user['id']]);
$mahasiswa = $stmtMhs->fetch(PDO::FETCH_ASSOC);

// Fetch internship data
$stmtPendaftaran = $conn->prepare("
    SELECT pm.*, d.nama as nama_dosen 
    FROM pendaftaran_magang pm 
    LEFT JOIN dosen d ON pm.id_dosen = d.id_dosen 
    WHERE pm.id_mahasiswa = ? AND pm.status_pendaftaran = 'Selesai'
    ORDER BY pm.id_pendaftaran DESC LIMIT 1
");
$stmtPendaftaran->execute([$user['id']]);
$pendaftaran = $stmtPendaftaran->fetch(PDO::FETCH_ASSOC);

if (!$pendaftaran) {
    die("Anda belum menyelesaikan program magang atau belum ada data magang yang selesai.");
}

// Fetch grade
$stmtNilai = $conn->prepare("SELECT * FROM nilai_akhir WHERE id_mahasiswa = ?");
$stmtNilai->execute([$user['id']]);
$nilai_akhir = $stmtNilai->fetch(PDO::FETCH_ASSOC);
$nilai = $nilai_akhir ? $nilai_akhir['nilai'] : 'N/A';

// Format Dates
setlocale(LC_TIME, 'id_ID.utf8', 'Indonesian');
$tgl_mulai = date('d F Y', strtotime($pendaftaran['tanggal_mulai']));
$tgl_selesai = date('d F Y', strtotime($pendaftaran['tanggal_selesai']));
$tgl_cetak = date('d F Y');

// Predikat
$predikat = "Sangat Baik";
if (is_numeric($nilai)) {
    if ($nilai >= 85) $predikat = "Sangat Memuaskan";
    elseif ($nilai >= 70) $predikat = "Memuaskan";
    elseif ($nilai >= 60) $predikat = "Cukup";
    else $predikat = "Kurang";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang - <?= htmlspecialchars($mahasiswa['nama']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }

        .cert-container {
            width: 1000px;
            height: 700px;
            background-color: #fff;
            position: relative;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            border-radius: 4px;
            overflow: hidden;
            box-sizing: border-box;
            padding: 30px;
        }

        /* Decorative Borders */
        .cert-border-outer {
            width: 100%;
            height: 100%;
            border: 2px solid #cbd5e1;
            padding: 10px;
            box-sizing: border-box;
        }
        
        .cert-border-inner {
            width: 100%;
            height: 100%;
            border: 8px solid #0f172a;
            position: relative;
            box-sizing: border-box;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f8fafc' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Golden Corner Accents */
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 4px solid #d4af37;
        }
        .corner-tl { top: 15px; left: 15px; border-right: none; border-bottom: none; }
        .corner-tr { top: 15px; right: 15px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: 15px; left: 15px; border-right: none; border-top: none; }
        .corner-br { bottom: 15px; right: 15px; border-left: none; border-top: none; }

        /* Content Structure */
        .cert-content {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            padding: 60px 80px;
            text-align: center;
            z-index: 10;
        }

        /* Typography */
        .cert-title {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 4px;
            margin: 0;
            text-transform: uppercase;
        }
        
        .cert-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            color: #64748b;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 50px;
        }

        .cert-text {
            font-size: 16px;
            color: #475569;
            margin-bottom: 25px;
        }

        .cert-name {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            color: #2563eb;
            margin: 0;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            display: inline-block;
            min-width: 500px;
        }

        .cert-detail {
            font-size: 14px;
            color: #64748b;
            margin-top: 8px;
            margin-bottom: 40px;
        }

        .cert-description {
            font-size: 18px;
            line-height: 1.6;
            color: #334155;
            max-width: 700px;
            margin: 0 auto 40px;
        }
        
        .cert-description strong {
            color: #0f172a;
        }

        /* Signatures */
        .cert-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding: 0 40px;
        }

        .signature-block {
            width: 250px;
        }

        .signature-line {
            border-bottom: 1px solid #475569;
            height: 60px;
            margin-bottom: 10px;
        }

        .signature-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 16px;
            margin: 0;
        }

        .signature-title {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Stamp/Badge */
        .cert-badge {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
            border: 4px solid #fff;
            outline: 2px solid #d4af37;
        }
        
        .cert-badge::before {
            content: "★";
            font-size: 40px;
        }

        /* Action Buttons */
        .action-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #fff;
            color: #475569;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
        }

        /* Print Styles */
        @media print {
            body { background: none; margin: 0; padding: 0; }
            .cert-container { 
                box-shadow: none; 
                width: 100%; 
                height: 100vh;
                margin: 0;
            }
            .action-buttons { display: none; }
            @page { size: landscape; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="cert-container" id="printableArea">
        <div class="cert-border-outer">
            <div class="cert-border-inner">
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>
                
                <div class="cert-content">
                    <h1 class="cert-title">Sertifikat Magang</h1>
                    <div class="cert-subtitle">Diberikan Sebagai Penghargaan Atas Dedikasi</div>
                    
                    <div class="cert-text">Diberikan kepada:</div>
                    
                    <h2 class="cert-name"><?= htmlspecialchars($mahasiswa['nama']) ?></h2>
                    <div class="cert-detail">NIM: <?= htmlspecialchars($mahasiswa['NIM']) ?> | Program Studi: <?= htmlspecialchars($mahasiswa['prodi']) ?></div>
                    
                    <p class="cert-description">
                        Telah berhasil menyelesaikan program magang di <strong><?= htmlspecialchars($pendaftaran['tempat_magang']) ?></strong> dengan sangat baik, 
                        terhitung mulai tanggal <strong><?= $tgl_mulai ?></strong> hingga <strong><?= $tgl_selesai ?></strong>. 
                        Selama program magang, yang bersangkutan telah menunjukkan dedikasi, kedisiplinan, dan hasil kerja 
                        dengan predikat <strong>"<?= $predikat ?>"</strong> (Nilai: <?= htmlspecialchars($nilai) ?>).
                    </p>
                    
                    <div class="cert-badge"></div>
                    
                    <div class="cert-signatures">
                        <div class="signature-block">
                            <div class="signature-line"></div>
                            <h3 class="signature-name"><?= htmlspecialchars($pendaftaran['nama_dosen'] ?? 'Dosen Pembimbing') ?></h3>
                            <div class="signature-title">Dosen Pembimbing</div>
                        </div>
                        <div class="signature-block">
                            <div class="signature-line" style="display: flex; align-items: flex-end; justify-content: center; font-size:12px; color:#64748b; padding-bottom:5px;">Tanggal: <?= $tgl_cetak ?></div>
                            <h3 class="signature-name">Program Studi</h3>
                            <div class="signature-title">Ketua Program Studi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="magang_aktif.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
            Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
            Cetak / Download PDF
        </button>
    </div>

</body>
</html>
