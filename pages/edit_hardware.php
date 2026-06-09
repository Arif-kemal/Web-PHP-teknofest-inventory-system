<?php
// pages/edit_hardware.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';
$hardware = null;

// 1. URL'den ID'yi al ve donanım bilgilerini çek
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM hardware WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $hardware = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hardware) {
        die("<div class='container mt-5'><div class='alert alert-danger'>Donanım bulunamadı.</div></div>");
    }
} else {
    header('Location: hardware.php');
    exit;
}

// 2. Takım üyelerini açılır menü için veritabanından çek
$stmtUsers = $db->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$usersList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// 3. Form gönderildiğinde veritabanını güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Müsait';
    $notes = trim($_POST['notes'] ?? '');
    
    // Zimmetli kişi seçilmişse ID'sini al, seçilmemişse NULL yap
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

    // Eğer durum Müsait veya Bakımda ise, zimmeti ve tarihi otomatik sıfırla
    if ($status !== 'Zimmetli') {
        $assigned_to = null;
        $assigned_date = null;
    } else {
        // Zimmetli seçilmişse ve kişi atanmışsa şu anki tarihi ver
        $assigned_date = $assigned_to ? date('Y-m-d H:i:s') : null;
    }

    if ($name === '') {
        $error = 'Donanım Adı boş bırakılamaz.';
    } else {
        try {
            $stmtUpdate = $db->prepare('
                UPDATE hardware 
                SET name = :name, category = :category, status = :status, 
                    assigned_to = :assigned_to, assigned_date = :assigned_date, notes = :notes 
                WHERE id = :id
            ');
            
            $stmtUpdate->execute([
                ':name' => $name,
                ':category' => $category,
                ':status' => $status,
                ':assigned_to' => $assigned_to,
                ':assigned_date' => $assigned_date,
                ':notes' => $notes,
                ':id' => $id
            ]);
            $success = 'Donanım başarıyla güncellendi!';
            
            // Yeni verileri anında ekranda görebilmek için diziyi güncelliyoruz
            $hardware['name'] = $name;
            $hardware['category'] = $category;
            $hardware['status'] = $status;
            $hardware['assigned_to'] = $assigned_to;
            $hardware['notes'] = $notes;

        } catch (PDOException $e) {
            $error = 'GERÇEK HATA: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Donanım Düzenle
        </h2>
        <a href="hardware.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Geri Dön
        </a>
    </div>
</div>

<div class="card shadow border-0" style="max-width: 800px;">
    <div class="card-body p-4">
        
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="edit_hardware.php?id=<?= $id ?>" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Donanım Adı *</label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($hardware['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Kategori</label>
                    <select class="form-select" name="category">
                        <option value="Geliştirme Kartı" <?= $hardware['category'] == 'Geliştirme Kartı' ? 'selected' : '' ?>>Geliştirme Kartı</option>
                        <option value="Sensör" <?= $hardware['category'] == 'Sensör' ? 'selected' : '' ?>>Sensör</option>
                        <option value="Kamera" <?= $hardware['category'] == 'Kamera' ? 'selected' : '' ?>>Kamera</option>
                        <option value="Motor / Sürücü" <?= $hardware['category'] == 'Motor / Sürücü' ? 'selected' : '' ?>>Motor / Sürücü</option>
                        <option value="GPU Sunucu" <?= $hardware['category'] == 'GPU Sunucu' ? 'selected' : '' ?>>GPU Sunucu</option>
                        <option value="Diğer" <?= $hardware['category'] == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Durum</label>
                    <select class="form-select" name="status" id="statusSelect">
                        <option value="Müsait" <?= $hardware['status'] == 'Müsait' ? 'selected' : '' ?>>Müsait</option>
                        <option value="Zimmetli" <?= $hardware['status'] == 'Zimmetli' ? 'selected' : '' ?>>Zimmetli</option>
                        <option value="Bakımda" <?= $hardware['status'] == 'Bakımda' ? 'selected' : '' ?>>Bakımda</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Zimmetli Kişi <small class="text-info">(Sadece Zimmetliyse)</small></label>
                    <select class="form-select" name="assigned_to">
                        <option value="">-- Kişi Seçin --</option>
                        <?php foreach ($usersList as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($hardware['assigned_to'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted fw-bold">Notlar</label>
                <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($hardware['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Değişiklikleri Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>