<?php
// pages/datasets.php

// 1. Veritabanı ve İskelet Bağlantıları
require_once '../config/db.php';
require_once '../includes/header.php';

// 2. Veri setlerini veritabanından çekme işlemi (JOIN ile ekleyen kişinin adını alıyoruz)
try {
    $stmt = $db->query("
        SELECT d.id, d.name, d.source, d.image_count, d.label_status, d.description, 
               u.full_name AS creator_name 
        FROM datasets d
        LEFT JOIN users u ON d.created_by = u.id
        ORDER BY d.id DESC
    ");
    $datasets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="text-secondary fw-bold">
        <i class="bi bi-database me-2"></i>Veri Setleri Envanteri
    </h2>
    <a href="add_dataset.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-lg me-1"></i>Yeni Veri Seti Ekle
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Veri Seti Adı</th>
                        <th>Kaynak</th>
                        <th>Görsel Sayısı</th>
                        <th>Etiketleme Durumu</th>
                        <th>Oluşturan</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($datasets) > 0): ?>
                        <?php foreach ($datasets as $row): ?>
                        <tr>
                            <td class="ps-4"><?= $row['id'] ?></td>
                            <td class="fw-bold text-dark">
                                <?= htmlspecialchars($row['name']) ?>
                                <?php if (!empty($row['description'])): ?>
                                    <div class="text-muted fw-normal" style="font-size: 0.85em;">
                                        <?= htmlspecialchars($row['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark border border-info">
                                    <i class="bi bi-link-45deg"></i> <?= htmlspecialchars($row['source']) ?>
                                </span>
                            </td>
                            <td><strong><?= number_format($row['image_count']) ?></strong> <small class="text-muted">adet</small></td>
                            <td>
                                <?php if($row['label_status'] == 'Tamamlandı'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Tamamlandı</span>
                                <?php elseif($row['label_status'] == 'Kısmen Etiketlendi'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Kısmen</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Etiketlenmedi</span>
                                <?php endif; ?>
                            </td>
                            <td><i class="bi bi-person me-1 text-muted"></i><?= htmlspecialchars($row['creator_name']) ?></td>
                            <td class="text-end pe-4">
                                <a href="edit_dataset.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <a href="delete_dataset.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu veri setini kalıcı olarak silmek istediğinize emin misiniz?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Henüz sisteme eklenmiş bir veri seti bulunmuyor.</td>
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