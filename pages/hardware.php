<?php


// 1. Veritabanı ve İskelet Bağlantıları
require_once '../config/db.php';
require_once '../includes/header.php';

// 2. Donanımları veritabanından çekme işlemi (JOIN ile zimmetli kişinin adını alıyoruz)
try {
    // SİHİRLİ DOKUNUŞ 1: SQL sorgusuna 'h.assigned_date' sütunu da eklendi
    $stmt = $db->query("
        SELECT h.id, h.name, h.type, h.status, h.serial_number, h.assigned_at, h.assigned_date, h.notes, 
               u.full_name AS assigned_user 
        FROM hardware h
        LEFT JOIN users u ON h.assigned_to = u.id
        ORDER BY h.id DESC
    ");
    $hardware_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-3">
    <h2 class="text-secondary fw-bold">
        <i class="bi bi-pc-display me-2"></i>Donanım Envanteri
    </h2>
    <a href="add_hardware.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-lg me-1"></i>Yeni Donanım Ekle
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Donanım Adı</th>
                        <th>Kategori</th>
                        <th>Durum</th>
                        <th>Zimmetli Kişi</th>
                        <th>Zimmet Tarihi</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($hardware_list) > 0): ?>
                        <?php foreach ($hardware_list as $row): ?>
                        <tr>
                            <td class="ps-4"><?= $row['id'] ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                <?php if (!empty($row['serial_number'])): ?>
                                    <small class="text-muted">SN: <?= htmlspecialchars($row['serial_number']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-light">
                                    <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($row['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Müsait'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Müsait</span>
                                <?php elseif($row['status'] == 'Zimmetli'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-person-badge me-1"></i>Zimmetli</span>
                                <?php elseif($row['status'] == 'Bakımda'): ?>
                                    <span class="badge bg-info text-dark"><i class="bi bi-tools me-1"></i>Bakımda</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Arızalı</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['assigned_user'])): ?>
                                    <i class="bi bi-person-fill text-primary me-1"></i><strong><?= htmlspecialchars($row['assigned_user']) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php 
                                    if ($row['status'] === 'Zimmetli') {
                                        // Önce yeni tarihe bak, boşsa eski tarihe bak
                                        $zaman = !empty($row['assigned_date']) ? $row['assigned_date'] : $row['assigned_at'];
                                        
                                        if (!empty($zaman)) {
                                            echo '<small class="text-muted">' . date('d.m.Y H:i', strtotime($zaman)) . '</small>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                ?>
                            </td>
                            
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-success" title="Zimmet Ata/Kaldır">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                
                                <a href="edit_hardware.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <a href="delete_hardware.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu donanımı envanterden kalıcı olarak silmek istediğinize emin misiniz?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Sisteme kayıtlı donanım bulunamadı.</td>
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