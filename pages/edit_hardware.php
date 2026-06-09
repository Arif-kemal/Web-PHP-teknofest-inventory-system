<?php
// pages/edit_hardware.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';
$hardware = null;

// 1. Adım: URL'den gelen ID'ye göre mevcut donanım bilgilerini getir (Formu doldurmak için)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM hardware WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $hardware = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hardware) {
        die("Böyle bir donanım bulunamadı.");
    }
} else {
    header('Location: hardware.php');
    exit;
}

// 2. Adım: Form gönderildiyse veritabanındaki bilgileri GÜNCELLE (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Diğer';
    $status = $_POST['status'] ?? 'Müsait';
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        $error = 'Donanım adı boş bırakılamaz.';
    } else {
        $stmtUpdate = $db->prepare('
            UPDATE hardware 
            SET name = :name, type = :type, status = :status, notes = :notes 
            WHERE id = :id
        ');
        
        try {
            $stmtUpdate->execute([
                ':name' => $name,
                ':type' => $type,
                ':status' => $status,
                ':notes' => $notes,
                ':id' => $id
            ]);
            $success = 'Donanım bilgileri başarıyla güncellendi!';
            // Formu güncel verilerle tekrar doldurmak için veriyi yeniden çekiyoruz
            $hardware['name'] = $name;
            $hardware['type'] = $type;
            $hardware['status'] = $status;
            $hardware['notes'] = $notes;
        } catch (PDOException $e) {
            $error = 'Güncelleme sırasında hata oluştu: ' . $e->getMessage();
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
                    <label for="name" class="form-label text-muted fw-bold">Donanım Adı</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($hardware['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="type" class="form-label text-muted fw-bold">Kategori</label>
                    <select class="form-select" id="type" name="type">
                        <option value="Geliştirme Kartı" <?= $hardware['type'] == 'Geliştirme Kartı' ? 'selected' : '' ?>>Geliştirme Kartı</option>
                        <option value="Kamera" <?= $hardware['type'] == 'Kamera' ? 'selected' : '' ?>>Kamera</option>
                        <option value="Sensör" <?= $hardware['type'] == 'Sensör' ? 'selected' : '' ?>>Sensör</option>
                        <option value="Diğer" <?= $hardware['type'] == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label text-muted fw-bold">Durum</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Müsait" <?= $hardware['status'] == 'Müsait' ? 'selected' : '' ?>>Müsait</option>
                        <option value="Zimmetli" <?= $hardware['status'] == 'Zimmetli' ? 'selected' : '' ?>>Zimmetli</option>
                        <option value="Bakımda" <?= $hardware['status'] == 'Bakımda' ? 'selected' : '' ?>>Bakımda</option>
                        <option value="Arızalı" <?= $hardware['status'] == 'Arızalı' ? 'selected' : '' ?>>Arızalı</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label text-muted fw-bold">Notlar</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($hardware['notes']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Değişiklikleri Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>