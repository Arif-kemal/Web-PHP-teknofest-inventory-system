<?php
/**
 * register.php
 * Yeni kullanıcı kayıt sayfası.
 */

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Zaten giriş yapmış biri kayıt sayfasına girmeye çalışırsa dashboard'a gönder
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error   = '';
$success = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';

    // Boş alan kontrolü
    if ($full_name === '' || $username === '' || $email === '' || $password === '') {
        $error = 'Lütfen tüm alanları eksiksiz doldurun.';
    } elseif (strlen($password) < 6) {
        $error = 'Şifreniz en az 6 karakter olmalıdır.';
    } else {
        // Kullanıcı adı veya E-posta sistemde var mı kontrolü (SQL Injection korumalı)
        $stmtCheck = $db->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmtCheck->execute([
            ':username' => $username,
            ':email'    => $email
        ]);
        
        if ($stmtCheck->fetch()) {
            $error = 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.';
        } else {
            // Şifreyi geri döndürülemez şekilde hash'le
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Veritabanına yeni kullanıcıyı ekle (Varsayılan rol: member)
            $stmtInsert = $db->prepare('
                INSERT INTO users (username, email, password, full_name, role) 
                VALUES (:username, :email, :password, :full_name, "member")
            ');
            
            try {
                $stmtInsert->execute([
                    ':username'  => $username,
                    ':email'     => $email,
                    ':password'  => $hashedPassword,
                    ':full_name' => $full_name
                ]);
                $success = 'Kaydınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.';
            } catch (PDOException $e) {
                $error = 'Kayıt işlemi sırasında sistemsel bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol — Takım Envanter Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            width: 100%;
            max-width: 450px;
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(99, 179, 237, 0.18);
            border-radius: 1rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }
        .register-header {
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
        }
        .form-control, .input-group-text {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(99, 179, 237, 0.25);
            color: #e2e8f0;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #10b981;
            color: #f1f5f9;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }
        .form-label { color: #94a3b8; font-size: 0.875rem; font-weight: 500; }
        .btn-register {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none; color: #fff; font-weight: 600; padding: 0.7rem; border-radius: 0.5rem;
        }
        .btn-register:hover { opacity: 0.92; color: #fff; }
        .login-link { color: #34d399; text-decoration: none; }
    </style>
</head>
<body>

<div class="register-card mx-3">
    <div class="register-header text-center">
        <h4 class="text-white fw-bold mb-0"><i class="bi bi-person-plus-fill me-2"></i>Yeni Hesap Oluştur</h4>
    </div>
    
    <div class="p-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger d-flex align-items-center py-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success d-flex align-items-center py-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="d-grid mt-4">
                <a href="login.php" class="btn btn-outline-light">Giriş Sayfasına Dön</a>
            </div>
        <?php else: ?>

        <form action="register.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="full_name" class="form-label">Ad Soyad</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Ahmet Yıldız" value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="kullanici_adi" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-posta Adresi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ornek@btu.edu.tr" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="En az 6 karakter" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-register">
                    <i class="bi bi-check2-square me-1"></i> Kayıt Ol
                </button>
            </div>
        </form>

        <hr class="border-secondary my-3">
        <p class="text-center text-secondary small mb-0">
            Zaten bir hesabınız var mı? <a href="login.php" class="login-link">Giriş Yap</a>
        </p>
        
        <?php endif; ?>
    </div>
</div>

</body>
</html>