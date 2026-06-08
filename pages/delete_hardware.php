<?php
// pages/delete_hardware.php

// 1. Veritabanı ve güvenlik bağlantıları
require_once '../config/db.php';
require_once '../includes/auth.php';

// Güvenlik: Sadece sisteme giriş yapmış kişiler silme işlemi yapabilir
require_login(); 

// URL'den gelen 'id' parametresi var mı ve geçerli bir sayı mı diye kontrol ediyoruz
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // SQL Injection'a karşı PDO prepare ile güvenli silme işlemi
        $stmt = $db->prepare("DELETE FROM hardware WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
    } catch (PDOException $e) {
        // Eğer silinmek istenen donanım başka bir tabloyla kritik bir bağa sahipse (Foreign Key kısıtlaması)
        // sistem hata verebilir. İleride buraya hata mesajı da eklenebilir.
        die("Silme işlemi sırasında bir hata oluştu: " . $e->getMessage());
    }
}

// İşlem biter bitmez (veya id yoksa) kullanıcıyı anında donanımlar sayfasına geri fırlatıyoruz
header('Location: ' . BASE_URL . '/pages/hardware.php');
exit;
?>