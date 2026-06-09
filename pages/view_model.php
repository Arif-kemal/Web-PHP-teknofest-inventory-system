<?php


require_once '../config/db.php';
require_once '../includes/header.php';

// URL'den geçerli bir ID gelmiş mi kontrol et
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Modeli, eğiten kişiyi ve kullanılan veri setini JOIN ile çekiyoruz
    $stmt = $db->prepare("
        SELECT m.*, u.full_name AS trainer_name, d.name AS dataset_name 
        FROM models m
        LEFT JOIN users u ON m.trained_by = u.id
        LEFT JOIN datasets d ON m.dataset_id = d.id
        WHERE m.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $model = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$model) {
        die("<div class='container mt-5'><div class='alert alert-danger'>Model bulunamadı!</div></div>");
    }
} else {
    header('Location: models.php');
    exit;
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-cpu me-2"></i>Model Detayları
        </h2>
        <a href="models.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Modellere Dön
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-box me-2"></i><?= htmlspecialchars($model['name']) ?>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 30%;" class="text-muted">Yapay Zeka Mimarisi:</th>
                            <td><span class="badge bg-primary fs-6"><?= htmlspecialchars($model['architecture']) ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Kullanılan Veri Seti:</th>
                            <td><i class="bi bi-database me-1 text-secondary"></i> <?= htmlspecialchars($model['dataset_name']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Durum:</th>
                            <td>
                                <?php if($model['status'] == 'Tamamlandı'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Tamamlandı</span>
                                <?php elseif($model['status'] == 'Eğitiliyor'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Eğitiliyor</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?= htmlspecialchars($model['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Modeli Eğiten:</th>
                            <td><i class="bi bi-person-badge me-1 text-secondary"></i> <?= htmlspecialchars($model['trainer_name']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Oluşturulma Tarihi:</th>
                            <td><?= date('d.m.Y H:i', strtotime($model['created_at'])) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light fw-bold text-secondary">
                <i class="bi bi-journal-text me-2"></i>Eğitim Notları & Açıklamalar
            </div>
            <div class="card-body">
                <?php if (!empty($model['notes'])): ?>
                    <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($model['notes'])) ?></p>
                <?php else: ?>
                    <p class="mb-0 text-muted fst-italic">Bu model için henüz bir not girilmemiş.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body text-center py-4">
                <h5 class="text-muted fw-bold mb-3">mAP Skoru</h5>
                <?php if ($model['map_score']): ?>
                    <h1 class="display-4 fw-bold text-success">%<?= $model['map_score'] ?></h1>
                <?php else: ?>
                    <h1 class="display-5 text-muted">-</h1>
                    <small>Henüz test edilmedi</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body text-center py-4">
                <h5 class="text-muted fw-bold mb-3">Epoch Sayısı</h5>
                <?php if ($model['epoch_count']): ?>
                    <h1 class="display-4 fw-bold text-primary"><?= $model['epoch_count'] ?></h1>
                    <small class="text-muted">Döngü tamamlandı</small>
                <?php else: ?>
                    <h1 class="display-5 text-muted">-</h1>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>