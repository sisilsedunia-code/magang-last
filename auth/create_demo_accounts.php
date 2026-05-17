<?php
/**
 * Run this script once (CLI or browser) to create demo accounts:
 * - dosen demo: demo.dosen@polije.ac.id / demo1234
 * - kaprodi demo: demo.kaprodi@polije.ac.id / demo1234
 *
 * Usage (CLI): php auth/create_demo_accounts.php
 */

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Demo dosen
    $demoDosenEmail = 'demo.dosen@polije.ac.id';
    $demoDosenPassword = 'demo1234';

    $stmt = $conn->prepare("SELECT * FROM dosen WHERE email = ?");
    $stmt->execute([$demoDosenEmail]);
    if (!$stmt->fetch()) {
        $hash = password_hash($demoDosenPassword, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO dosen (NIP, NIDN, nama, email, password, status, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute(['000000', '000000', 'Demo Dosen', $demoDosenEmail, $hash, 'Aktif', '081234567890']);
        echo "Demo dosen created: $demoDosenEmail / $demoDosenPassword\n";
    } else {
        echo "Demo dosen already exists: $demoDosenEmail\n";
    }

    // Demo kaprodi (dosen + kps entry)
    $demoKaprodiEmail = 'demo.kaprodi@polije.ac.id';
    $demoKaprodiPassword = 'demo1234';

    $stmt = $conn->prepare("SELECT * FROM dosen WHERE email = ?");
    $stmt->execute([$demoKaprodiEmail]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $hash = password_hash($demoKaprodiPassword, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO dosen (NIP, NIDN, nama, email, password, status, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute(['000001', '000001', 'Demo Kaprodi', $demoKaprodiEmail, $hash, 'Aktif', '081234567891']);
        $id = $conn->lastInsertId();
        $insk = $conn->prepare("INSERT INTO kps (id_dosen, jabatan, program_studi) VALUES (?, ?, ?)");
        $insk->execute([$id, 'Ketua Program Studi (Demo)', 'Teknik Informatika']);
        echo "Demo kaprodi created: $demoKaprodiEmail / $demoKaprodiPassword\n";
    } else {
        echo "Demo kaprodi already exists: $demoKaprodiEmail\n";
    }

    echo "Done. Use the credentials above to login via form.\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
