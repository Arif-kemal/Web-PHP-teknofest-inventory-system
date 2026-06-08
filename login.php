<?php
/**
 * login.php
 */

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error   = ''; 
$success = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre alanları boş bırakılamaz.';
    } else {
        // PDO kullanılıyor. config/db.php dosyamızdaki değişkenin adı $db idi, o yüzden $db olarak güncelledik.
        $stmt = $db->prepare(
            'SELECT id, username, email, password, full_name, role
               FROM users
              WHERE username = :username
              LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            regenerate_session();
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            header('Location: ' . BASE_URL . '/pages/dashboard.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap — Takım Envanter Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(99, 179, 237, 0.18);
            border-radius: 1rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 1rem 1rem 0 0;
            padding: 2rem 1.5rem 1.5rem;
        }
        .login-header .brand-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.15);
            border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: #fff; margin: 0 auto 1rem;
        }
        .form-control, .input-group-text {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(99, 179, 237, 0.25);
            color: #e2e8f0;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #3b82f6; color: #f1f5f9;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .form-label { color: #94a3b8; font-size: 0.875rem; font-weight: 500; }
        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none; color: #fff; font-weight: 600; padding: 0.7rem; border-radius: 0.5rem;
        }
        .btn-login:hover { opacity: 0.92; color: #fff; }
        .register-link { color: #60a5fa; text-decoration: none; }
        .btn-toggle-pw {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(99, 179, 237, 0.25); border-left: none; color: #64748b;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header text-center">
        <div class="brand-icon mx-auto"><i class="bi bi-cpu-fill"></i></div>
        <h4 class="text-white fw-bold mb-1">Takım Envanter Sistemi</h4>
        <p class="text-white-50 small mb-0">Model &amp; Veri Seti Yönetim Platformu</p>
    </div>
    <div class="p-4">
        <h5 class="text-light fw-semibold mb-4">Oturum Aç</h5>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger d-flex align-items-center py-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="kullanici_adi" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn btn-toggle-pw" id="togglePassword">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Giriş Yap
                </button>
            </div>
        </form>
        <hr class="border-secondary my-3">
        <p class="text-center text-secondary small mb-0">
            Hesabınız yok mu? <a href="register.php" class="register-link">Kayıt Ol</a>
        </p>
    </div>
</div>
<script>
    const toggleBtn  = document.getElementById('togglePassword');
    const pwInput    = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    toggleBtn.addEventListener('click', () => {
        const isPassword = pwInput.type === 'password';
        pwInput.type     = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>