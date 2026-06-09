<?php
// pages/dashboard.php 

require_once '../config/db.php';
require_once '../includes/header.php';

try {
    // DeepSeek'in önerdiği, veritabanını yormayan tek sorguluk devrim:
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM users) as user_count,
            (SELECT COUNT(*) FROM models) as model_count,
            (SELECT COUNT(*) FROM datasets) as dataset_count,
            (SELECT COUNT(*) FROM hardware) as hardware_count
    ");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gelen verileri HTML içindeki isimlerle birebir aynı olacak şekilde atıyoruz
    $userCount     = $counts['user_count'] ?? 0;
    $modelCount    = $counts['model_count'] ?? 0;
    $datasetCount  = $counts['dataset_count'] ?? 0;
    $hardwareCount = $counts['hardware_count'] ?? 0;

} catch (PDOException $e) {
    // GÜVENLİK: Saldırganların veritabanı yapımızı görmemesi için hatayı gizliyoruz!
    die("Sistemsel bir hata oluştu. Lütfen teknik ekiple iletişime geçiniz.");
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12">
        <h2 class="fw-bold text-secondary">
            <i class="bi bi-speedometer2 me-2"></i>Sistem Özeti (Dashboard)
        </h2>
        <p class="text-muted">Teknofest takımınızın güncel durumunu buradan takip edebilirsiniz.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    
    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3 text-primary"><i class="bi bi-people"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-muted">Takım Üyesi</h5>
                    <h2 class="fw-bold mb-0 text-dark"><?= $userCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3 text-success"><i class="bi bi-box"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-muted">YOLO Modeli</h5>
                    <h2 class="fw-bold mb-0 text-dark"><?= $modelCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3 text-warning"><i class="bi bi-database"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-muted">Veri Seti</h5>
                    <h2 class="fw-bold mb-0 text-dark"><?= $datasetCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3 text-danger"><i class="bi bi-pc-display"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-muted">Donanım</h5>
                    <h2 class="fw-bold mb-0 text-dark"><?= $hardwareCount ?></h2>
                </div>
            </div>
        </div>
    </div>
    
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> Hoş geldin <strong><?= htmlspecialchars(current_full_name(), ENT_QUOTES, 'UTF-8') ?></strong>! Yukarıdaki menüyü kullanarak işlemleri gerçekleştireceğin sayfalara geçiş yapabilirsin.
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
?>