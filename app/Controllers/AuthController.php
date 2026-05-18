<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller {

    public function index() {
        if (isset($_SESSION['user'])) {
            return $this->redirectBasedOnRole($_SESSION['user']['role']);
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);
        
        // Render the auth/login view
        $this->view('auth/login', ['error' => $error]);
    }

    public function process() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->view('auth/login', ['error' => 'Email dan password wajib diisi!']);
            return;
        }

        $db = (new Database())->getConnection();
        $userModel = new User($db);
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $this->view('auth/login', ['error' => 'Email atau password salah!']);
            return;
        }

        if ($user['role'] === 'mahasiswa') {
            $this->view('auth/login', ['error' => 'Akun mahasiswa wajib masuk menggunakan Google SSO.']);
            return;
        }

        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'role' => $user['role']
            ];

            return $this->redirectBasedOnRole($user['role']);
        }

        $this->view('auth/login', ['error' => 'Email atau password salah!']);
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    private function redirectBasedOnRole($role) {
        // Fallback for procedural file paths; eventually replace with MVC routes (/dashboard/admin etc)
        switch ($role) {
            case 'mahasiswa':
                $this->redirect("/pages/mahasiswa/beranda.php");
                break;
            case 'dosen':
                $this->redirect("/pages/dosen/dashboard.php");
                break;
            case 'admin':
                $this->redirect("/pages/admin/beranda.php");
                break;
            case 'kps':
                $this->redirect("/pages/kaprodi/dashboard.php");
                break;
            default:
                $this->redirect("/");
        }
    }
}
