<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Update any pendaftaran_magang to 'Selesai' if the student has a 'Disetujui' laporan akhir
$stmt = $conn->query("
    UPDATE pendaftaran_magang pm
    JOIN laporan_akhir la ON pm.id_mahasiswa = la.id_mahasiswa
    SET pm.status_pendaftaran = 'Selesai'
    WHERE la.status_review = 'Disetujui' AND pm.status_pendaftaran = 'Aktif'
");

echo "Database fixed! Updated " . $stmt->rowCount() . " records to 'Selesai'.";
unlink(__FILE__);
