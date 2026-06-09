<?php


require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';
$dataset = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM datasets WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $dataset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dataset) {
        die("Böyle bir veri seti bulunamadı.");
    }
} else {
    header('Location: datasets.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $image_count = $_POST['image_count'] !== '' ? (int)$_POST['image_count'] : 0;
    $label_status = $_POST['label_status'] ?? 'Etiketlenmedi';
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Veri seti adı boş bırakılamaz.';
    } else {
        $stmtUpdate = $db->prepare('
            UPDATE datasets 
            SET name = :name, source = :source, image_count = :image_count, 
                label_status = :label_status, description = :description 
            WHERE id = :id
        ');
        
        try {
            $stmtUpdate->execute([
                ':name' => $name,
                ':source' => $source,
                ':image_count' => $image_count,
                ':label_status' => $label_status,
                ':description' => $description,
                ':id' => $id
            ]);
            $success = 'Veri seti başarıyla güncellendi!';
            
            // Yeni verileri ekrana basmak için diziyi güncelliyoruz
            $dataset['name'] = $name;
            $dataset['source'] = $source;
            $dataset['image_count'] = $image_count;
            $dataset['label_status'] = $label_status;
            $dataset['description'] = $description;
        } catch (PDOException $e) {
            $error = 'Güncelleme sırasında hata: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Veri Setini Düzenle
        </h2>
        <a href="datasets.php" class="btn btn-outline-secondary shadow-sm">
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

        <form action="edit_dataset.php?id=<?= $id ?>" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Veri Seti Adı *</label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($dataset['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Kaynak Linki</label>
                    <input type="text" class="form-control" name="source" value="<?= htmlspecialchars($dataset['source']) ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Görsel Sayısı</label>
                    <input type="number" class="form-control" name="image_count" value="<?= htmlspecialchars($dataset['image_count']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted fw-bold">Etiketleme Durumu</label>
                    <select class="form-select" name="label_status">
                        <option value="Etiketlenmedi" <?= $dataset['label_status'] == 'Etiketlenmedi' ? 'selected' : '' ?>>Etiketlenmedi</option>
                        <option value="Kısmen Etiketlendi" <?= $dataset['label_status'] == 'Kısmen Etiketlendi' ? 'selected' : '' ?>>Kısmen Etiketlendi</option>
                        <option value="Tamamlandı" <?= $dataset['label_status'] == 'Tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted fw-bold">Açıklama</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($dataset['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Değişiklikleri Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>