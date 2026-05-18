<?php
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$date = date('Y-m-d');
$filename = "rekap_nilai_magang_{$date}.xls";

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$stmt = $conn->query("
    SELECT
        m.NIM,
        m.nama,
        d.nama AS dosen,
        pm.tempat_magang,
        na.nilai
    FROM nilai_akhir na
    JOIN mahasiswa m ON na.id_mahasiswa = m.id_mahasiswa
    LEFT JOIN dosen d ON na.id_dosen = d.id_dosen
    LEFT JOIN pendaftaran_magang pm ON m.id_mahasiswa = pm.id_mahasiswa
    ORDER BY m.nama ASC
");

?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!--[if gte mso 9]>
<xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name>Rekap Nilai</x:Name>
<x:WorksheetOptions>
<x:DisplayGridlines/>
</x:WorksheetOptions>
</x:ExcelWorksheet>
</x:ExcelWorksheets>
</x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
    table { 
        border-collapse: collapse; 
        width: 100%; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }
    th { 
        background-color: #2563eb; 
        color: white; 
        font-weight: bold; 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        text-align: left;
    }
    td { 
        padding: 10px; 
        border: 1px solid #e2e8f0; 
        color: #334155;
    }
    tr:nth-child(even) { 
        background-color: #f8fafc; 
    }
    tr:hover {
        background-color: #f1f5f9;
    }
    .title {
        font-size: 18px;
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 10px;
        text-align: center;
    }
    .text-center {
        text-align: center;
    }
    .text-bold {
        font-weight: bold;
    }
</style>
</head>
<body>
    <div class="title">REKAPITULASI NILAI AKHIR MAHASISWA MAGANG</div>
    <div style="text-align: center; margin-bottom: 20px; color: #64748b;">Tanggal Cetak: <?= date('d F Y') ?></div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 150px;">NIM</th>
                <th style="width: 250px;">Nama Lengkap</th>
                <th style="width: 250px;">Dosen Pembimbing</th>
                <th style="width: 250px;">Tempat Magang</th>
                <th style="width: 100px; text-align: center;">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td>'<?= $row['NIM'] ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['dosen'] ?? 'Belum ditentukan') ?></td>
                    <td><?= htmlspecialchars($row['tempat_magang'] ?? '-') ?></td>
                    <td class="text-center text-bold"><?= $row['nilai'] ?? '0' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
<?php exit; ?>
