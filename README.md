# ⚡ TakımPanel — Teknofest Takım Envanter & AI Model Yönetim Sistemi

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/Lisans-MIT-green?style=for-the-badge)

**Teknofest yarışmacıları için donanım, yapay zeka modeli ve veri seti envanterini tek panelden yönetme platformu.**

[🎬 Demo Videosu](#-demo) · [📸 Ekran Görüntüleri](#-ekran-görüntüleri) · [🚀 Kurulum](#-kurulum) · [📂 Mimari](#-proje-mimarisi)

</div>

---

## 🎯 Projenin Doğuş Hikayesi — Neden Bu Proje?

> *"İyi bir araç, kötü bir koşucu yapmaz; ama iyi bir koşucu kötü araçla bile sonuca ulaşır. Biz hem iyi araçlar hem de iyi bir sistem istedik."*

Takımımız **Teknofest** kapsamında son derece iddialı bir proje geliştiriyor:

🚗 **TOGG** araçlarının görüntülerini işleyerek sürücü davranışlarını analiz etmek  
📡 **Node5 5G / QoD (Quality on Demand)** mimarisi üzerinden gerçek zamanlı veri akışı  
🤖 **YOLOv8** nesne tespiti modelleriyle anlık sınıflandırma  

Bu kadar yoğun bir teknik süreçte takımın envanteri inanılmaz bir hız ve karmaşayla büyüdü:

| Sorun | Detay |
|-------|-------|
| 🖥️ Donanım kaotik | RTX 3050 Laptop GPU'lar, Jetson Nano'lar, endüstriyel kameralar — **kimde ne var, hiçbir fikir yok** |
| 📦 Model sürümleri kayboldu | YOLOv8n, YOLOv8s, YOLOv8m... Hangi epoch'ta ne mAP skoru çıktı? **Unutuldu gitti** |
| 🗂️ Veri seti linkleri dağıldı | Roboflow projeleri, Kaggle datasetleri... **Herkesin farklı bir linki var** |

**TakımPanel**, tam bu kaosa çözüm olarak doğdu. Hafif, güvenli, şık ve kurulumu 5 dakika.

---

## ✨ Özellikler

### 🔐 Kimlik & Yetkilendirme
- Güvenli **kullanıcı kaydı** ve **oturum açma/kapama**
- `password_hash()` + `password_verify()` ile şifreli saklama (plain-text asla yok)
- PHP **Session** tabanlı oturum yönetimi (düz çerez yok)
- **Admin / Üye** rol sistemi — Kaptan silmek ister, üye sadece eklemek

### 📊 Dashboard (Ana Panel)
- Sistemdeki toplam model, veri seti ve donanım sayılarını anlık gösteren **Glassmorphism** özet kartlar
- MySQL `COUNT()` sorguları ile dinamik veri

### 🛠️ Donanım Yönetimi (CRUD)
- Donanım **ekleme, listeleme, güncelleme ve silme**
- **Zimmetleme sistemi:** Hangi donanım kimdeydi, hangi tarihte alındı — Türkiye saatiyle `DATETIME` kaydı
- Anlık durum takibi: `Boşta` 🟢 / `Kullanımda` 🔵 / `Arızalı` 🔴
- Silmek yerine durumu `Arızalı` güncelleme senaryosu

### 🤖 AI Model Takibi (CRUD)
- Model adı, mimari tipi (YOLOv8n/s/m/l/x), epoch sayısı
- **mAP@0.5 skoru** ve doğruluk oranı kaydı
- Modeli hangi veri setiyle eğittiğini Foreign Key ile bağlama

### 📂 Veri Seti Yönetimi (CRUD)
- Kaynak platform (Roboflow / Kaggle / Custom)
- Görsel sayısı ve etiketleme durumu (`Tamamlandı` / `Devam Ediyor`)
- Kaynak link ile doğrudan erişim

---

## 🎨 Tasarım Felsefesi

Proje arayüzü "sıradan bir ödev projesi" görünümünden kasıtlı olarak uzaklaştırıldı:

| Öğe | Detay |
|-----|-------|
| 🎨 Renk Paleti | Turkuaz & Koyu lacivert dominant, amber vurgu renkleri |
| 🪟 UI Kartlar | **Glassmorphism** — yarı şeffaf cam efekti, `backdrop-filter: blur()` |
| 🌸 Arka Plan | CSS `@keyframes` ile uçuşan **Sakura yaprakları** animasyonu |
| 📐 Bileşenler | Tüm öğeler Bootstrap 5.3 ile stillendirildi |
| 📱 Duyarlı Tasarım | Mobil, tablet ve masaüstü uyumlu responsive layout |

---

## 🗄️ Veritabanı Şeması

```
users ──┬──< models
        ├──< datasets
        └──< hardware (ekleyen)
             hardware (kullanan) >──── users
```

<details>
<summary>📋 Tablo detaylarını görmek için tıkla</summary>

### `users` — Kullanıcılar
| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | INT PK | Otomatik artan ID |
| ad_soyad | VARCHAR(100) | Tam adı |
| email | VARCHAR(150) UNIQUE | Giriş e-postası |
| sifre_hash | VARCHAR(255) | `password_hash()` ile hash'lenmiş şifre |
| rol | ENUM | `admin` veya `uye` |
| kayit_tarihi | DATETIME | Kayıt zamanı |

### `models` — AI Modelleri
| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | INT PK | — |
| model_adi | VARCHAR(150) | Modelin adı |
| mimari_tipi | ENUM | YOLOv8n / YOLOv8s / YOLOv8m / YOLOv8l / YOLOv8x / Diğer |
| epoch_sayisi | INT | Eğitim epoch sayısı |
| map_skoru | DECIMAL(5,2) | mAP@0.5 değeri |
| dogruluk_orani | DECIMAL(5,2) | Doğruluk % |
| dataset_id | INT FK | Kullanılan veri seti |
| egiten_id | INT FK | Modeli eğiten kullanıcı |

### `datasets` — Veri Setleri
| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | INT PK | — |
| isim | VARCHAR(150) | Veri setinin adı |
| kaynak | ENUM | Roboflow / Kaggle / Custom |
| gorsel_sayisi | INT | Toplam görsel adedi |
| etiketleme_durumu | ENUM | Tamamlandı / Devam Ediyor |
| kaynak_link | VARCHAR(500) | Platforma link |

### `hardware` — Donanım Envanteri
| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | INT PK | — |
| donanim_adi | VARCHAR(150) | Cihaz adı |
| kategori | ENUM | Kamera / Geliştirme Kartı / Sensör / Bilgisayar / Diğer |
| durum | ENUM | Boşta / Kullanımda / Arızalı |
| kullanan_id | INT FK NULL | Zimmetlenen kullanıcı (boşsa NULL) |
| teslim_tarihi | DATE | Zimmet tarihi |

</details>

---

## 📂 Proje Mimarisi

<details>
<summary>🗂️ Dosya yapısını görmek için tıkla</summary>

```
proje/
├── 📁 assets/
│   ├── css/style.css        ← Özel stiller, Glassmorphism, Sakura animasyonu
│   └── js/script.js         ← Silme onayı, flash mesaj, auto-submit
│
├── 📁 config/
│   └── db.php               ← PDO bağlantısı (hosting'e alırken güncelle)
│
├── 📁 includes/
│   ├── header.php           ← Bootstrap navbar, Bootstrap CDN linkleri
│   ├── footer.php           ← Kapanış etiketleri, Bootstrap JS
│   ├── auth.php             ← Session fonksiyonları (requireLogin, isAdmin...)
│   └── functions.php        ← XSS koruması (e()), flash mesaj, badge helper'ları
│
├── 📁 classes/              ← OOP gösterimi
│   ├── Database.php         ← Singleton PDO wrapper
│   └── User.php             ← Kayıt, giriş doğrulama, kullanıcı sorgulama
│
├── 📁 pages/
│   ├── dashboard.php        ← İstatistik paneli
│   ├── models.php           ← AI model CRUD
│   ├── datasets.php         ← Veri seti CRUD
│   └── hardware.php         ← Donanım CRUD + zimmetleme
│
├── index.php                ← Bekçi — girişe göre yönlendirme
├── login.php                ← Giriş formu
├── register.php             ← Kayıt formu
├── logout.php               ← Oturumu kapat
├── schema.sql               ← Veritabanı şeması (bunu içe aktar)
├── AI.md                    ← Yapay zeka yardım günlüğü
└── README.md                ← Bu dosya
```

</details>

---

## 🚀 Kurulum

### Gereksinimler
- PHP 8.0+
- MySQL 8.0+ / MariaDB 10.4+
- Apache / Nginx (veya XAMPP / WAMP lokal ortam)

### Adım Adım Kurulum

**1. Repoyu klonla**
```bash
git clone https://github.com/KULLANICI_ADIN/takimpanel.git
cd takimpanel
```

**2. Veritabanını oluştur**

phpMyAdmin veya MySQL CLI kullanarak `schema.sql` dosyasını içe aktar:

```bash
# CLI ile:
mysql -u root -p < schema.sql

# phpMyAdmin ile:
# Sol panel → Yeni Veritabanı → "teknofest_db" → Oluştur
# Üst menü → İçe Aktar → schema.sql dosyasını seç → Git
```

**3. Veritabanı bağlantısını yapılandır**

`config/db.php` dosyasını aç ve bilgileri güncelle:

```php
define('DB_HOST', 'localhost');   // Genellikle değişmez
define('DB_NAME', 'teknofest_db');
define('DB_USER', 'root');        // ← Kendi kullanıcı adın
define('DB_PASS', '');            // ← Kendi şifren
```

> ⚠️ **Hosting'e alırken:** Hosting kontrol panelinden aldığın veritabanı adı, kullanıcı adı ve şifreyi buraya gir.

**4. Projeyi çalıştır**

```
# XAMPP: htdocs/takimpanel/ klasörüne koy
# Tarayıcıda aç:
http://localhost/takimpanel/
```

**5. İlk giriş**

`schema.sql` bir test admin kullanıcısı oluşturur:

```
E-posta : admin@teknofest.local
Şifre   : Admin1234
```

> 🔒 Giriş yaptıktan sonra şifreyi değiştirmeyi unutma!

---

## 🎬 Demo

▶️ **[VİDEO LİNKİ BURAYA]** *(YouTube veya açık erişimli Google Drive)*

---

## 📸 Ekran Görüntüleri

### 🏠 Dashboard
![Dashboard](![EKRAN GÖRÜNTÜSÜ BURAYA])

### 🛠️ Donanım Yönetimi
![Donanım](![EKRAN GÖRÜNTÜSÜ BURAYA])

---

## 🔒 Güvenlik Notları

| Önlem | Uygulama |
|-------|----------|
| Şifre güvenliği | `password_hash()` / `password_verify()` (bcrypt, cost=12) |
| Oturum yönetimi | PHP `$_SESSION` — düz çerez yok |
| Session fixation | Giriş sonrası `session_regenerate_id(true)` |
| SQL Injection | PDO Prepared Statements — her sorguda parametreli |
| XSS Koruması | Tüm çıktılarda `htmlspecialchars()` (`e()` fonksiyonu) |

---

## 🌐 Diğer Takımlar İçin Değer Önerisi

Bu projeyi sadece bizim takımımız değil; **otonom araç, görüntü işleme veya IoT** üzerine çalışan tüm ekipler kullanabilir:

- 🏎️ Otonom araç takımları → model ve sensör envanteri
- 🤖 Robotik takımları → geliştirme kartı zimmet takibi  
- 🌾 Tarım teknolojisi takımları → drone ve kamera yönetimi
- 🎓 Üniversite yapay zeka kulüpleri → veri seti ve model kütüphanesi

Repoyu **fork'la**, `config/db.php` ve `schema.sql`'i düzenle, 5 dakikada kendi sistemin hazır.

---

## 🛠️ Kullanılan Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| Backend | PHP 8+ (Vanilla — framework yok) |
| Veritabanı | MySQL 8 / PDO |
| Frontend | HTML5, CSS3, Bootstrap 5.3 |
| İkonlar | Bootstrap Icons 1.11 |
| OOP | Singleton `Database`, `User` sınıfları |
| Güvenlik | bcrypt, Sessions, Prepared Statements |

---

## 📄 Lisans

MIT License — Dilediğin gibi kullan, fork'la, geliştir.

---

<div align="center">

---

*📚 Bu proje, **Web Tabanlı Programlama** dersi kapsamında dönem projesi olarak geliştirilmiştir.*  
*Proje gereksinimleri doğrultusunda; kullanıcı kaydı, oturum yönetimi, CRUD operasyonları,*  
*hazır CSS kütüphanesi kullanımı ve güvenlik standartları eksiksiz uygulanmıştır.*  
*Backend tarafında herhangi bir harici PHP kütüphanesi/framework kullanılmamış;*  
*tüm kodlar **Vanilla PHP 8+** ile özgün olarak yazılmıştır.*

---

</div>