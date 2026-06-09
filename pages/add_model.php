<?php


require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// Formdaki açılır menü için mevcut veri setlerini veritabanından çekiyoruz
try {
    $stmtDatasets = $db->query("SELECT id, name FROM datasets ORDER BY id DESC");
    $datasets = $stmtDatasets->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri setleri çekilirken hata oluştu: " . $e->getMessage());
}

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $architecture = $_POST['architecture'] ?? 'YOLOv8n';
    $map_score = $_POST['map_score'] !== '' ? (float)$_POST['map_score'] : null;
    $epoch_count = $_POST['epoch_count'] !== '' ? (int)$_POST['epoch_count'] : null;
    $status = $_POST['status'] ?? 'Eğitiliyor';
    $dataset_id = $_POST['dataset_id'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    // Oturumu açık olan kullanıcının ID'sini auth.php'deki fonksiyonumuzla otomatik alıyoruz
    $trained_by = current_user_id(); 

    if ($name === '' || $dataset_id === '') {
        $error = 'Lütfen model adı ve veri seti alanlarını doldurunuz.';
    } else {
        $stmt = $db->prepare('
            INSERT INTO models (name, architecture, map_score, epoch_count, status, notes, trained_by, dataset_id) 
            VALUES (:name, :architecture, :map_score, :epoch_count, :status, :notes, :trained_by, :dataset_id)
        ');
        
        try {
            $stmt->execute([
                ':name' => $name,
                ':architecture' => $architecture,
                ':map_score' => $map_score,
                ':epoch_count' => $epoch_count,
                ':status' => $status,
                ':notes' => $notes,
                ':trained_by' => $trained_by,
                ':dataset_id' => $dataset_id
            ]);
            $success = 'Yeni model başarıyla envantere eklendi!';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-plus-square-dotted me-2"></i>Yeni Model Kaydı
        </h2>
        <a href="models.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Modellere Dön
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

        <form action="add_model.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-muted fw-bold">Model Adı *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Örn: Araç Tespit v2" required>
                </div>
                <div class="col-md-6">
                    <label for="architecture" class="form-label text-muted fw-bold">Mimari</label>
                    <select class="form-select" id="architecture" name="architecture">
                        <option value="YOLOv8n">YOLOv8n (Nano)</option>
                        <option value="YOLOv8s">YOLOv8s (Small)</option>
                        <option value="YOLOv8m" selected>YOLOv8m (Medium)</option>
                        <option value="YOLOv8l">YOLOv8l (Large)</option>
                        <option value="YOLOv8x">YOLOv8x (Extra Large)</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="dataset_id" class="form-label text-muted fw-bold">Kullanılan Veri Seti *</label>
                    <select class="form-select" id="dataset_id" name="dataset_id" required>
                        <option value="">-- Veri Seti Seçiniz --</option>
                        <?php foreach ($datasets as $ds): ?>
                            <option value="<?= $ds['id'] ?>"><?= htmlspecialchars($ds['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="map_score" class="form-label text-muted fw-bold">mAP Skoru (%)</label>
                    <input type="number" step="0.01" class="form-control" id="map_score" name="map_score" placeholder="Örn: 92.45">
                </div>
                <div class="col-md-4">
                    <label for="epoch_count" class="form-label text-muted fw-bold">Epoch Sayısı</label>
                    <input type="number" class="form-control" id="epoch_count" name="epoch_count" placeholder="Örn: 150">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label text-muted fw-bold">Durum</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Eğitiliyor" selected>Eğitiliyor</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                        <option value="Başarısız">Başarısız</option>
                        <option value="Arşivlendi">Arşivlendi</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label text-muted fw-bold">Eğitim Notları</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Geliştirmeler, sorunlar veya hyperparameter notları..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Modeli Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>