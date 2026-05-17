<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Magang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .sso-divider {
            margin: 12px 0 10px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
        }

        .btn-google-sso {
            width: 100%;
            height: 52px;
            border-radius: 10px;
            border: 1px solid #dadce0;
            background: #fff;
            color: #3c4043;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 0 16px;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(60, 64, 67, 0.08);
            transition: background-color 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .btn-google-sso:hover {
            background: #f8f9fa;
            border-color: #c6cdd4;
            box-shadow: 0 2px 6px rgba(60, 64, 67, 0.12);
            color: #3c4043;
        }

        .btn-google-sso img {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .sso-note {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
            text-align: center;
            line-height: 1.45;
        }
    </style>
</head>
<body>

<div class="shape shape-1"></div>
<div class="shape shape-2"></div>

<div class="login-card">

    <div class="login-header">
        <div class="login-logo">
            <img src="assets/logoLogin.png" alt="Logo Sistem">
        </div>
        <h4>Selamat Datang</h4>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login">

        <div class="input-group-custom">
            <div class="icon-wrapper"><i class="bi bi-envelope"></i></div>
            <input type="email" name="email" class="form-control" placeholder="Alamat Email" required>
        </div>

        <div class="input-group-custom" style="position: relative;">
            <div class="icon-wrapper"><i class="bi bi-lock"></i></div>
            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Kata Sandi" required style="padding-right: 40px;">
            <i class="bi bi-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 10;"></i>
        </div>

        <button type="submit" class="btn btn-login mb-4 w-100">
            Masuk
        </button>

    </form>
    <div style="text-align:center; margin-top:10px;">
        <div class="sso-divider">atau</div>
        <a href="auth/oauth_start.php" class="btn-google-sso">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
            <span>Masuk dengan Google</span>
        </a>
        <div class="sso-note">Mahasiswa, Dosen, dan Kaprodi wajib masuk via Google SSO. Admin tetap login via form.</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    document.addEventListener("DOMContentLoaded", function() {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(function() { alert.remove(); }, 500);
            }, 3000);
        });
    });
</script>
</body>
</html>
