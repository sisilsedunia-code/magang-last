<?php
// OAuth callback handler (auth folder)
session_start();

require_once __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/google.php';
require_once __DIR__ . '/../config/userchecker.php';

// Basic validation
if (!isset($_GET['state']) || !isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    $_SESSION['error'] = 'Invalid OAuth state. Silakan coba lagi.';
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Google OAuth error: ' . htmlspecialchars($_GET['error']);
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Tidak ada kode otorisasi diterima.';
    header('Location: ../login.php');
    exit;
}

$code = $_GET['code'];

// Exchange code for tokens
$post = http_build_query([
    'code' => $code,
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'redirect_uri' => $config['redirect_uri'],
    'grant_type' => 'authorization_code'
]);

$ch = curl_init($config['token_uri']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$resp = curl_exec($ch);
if ($resp === false) {
    $_SESSION['error'] = 'Gagal menghubungi server Google.';
    header('Location: ../login.php');
    exit;
}
$data = json_decode($resp, true);
curl_close($ch);

if (!isset($data['access_token'])) {
    $_SESSION['error'] = 'Token tidak diterima dari Google.';
    header('Location: ../login.php');
    exit;
}

$accessToken = $data['access_token'];

// Get user info
$ch = curl_init($config['userinfo_uri']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$uiResp = curl_exec($ch);
if ($uiResp === false) {
    $_SESSION['error'] = 'Gagal mengambil informasi pengguna dari Google.';
    header('Location: ../login.php');
    exit;
}
$userInfo = json_decode($uiResp, true);
curl_close($ch);

$email = $userInfo['email'] ?? '';
$name = $userInfo['name'] ?? ($userInfo['given_name'] ?? '');

if (empty($email)) {
    $_SESSION['error'] = 'Email tidak tersedia dari Google.';
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$userModel = new UserModel($conn);
$user = $userModel->findUser($email);

// Admin must login via form
if ($user && $user['role'] === 'admin') {
    $_SESSION['error'] = 'Akun admin harus masuk melalui form (email/password).';
    header('Location: ../login.php');
    exit;
}

// Determine domain rules
$domain = strtolower(substr(strrchr($email, "@"), 1));

if ($domain === 'student.polije.ac.id') {
    // mahasiswa
    if (!$user || $user['role'] !== 'mahasiswa') {
        $_SESSION['error'] = 'Akun mahasiswa tidak ditemukan. Silakan registrasi terlebih dahulu.';
        header('Location: ../register.php');
        exit;
    }
} elseif ($domain === 'polije.ac.id') {
    // dosen or kaprodi (kps)
    if (!$user) {
        $_SESSION['error'] = 'Akun dosen/kaprodi tidak ditemukan di sistem.';
        header('Location: ../login.php');
        exit;
    }
    if ($user['role'] === 'mahasiswa') {
        $_SESSION['error'] = 'Email ini terdaftar sebagai mahasiswa. Gunakan akun student.polije.ac.id.';
        header('Location: ../login.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'Domain email tidak diizinkan. Gunakan akun polije atau student.polije.';
    header('Location: ../login.php');
    exit;
}

// Login success: set session and redirect according to role
$_SESSION['user'] = [
    'id' => $user['id'],
    'nama' => $user['nama'],
    'role' => $user['role']
];

switch ($user['role']) {
    case 'mahasiswa':
        header('Location: ../mahasiswa/beranda.php');
        break;
    case 'dosen':
        header('Location: ../dosen/dashboard.php');
        break;
    case 'kps':
        header('Location: ../kaprodi/dashboard.php');
        break;
    default:
        header('Location: ../index.php');
}
exit;
