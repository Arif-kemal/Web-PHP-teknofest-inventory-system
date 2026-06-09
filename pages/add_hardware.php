<?php
// pages/add_hardware.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// 1. Takım üyelerini "Zimmetli Kişi" açılır menüsü için veritabanından çek
$stmtUsers = $db->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$usersList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Form gönderildiyse (POST isteği)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? 'Diğer'; // Veritabanındaki "category" sütunu ile uyumlu yapıldı
    $status = $_POST['status'] ?? 'Müsait';
    $serial_number = trim($_POST['serial_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // 2. Zimmet Kişisi ve Tarihi Mantığı
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    
    // Eğer durum Zimmetli değilse, zimmet bilgisini zorla sıfırla
    if ($status !== 'Zimmetli') {
        $assigned_to = null;
        $assigned_date = null;
    } else {
        // Zimmetliyse ve kişi seçildiyse bugünün tarihini (Türkiye saatiyle) at
        $assigned_date = $assigned_to ? date('Y-m-d H:i:s') : null;
    }

    // Sadece donanım adı zorunlu olsun
    if ($name === '') {
        $error = 'Lütfen donanım adını giriniz.';
    } else {
        // SQL Sorgusuna category, assigned_to ve assigned_date eklendi
        $stmt = $db->prepare('
            INSERT INTO hardware (name, category, status, serial_number, assigned_to, assigned_date, notes) 
            VALUES (:name, :category, :status, :serial_number, :assigned_to, :assigned_date, :notes)
        ');
        
        try {
            $stmt->execute([
                ':name' => $name,
                ':category' => $category,
                ':status' => $status,
                ':serial_number' => $serial_number,
                ':assigned_to' => $assigned_to,
                ':assigned_date' => $assigned_date,
                ':notes' => $notes
            ]);
            $success = 'Donanım envantere başarıyla eklendi!';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Yeni Donanım Ekle
        </h2>
        <a href="hardware.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Envantere Dön
        </a>
    </div>
</div>

<div class="card shadow-sm border-0" style="max-width: 800px;">
    <div class="card-body p-4">
        
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="add_hardware.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-muted fw-bold">Donanım Adı *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Örn: Raspberry Pi 4" required>
                </div>
                <div class="col-md-6">
                    <label for="serial_number" class="form-label text-muted fw-bold">Seri Numarası</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Varsa SN giriniz">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label text-muted fw-bold">Kategori</label>
                    <select class="form-select" id="category" name="category">
                        <option value="Geliştirme Kartı">Geliştirme Kartı</option>
                        <option value="Kamera">Kamera</option>
                        <option value="Sensör">Sensör</option>
                        <option value="GPU Sunucu">GPU Sunucu</option>
                        <option value="Diğer" selected>Diğer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label text-muted fw-bold">Başlangıç Durumu</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Müsait" selected>Müsait</option>
                        <option value="Zimmetli">Zimmetli</option>
                        <option value="Bakımda">Bakımda</option>
                        <option value="Arızalı">Arızalı</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted fw-bold">Zimmetli Kişi <small class="text-info">(Zimmetliyse)</small></label>
                    <select class="form-select" name="assigned_to">
                        <option value="">-- Kişi Seçin --</option>
                        <?php foreach ($usersList as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label text-muted fw-bold">Notlar / Açıklama</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Donanımla ilgili eklemek istedikleriniz..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>