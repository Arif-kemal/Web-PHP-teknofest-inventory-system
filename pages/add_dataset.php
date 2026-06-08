<?php
// pages/add_dataset.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $image_count = $_POST['image_count'] !== '' ? (int)$_POST['image_count'] : 0;
    $label_status = $_POST['label_status'] ?? 'Etiketlenmedi';
    $description = trim($_POST['description'] ?? '');
    
    // Oturumu açık olan kullanıcının ID'sini otomatik alıyoruz (Kim ekledi?)
    $created_by = current_user_id();

    // Veri seti adı zorunlu alan
    if ($name === '') {
        $error = 'Lütfen veri seti adını doldurunuz.';
    } else {
        $stmt = $db->prepare('
            INSERT INTO datasets (name, source, image_count, label_status, description, created_by) 
            VALUES (:name, :source, :image_count, :label_status, :description, :created_by)
        ');
        
        try {
            $stmt->execute([
                ':name' => $name,
                ':source' => $source,
                ':image_count' => $image_count,
                ':label_status' => $label_status,
                ':description' => $description,
                ':created_by' => $created_by
            ]);
            $success = 'Yeni veri seti başarıyla envantere eklendi!';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-folder-plus me-2"></i>Yeni Veri Seti Ekle
        </h2>
        <a href="datasets.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Veri Setlerine Dön
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

        <form action="add_dataset.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-muted fw-bold">Veri Seti Adı *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Örn: Otonom Araç Tabelalar v1" required>
                </div>
                <div class="col-md-6">
                    <label for="source" class="form-label text-muted fw-bold">Kaynak Linki / Konumu</label>
                    <input type="text" class="form-control" id="source" name="source" placeholder="Roboflow, Kaggle linki veya Drive klasörü">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="image_count" class="form-label text-muted fw-bold">Görsel Sayısı</label>
                    <input type="number" class="form-control" id="image_count" name="image_count" placeholder="Örn: 2500">
                </div>
                <div class="col-md-6">
                    <label for="label_status" class="form-label text-muted fw-bold">Etiketleme Durumu</label>
                    <select class="form-select" id="label_status" name="label_status">
                        <option value="Etiketlenmedi" selected>Etiketlenmedi</option>
                        <option value="Kısmen Etiketlendi">Kısmen Etiketlendi</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label text-muted fw-bold">Açıklama / İçerik Detayları</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Hangi sınıflar var? Gece çekimi var mı? Ekstra notlar..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Veri Setini Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>