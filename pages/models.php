<?php


// 1. Veritabanı ve İskelet Bağlantıları
require_once '../config/db.php';
require_once '../includes/header.php';

// 2. Modelleri veritabanından çekme işlemi (JOIN ile eğiten kişinin adını ve veri seti adını da alıyoruz)
try {
    $stmt = $db->query("
        SELECT m.id, m.name, m.architecture, m.map_score, m.status, 
               u.full_name AS trainer_name, 
               d.name AS dataset_name
        FROM models m
        LEFT JOIN users u ON m.trained_by = u.id
        LEFT JOIN datasets d ON m.dataset_id = d.id
        ORDER BY m.id DESC
    ");
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="text-secondary fw-bold">
        <i class="bi bi-box me-2"></i>Eğitilen Modeller
    </h2>
    <a href="add_model.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-lg me-1"></i>Yeni Model Ekle
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Model Adı</th>
                        <th>Mimari</th>
                        <th>Veri Seti</th>
                        <th>mAP Skoru</th>
                        <th>Durum</th>
                        <th>Eğiten</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($models) > 0): ?>
                        <?php foreach ($models as $row): ?>
                        <tr>
                            <td class="ps-4"><?= $row['id'] ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['architecture']) ?></span></td>
                            <td><small><?= htmlspecialchars($row['dataset_name']) ?></small></td>
                            <td>
                                <?php if ($row['map_score']): ?>
                                    <strong>%<?= $row['map_score'] ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Tamamlandı'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Tamamlandı</span>
                                <?php elseif($row['status'] == 'Eğitiliyor'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Eğitiliyor</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?= $row['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['trainer_name']) ?></td>
                            <td class="text-end pe-4">
                                <a href="view_model.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-info" title="İncele">
                                    <i class="bi bi-eye"></i> İncele
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Henüz sisteme eklenmiş bir model bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// 3. Footer'ı çağır
require_once '../includes/footer.php'; 
?>