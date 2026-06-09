<?php
require_once __DIR__ . '/auth.php';
require_login(); // Sayfaya sadece giriş yapanlar girebilir
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takım Envanter Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* 1. FOOTER'I EN ALTTA SABİTLEME (FLEXBOX AYARI) */
        body {
            background-image: 
                linear-gradient(135deg, rgba(224, 247, 250, 0.85) 0%, rgba(255, 255, 255, 0.95) 100%),
                url('https://images.unsplash.com/photo-1522383225653-ed111181a951?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            
            /* Footer'ı aşağıya itmek için Flexbox motorunu açıyoruz */
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ekranın tamamını kaplamasını sağla */
            margin: 0;
            overflow-x: hidden; /* Sağa kaymayı engelle (yapraklar için) */
        }

        /* Ana içerik konteynerine (main) 'flex-grow: 1' vererek footer'ı dibe iteriz. 
        Eğer HTML'inde 'main' etiketi yoksa, <div class="container"> gibi en dıştaki div'e bu sınıfı ekle. */
        main {
            flex-grow: 1;
            padding-bottom: 2rem; /* Footer'la içerik arasına boşluk */
        }

        /* Mevcut tasarımın diğer kısımları */
        .bg-dark { background-color: #00838f !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { background-color: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.2); }
        .table-dark { background-color: #006064; }
        
        footer {
            background-color: rgba(0, 131, 143, 0.9) !important; /* Turkuaz tonu */
            backdrop-filter: blur(3px);
            color: white;
            padding: 1rem 0;
            margin-top: auto; /* Sayfa kısa olsa bile dibe yapışmasını sağlayan sihir */
        }

        /* 2. UÇUŞAN SAKURA YAPRAKLARI ANİMASYONU */
        .sakura-yapraklari {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1; /* İçeriğin arkasında kalsınlar */
            pointer-events: none; /* Tıklamayı engelle */
        }

        .sakura-yapraklari span {
            position: absolute; display: block;
            width: 15px; height: 15px; /* Yaprak boyutu */
            background: #ffc0cb; /* Pembe renk */
            border-radius: 100% 0% 100% 0%; /* Yaprak şekli */
            opacity: 0;
            animation: dökülen_yapraklar 15s linear infinite; /* Animasyonu bağla */
        }

        /* 10 yaprağın her birine farklı başlama süresi, konum ve hız verelim (doğallık için) */
        .sakura-yapraklari span:nth-child(1)  { left: 10%; animation-delay: 0s;  animation-duration: 12s; }
        .sakura-yapraklari span:nth-child(2)  { left: 30%; animation-delay: 2s;  animation-duration: 18s; }
        .sakura-yapraklari span:nth-child(3)  { left: 50%; animation-delay: 4s;  animation-duration: 14s; }
        .sakura-yapraklari span:nth-child(4)  { left: 70%; animation-delay: 6s;  animation-duration: 16s; }
        .sakura-yapraklari span:nth-child(5)  { left: 90%; animation-delay: 8s;  animation-duration: 20s; }
        .sakura-yapraklari span:nth-child(6)  { left: 20%; animation-delay: 1s;  animation-duration: 13s; }
        .sakura-yapraklari span:nth-child(7)  { left: 40%; animation-delay: 3s;  animation-duration: 17s; }
        .sakura-yapraklari span:nth-child(8)  { left: 60%; animation-delay: 5s;  animation-duration: 15s; }
        .sakura-yapraklari span:nth-child(9)  { left: 80%; animation-delay: 7s;  animation-duration: 19s; }
        .sakura-yapraklari span:nth-child(10) { left: 95%; animation-delay: 9s;  animation-duration: 11s; }

        /* Animasyon Matematiği: Yukarıdan aşağıya dökülme ve dönme */
        @keyframes dökülen_yapraklar {
            0% {
                opacity: 0;
                top: -10%; /* Ekranın yukarısından başla */
                transform: translateX(0) rotate(0deg);
            }
            10% { opacity: 1; } /* Belirginleş */
            90% { opacity: 1; }
            100% {
                opacity: 0;
                top: 100%; /* Ekranın altına kadar dökül */
                /* Düşerken rüzgarla savruluyormuş gibi sağa/sola kaydır ve döndür */
                transform: translateX(100px) rotate(720deg); 
            }
        }
    </style>
</head>
<body>
    <div class="sakura-yapraklari">
        <span></span><span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span><span></span>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/pages/dashboard.php">
                <i class="bi bi-cpu-fill text-primary me-2"></i>Envanter
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pages/dashboard.php"><i class="bi bi-house-door me-1"></i>Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pages/models.php"><i class="bi bi-box me-1"></i>Modeller</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pages/datasets.php"><i class="bi bi-database me-1"></i>Veri Setleri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/pages/hardware.php"><i class="bi bi-pc-display me-1"></i>Donanımlar</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars(current_full_name(), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Çıkış Yap</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container">