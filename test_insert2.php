<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$nim     = 'e41252749';
$nama    = 'Test 2';
$email   = 'e41252749@student.polije.ac.id';
$prodi   = 'TIF';
$telepon = '081234567890';
$password = '123456';

try {
    $stmt = $conn->prepare("
        INSERT INTO mahasiswa (NIM, nama, email, prodi, no_hp, password) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nim, $nama, $email, $prodi, $telepon, $password]);
    echo "SUCCESS_NEW";
} catch (PDOException $e) {
    echo "ERROR CODE: " . $e->getCode() . "\n";
    echo "ERROR MESSAGE: " . $e->getMessage() . "\n";
}
