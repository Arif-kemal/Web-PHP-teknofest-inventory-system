<?php


require_once '../config/db.php';
require_once '../includes/auth.php';

// Sadece giriş yapmış kullanıcılar silebilir
require_login();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // İlgili veri setini veritabanından sil
        $stmt = $db->prepare("DELETE FROM datasets WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // Eğer bu veri setine bağlı bir model varsa (Foreign Key) silmeyi reddedebilir
        die("Silme hatası (Muhtemelen bu veri seti bir modele bağlı): " . $e->getMessage());
    }
}

// Silme bitince veya id yoksa sayfaya geri dön
header('Location: ' . BASE_URL . '/pages/datasets.php');
exit;
?>