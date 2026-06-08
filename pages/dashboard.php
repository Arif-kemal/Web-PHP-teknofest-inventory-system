<?php
// pages/dashboard.php

// 1. Veritabanı köprümüzü çağırıyoruz
require_once '../config/db.php';

// 2. İskeletimizin üst kısmını (header) çağırıyoruz. (auth.php ve güvenlik kontrolleri burada yapılıyor)
require_once '../includes/header.php'; 

// İstatistikleri veritabanından çekelim (PDO)
try {
    // Kullanıcı Sayısı
    $stmtUser = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmtUser->fetchColumn();

    // Model Sayısı
    $stmtModel = $db->query("SELECT COUNT(*) FROM models");
    $modelCount = $stmtModel->fetchColumn();

    // Veri Seti Sayısı
    $stmtDataset = $db->query("SELECT COUNT(*) FROM datasets");
    $datasetCount = $stmtDataset->fetchColumn();

    // Donanım Sayısı
    $stmtHardware = $db->query("SELECT COUNT(*) FROM hardware");
    $hardwareCount = $stmtHardware->fetchColumn();

} catch (PDOException $e) {
    die("Sorgu Hatası: " . $e->getMessage());
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
        <div class="card text-white bg-primary h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-people"></i></div>
                <div>
                    <h5 class="card-title mb-0">Takım Üyesi</h5>
                    <h2 class="fw-bold mb-0"><?= $userCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-box"></i></div>
                <div>
                    <h5 class="card-title mb-0">YOLO Modeli</h5>
                    <h2 class="fw-bold mb-0"><?= $modelCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-warning h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-database"></i></div>
                <div>
                    <h5 class="card-title mb-0 text-dark">Veri Seti</h5>
                    <h2 class="fw-bold mb-0 text-dark"><?= $datasetCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-danger h-100 shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-pc-display"></i></div>
                <div>
                    <h5 class="card-title mb-0">Donanım</h5>
                    <h2 class="fw-bold mb-0"><?= $hardwareCount ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> Hoş geldin <strong><?= htmlspecialchars(current_full_name(), ENT_QUOTES, 'UTF-8') ?></strong>! Yukarıdaki menüyü kullanarak işlemleri gerçekleştireceğin sayfalara geçiş yapabilirsin. <em>(Diğer sayfaları henüz kodlamadığımız için menüdeki linklere tıklarsan "Bulunamadı" hatası alman normaldir, bir sonraki aşamada onlara geçeceğiz).</em>
        </div>
    </div>
</div>

<?php 
// 3. İskeletimizin alt kısmını (footer) çağırıp sayfayı kapatıyoruz
require_once '../includes/footer.php'; 
?>