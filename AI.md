hocam ben bir php projesi yapacaüım ve bunun için  bana bazı bilgiler vermen gerekli daha sonra kodlamaya geçiceğiz yapacağımız proje teknofest proejeleri için yolvo modeli,takımdaki kişiler, eğitim set verileri, donanımlarla ilgili  bir proje yapmak istiyorum bunun için öncelikle SQL de localhostta bunun veri tabanını oluşturmamız lazım

Anlaşıldı hocam, Teknofest projeniz için veritabanı tasarımına başlayalım.  
“Yolvo” ifadesinden YOLO (You Only Look Once) nesne tespit modelini kastettiğinizi varsayıyorum. Proje kapsamında takım üyeleri, eğitim setleri, donanımlar ve kullanılan modellerin yönetileceği bir PHP projesi olacak.

Aşağıda **localhost**’ta çalışacak **MySQL** veritabanını oluşturmak için gerekli SQL kodlarını hazırladım. Tablolar ve ilişkiler:

- **takimlar** – Takım bilgileri  
- **kullanicilar** – Takım üyeleri (her üye bir takıma bağlı)  
- **modeller** – Kullanılan YOLO modelleri (v3, v4, v5, v8 vb.)  
- **egitim_setleri** – Eğitim veri setleri (kullanılan datasetler, etiket sayısı vb.)  
- **donanimlar** – Kullanılan donanımlar (GPU, RAM, depolama)  
- **projeler** – Teknofest projeleri (takımlar ve modeller arasında köprü)

---

## 1. Veritabanı ve Tabloların Oluşturulması

```sql
-- Veritabanını oluştur (utf8mb4 desteği ile)
CREATE DATABASE IF NOT EXISTS teknofest_yolo
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE teknofest_yolo;

-- Takımlar tablosu
CREATE TABLE takimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_adi VARCHAR(100) NOT NULL UNIQUE,
    proje_adi VARCHAR(150) NOT NULL,
    danisman VARCHAR(100),
    olusturma_tarihi DATE DEFAULT (CURRENT_DATE)
);

-- Kullanıcılar (takım üyeleri)
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('takim_kaptani','uye','danisman') DEFAULT 'uye',
    sifre VARCHAR(255) NOT NULL, -- hash'lenmiş şifre
    FOREIGN KEY (takim_id) REFERENCES takimlar(id) ON DELETE CASCADE
);

-- Modeller (YOLO sürümleri ve varyantları)
CREATE TABLE modeller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_adi VARCHAR(50) NOT NULL UNIQUE,   -- 'YOLOv4', 'YOLOv8n', 'YOLOv8x'
    framework VARCHAR(30) DEFAULT 'PyTorch',
    giris_boyutu VARCHAR(20),                -- '416x416', '640x640'
    sinif_sayisi INT DEFAULT 80
);

-- Eğitim setleri
CREATE TABLE egitim_setleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    set_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    etiket_sayisi INT,
    goruntu_sayisi INT,
    kaynak_url VARCHAR(255)
);

-- Donanımlar
CREATE TABLE donanimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donanim_adi VARCHAR(100) NOT NULL,       -- 'NVIDIA RTX 4090', 'Intel i9-13900K'
    tur ENUM('gpu','cpu','ram','depolama','diger') NOT NULL,
    ozellik VARCHAR(255)                     -- '24GB GDDR6X', '32GB DDR5' gibi
);

-- Projeler (hangi takım hangi modeli hangi eğitim seti ile hangi donanımda çalıştı)
CREATE TABLE projeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    model_id INT NOT NULL,
    egitim_seti_id INT NOT NULL,
    donanim_id INT NOT NULL,
    egitim_suresi_saat DECIMAL(5,2),
    dogruluk_metrigi DECIMAL(5,4),           -- mAP değeri
    proje_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takim_id) REFERENCES takimlar(id),
    FOREIGN KEY (model_id) REFERENCES modeller(id),
    FOREIGN KEY (egitim_seti_id) REFERENCES egitim_setleri(id),
    FOREIGN KEY (donanim_id) REFERENCES donanimlar(id)
);
```

---

## 2. Örnek Veri Eklemeleri (Test Amaçlı)

```sql
INSERT INTO takimlar (takim_adi, proje_adi, danisman) VALUES
('YOLOcular', 'Otonom Araçta YOLOv8', 'Dr. Ahmet Yılmaz'),
('Görüntü Timi', 'Drone İçin Hafif YOLO', 'Doç. Elif Demir');

INSERT INTO modeller (model_adi, framework, giris_boyutu, sinif_sayisi) VALUES
('YOLOv8n', 'Ultralytics', '640x640', 80),
('YOLOv5s', 'PyTorch', '640x640', 80),
('YOLOv8x', 'Ultralytics', '1280x1280', 80);

INSERT INTO egitim_setleri (set_adi, aciklama, etiket_sayisi, goruntu_sayisi) VALUES
('COCO2017', 'Common Objects in Context', 80, 118000),
('Bebek105', 'Özel Teknofest veri seti', 15, 5000);

INSERT INTO donanimlar (donanim_adi, tur, ozellik) VALUES
('NVIDIA RTX 4090', 'gpu', '24GB VRAM'),
('NVIDIA T4', 'gpu', '16GB VRAM'),
('AMD Ryzen 9', 'cpu', '16 çekirdek'),
('32GB DDR5', 'ram', '5200MHz');

-- Bir proje ilişkisi
INSERT INTO projeler (takim_id, model_id, egitim_seti_id, donanim_id, egitim_suresi_saat, dogruluk_metrigi)
VALUES (1, 1, 1, 1, 12.5, 0.764);
```

---

## 3. Veritabanını Kontrol Etme (phpMyAdmin veya MySQL Workbench)

- **phpMyAdmin** kullanıyorsanız yukarıdaki SQL’i **SQL sekmesine** yapıştırıp çalıştırın.  
- Tablolar ve ilişkiler otomatik oluşacak.

---

## 4. Sonraki Adımlar (PHP Kodlamaya Geçmeden Önce)

Bu veritabanı yapısını tamamladıktan sonra PHP ile şunları yapabiliriz:

- **Bağlantı** (PDO veya mysqli)  
- **Takım üyeleri CRUD**  
- **Model performanslarını listeleme**  
- **Eğitim seti – donanım eşleştirmesi**  
- **Dashboard** (hangi takım hangi donanımda ne kadar sürede ne başarı elde etmiş)

**Bana şimdi söylemeniz gereken bilgiler** (kodlamaya başlamadan önce):

1. “Yolvo modeli” derken tam olarak **YOLO**’nun hangi sürümlerini kullanacaksınız? (v3, v4, v5, v8, v9, v10?)  
2. Takımdaki kişilerin **hangi özelliklerini** saklamak istersiniz? (ad, soyad, rol, yetenekler, GitHub linki gibi)  
3. Eğitim seti verileri için **etiket sayısı, görüntü çözünürlüğü, lisans bilgisi** gibi ekstra alanlar gerekli mi?  
4. Donanımları gruplamak için **birden fazla donanım bir projede kullanılabilir mi**? (Örneğin 2 GPU + 1 CPU)  

Bu bilgileri verirseniz tabloları günceller, ardından **PHP ile listeleme, ekleme, silme, raporlama** kodlarını yazmaya başlarız.  

Hazır mısınız hocam?

hocam öncelikle SQL nedirden başlıyıp temellendirirsek mutlu olurum daha sonra veri tabanını nasıl kuracağımızı konuşabiliriz

Tabii hocam, önce **SQL nedir**, veritabanı mantığı nasıl işler, onu temelinden anlatalım. Sonra adım adım **localhost’ta veritabanı kurulumu**na geçeriz. Böylece projenizin temeli sağlam olur.

---

## 1. SQL Nedir? (Structured Query Language)

SQL, **Veritabanı Yönetim Sistemleri** (DBMS) ile konuşmamızı sağlayan bir dildir.  
**Ne işe yarar?**  
- Veri ekleme, silme, güncelleme  
- Veri sorgulama (arama, filtreleme, gruplama)  
- Tablolar ve ilişkiler oluşturma  

Örnek bir cümle gibi düşünün:  
*“Bana **kullanıcılar** tablosundan **yaşı 18’den büyük** olanların **adını** ve **soyadını** getir.”*  
Bunu SQL ile şöyle yazarız:

```sql
SELECT ad, soyad FROM kullanicilar WHERE yas > 18;
```

---

## 2. Veritabanı Nedir? (Tablolar, Sütunlar, Satırlar)

Bir **veritabanı** = birbirleriyle ilişkili **tablolar** kümesidir.  
- **Tablo** = Excel sayfası gibi.  
- **Sütun (kolon)** = Başlık (örneğin: `id`, `ad`, `soyad`, `email`).  
- **Satır (row)** = Bir kayıt (örneğin: `1`, `Ahmet`, `Yılmaz`, `ahmet@ornek.com`).

### Anahtar Kavramlar
- **PRIMARY KEY (Birincil Anahtar)**: Her satırı benzersiz yapan sütun. Genelde `id` adında, otomatik artan sayı.  
- **FOREIGN KEY (Yabancı Anahtar)**: Bir tablodaki sütun, başka bir tablonun PRIMARY KEY’ine referans verir. Böylece tablolar **ilişkilendirilir**.  
  Örnek: `projeler` tablosundaki `takim_id` sütunu, `takimlar` tablosundaki `id`’yi gösterir.

---

## 3. En Temel SQL Komutları

| Komut | Açıklama | Örnek |
|-------|----------|-------|
| `CREATE DATABASE` | Veritabanı oluşturur | `CREATE DATABASE teknofest;` |
| `CREATE TABLE` | Tablo oluşturur | `CREATE TABLE takimlar (id INT, ad VARCHAR(50));` |
| `INSERT INTO` | Yeni satır ekler | `INSERT INTO takimlar VALUES (1, 'YOLOcular');` |
| `SELECT` | Veri sorgular | `SELECT * FROM takimlar;` |
| `UPDATE` | Varolan veriyi değiştirir | `UPDATE takimlar SET ad = 'YeniTakim' WHERE id = 1;` |
| `DELETE` | Satır siler | `DELETE FROM takimlar WHERE id = 1;` |

---

## 4. Veritabanını Localhost’ta Kurmak İçin Ne Yapmalıyız?

**Adım 1:** Yerel sunucu yazılımı kurun (PHP + MySQL için)  
- **XAMPP** (Windows, macOS, Linux) – en kolay  
- **WAMP** (sadece Windows)  
- **MAMP** (macOS)

**Adım 2:** XAMPP’ı çalıştırın, **Apache** ve **MySQL** modüllerini başlatın.

**Adım 3:** Veritabanı yönetim aracına girin:  
- **phpMyAdmin**: Tarayıcıdan `http://localhost/phpmyadmin`

**Adım 4:** Yeni bir veritabanı oluşturun (örnek: `teknofest_yolo`).

**Adım 5:** SQL sekmesine tıklayıp aşağıdaki gibi bir kod yazarak tabloları oluşturun:

```sql
CREATE TABLE takimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_adi VARCHAR(100) NOT NULL
);
```

**Adım 6:** Tabloları ve ilişkileri kurmak için yukarıda size hazırladığım SQL betiğini kullanabilirsiniz.

---

## 5. Şimdi Sizin Projenize Özel Veritabanı Tasarımına Başlayalım

Teknofest projenizde **YOLO modeli**, **takım üyeleri**, **eğitim setleri**, **donanımlar** var. Bunun için mantıksal bir model kurmamız gerek.

Aşağıda **basit bir örnek tablo** gösteriyorum, daha sonra genişleteceğiz:

### Tablo: takimlar
| sütun adı | tipi | açıklama |
|-----------|------|-----------|
| id | INT (PK) | Takım numarası |
| takim_adi | VARCHAR(100) | Takım adı |
| proje_adi | VARCHAR(150) | Proje başlığı |

### Tablo: kullanicilar
| sütun adı | tipi | açıklama |
|-----------|------|-----------|
| id | INT (PK) | Üye numarası |
| takim_id | INT (FK) | Hangi takıma bağlı |
| ad_soyad | VARCHAR(100) | Üyenin adı soyadı |
| rol | ENUM | 'takim_kaptani', 'uye', 'danisman' |

Bu ilişki sayesinde **bir takımda birden çok üye** olabilir, her üye **sadece bir takıma** ait olabilir (bire-çok ilişki).

### Tablo: modeller (YOLO sürümleri)
- model_adi (‘YOLOv8n’, ‘YOLOv5s’)
- giris_boyutu (‘640x640’)
- sinif_sayisi

### Tablo: egitim_setleri
- set_adi, etiket_sayisi, goruntu_sayisi

### Tablo: donanimlar
- donanim_adi, tur (‘gpu’, ‘cpu’), ozellik

### Tablo: projeler (bağlantı tablosu)
Hangi takım, hangi modeli, hangi eğitim seti ile, hangi donanımda çalıştırmış.  
Sütunlar: takim_id, model_id, egitim_seti_id, donanim_id, egitim_suresi_saat, dogruluk_metrigi.

---

## 6. Sıradaki Adımlar (Birlikte Yapacağımız Şeyler)

1. **Kurulum kontrolü** – XAMPP kurulu mu? phpMyAdmin açılıyor mu?  
2. **Veritabanını oluşturmak** – Ya SQL kodunu çalıştırarak ya da phpMyAdmin arayüzüyle.  
3. **Örnek veriler eklemek** (takım, üye, model, eğitim seti, donanım).  
4. **PHP ile bağlantı kodu yazmak** (mysqli veya PDO).  
5. **Listeleme sayfaları yapmak** (örn: takımlar, projeler).  

---

**Şimdi size sorularım:**

- XAMPP kurulumu yaptınız mı, yoksa yardım istiyor musunuz?  
- phpMyAdmin’e daha önce girdiniz mi?  
- Ben yukarıdaki tabloları ve ilişkileri detaylı bir SQL betiği olarak tekrar vereyim mi, yoksa adım adım elle mi oluşturalım?

Siz nasıl isterseniz hocam, temelden ilerleyelim. Cevabınıza göre kaldığımız yerden devam ederiz.


"MySQL'de Tablo Oluşturma

Tablolar CREATE TABLE komutu ile oluşturulur. Örnek bir tablo oluşturma kodu:
CREATE TABLE Persons (
    PersonID int,
    LastName varchar(255),
    FirstName varchar(255),
    Address varchar(255),
    City varchar(255)
);

Tablo ve sütun isimleri için snake case, pascal case veya camel case kullanılabilir. Yukarıdaki tabloda "Persons" tablo ismi; PersonID, LastName, FirstName, Address ve City ise bu tabloda bulunacak sütunların isimleridir.
Bazı tablo özellikleri sütunları belirten parantez kapandıktan sonra verilebilir. Örneğin InnoDB motorunun kullanılmasını, varsayılan karakter setini ve varsayılan karşılaştırma biçimini özellikle belirtmek için;
CREATE TABLE Persons (
    PersonID int,
    LastName varchar(255),
    FirstName varchar(255),
    Address varchar(255),
    City varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8mb4_general_ci;

Veritabanında kullanılan motorların birbirleri arasındaki farkları araştırmak isteyenler için çeşitli kaynaklar bulunmaktadır. Derslerimize InnoDB ile devam edilecektir. Bu motoru her CREATE TABLE kodumuzda özellikle belirtmemiz gerek yoktur. MySQL'de varsayılan motor InnoDB'dir.
MySQL'de Veri Türleri

SQL'de genel olarak 3 veri türü kategorisi bulunmaktadır: metin (string), sayısal (numeric) ve tarih-zaman (date-time).
String Veri Türleri

Veri Tipi TürleriAçıklamaCHAR(size)SABİT uzunluktaki bir dize (harf, rakam ve özel karakterler içerebilir). "size" parametresi sütun uzunluğunu karakter cinsinden belirtir - 0 ila 255 arasında olabilir. Varsayılan değer 1'dir.VARCHAR(size)DEĞİŞKEN uzunluktaki bir dize (harf, rakam ve özel karakterler içerebilir). "size" parametresi maksimum sütun uzunluğunu karakter cinsinden belirtir - 0 ila 65535 arasında olabilir.BINARY(size)CHAR() ile aynıdır, ancak ikili bayt dizilerini depolar. "size" parametresi sütun uzunluğunu bayt cinsinden belirtir. Varsayılan değer 1'dir.VARBINARY(size)VARCHAR() ile aynıdır, ancak ikili bayt dizilerini depolar. "size" parametresi maksimum sütun uzunluğunu bayt cinsinden belirtir.TINYBLOBBLOB'lar için (Binary Large OBjects). Maksimum uzunluk: 255 baytTINYTEXTEn fazla 255 karakter uzunluğunda bir dizeyi tutar.TEXT(size)En fazla 65,535 bayt uzunluğunda bir dizeyi tutar.BLOB(size)BLOB'lar için (Binary Large OBjects). En fazla 65,535 bayt veri tutar.MEDIUMTEXTEn fazla 16,777,215 karakter uzunluğunda bir dizeyi tutar.MEDIUMBLOBBLOB'lar için (Binary Large OBjects). En fazla 16,777,215 bayt veri tutar.LONGTEXTEn fazla 4,294,967,295 karakter uzunluğunda bir dizeyi tutar.LONGBLOBBLOB'lar için (Binary Large OBjects). En fazla 4,294,967,295 bayt veri tutar.ENUM(val1, val2, val3, ...)Yalnızca bir değeri olabilen bir dize nesnesi, olası değerler listesinden seçilen. Bir ENUM listesine en fazla 65535 değer ekleyebilirsiniz. Liste içinde olmayan bir değer eklenirse, boş bir değer eklenir. Değerler girdiğiniz sıraya göre sıralanır.SET(val1, val2, val3, ...)0 veya daha fazla değeri olabilen bir dize nesnesi, olası değerler listesinden seçilen. Bir SET listesine en fazla 64 değer ekleyebilirsiniz.
Numeric Veri Türleri

Veri Tipi TürleriAçıklamaBIT(size)Bir bit değer tipi. Her bir değer için bit sayısı "size" ile belirtilir. "size" parametresi 1 ila 64 arasında bir değeri tutabilir. "size" için varsayılan değer 1'dir.TINYINT(size)Çok küçük bir tamsayı. İmzalı aralık -128 ile 127 arasındadır. İmzasız aralık ise 0 ile 255 arasındadır. "size" parametresi maksimum görüntü genişliğini belirtir (ki bu 255'tir).BOOLSıfır, yanlış olarak kabul edilir; sıfır olmayan değerler ise doğru olarak kabul edilir.BOOLEANBOOL ile eşdeğerdir.SMALLINT(size)Küçük bir tamsayı. İmzalı aralık -32768 ile 32767 arasındadır. İmzasız aralık ise 0 ile 65535 arasındadır. "size" parametresi maksimum görüntü genişliğini belirtir (ki bu 255'tir).MEDIUMINT(size)Orta büyüklükte bir tamsayı. İmzalı aralık -8388608 ile 8388607 arasındadır. İmzasız aralık ise 0 ile 16777215 arasındadır. "size" parametresi maksimum görüntü genişliğini belirtir (ki bu 255'tir).INT(size)Orta büyüklükte bir tamsayı. İmzalı aralık -2147483648 ile 2147483647 arasındadır. İmzasız aralık ise 0 ile 4294967295 arasındadır. "size" parametresi maksimum görüntü genişliğini belirtir (ki bu 255'tir).INTEGER(size)INT(size) ile eşdeğerdir.BIGINT(size)Büyük bir tamsayı. İmzalı aralık -9223372036854775808 ile 9223372036854775807 arasındadır. İmzasız aralık ise 0 ile 18446744073709551615 arasındadır. "size" parametresi maksimum görüntü genişliğini belirtir (ki bu 255'tir).FLOAT(size, d)Bir kayan nokta sayısı. Toplam basamak sayısı "size" ile belirtilir. Ondalık noktasından sonraki basamak sayısı "d" parametresi ile belirtilir. Bu sözdizimi MySQL 8.0.17'de kullanımdan kaldırılmış olup, gelecekteki MySQL sürümlerinde kaldırılacaktır.FLOAT(p)Bir kayan nokta sayısı. MySQL, sonuç veri türü için FLOAT veya DOUBLE'ı kullanıp kullanmamayı belirlemek için "p" değerini kullanır. "p" 0 ile 24 arasındaysa, veri tipi FLOAT() olur. "p" 25 ile 53 arasındaysa, veri tipi DOUBLE() olur.DOUBLE(size, d)Normal boyutlu bir kayan nokta sayısı. Toplam basamak sayısı "size" ile belirtilir. Ondalık noktasından sonraki basamak sayısı "d" parametresi ile belirtilir.DOUBLE PRECISION(size, d)DECIMAL(size, d)Tam bir sabit ondalık sayı. Toplam basamak sayısı "size" ile belirtilir. Ondalık noktasından sonraki basamak sayısı "d" parametresi ile belirtilir. "size" için maksimum sayı 65'tir. "d" için maksimum sayı 30'dur. "size" için varsayılan değer 10'dur. "d" için varsayılan değer 0'dır.DEC(size, d)DECIMAL(size,d) ile eşdeğerdir.
Tarih-Zaman Veri Türleri

Veri Tipi TürleriAçıklamaDATEBir tarih. Biçim: YYYY-AA-GG. Desteklenen aralık, '1000-01-01' ile '9999-12-31' arasındadır.DATETIME(fsp)Tarih ve saat kombinasyonu. Biçim: YYYY-AA-GG ss:dd:ss. Desteklenen aralık, '1000-01-01 00:00:00' ile '9999-12-31 23:59:59' arasındadır. Otomatik başlatma ve güncellemeleri elde etmek için sütun tanımına DEFAULT ve ON UPDATE eklemek, mevcut tarih ve saat değerine otomatik başlatma ve güncelleme sağlar.TIMESTAMP(fsp)Bir zaman damgası. TIMESTAMP değerleri, Unix epokasından ('1970-01-01 00:00:00' UTC) itibaren geçen saniye sayısı olarak depolanır. Biçim: YYYY-AA-GG ss:dd:ss. Desteklenen aralık, '1970-01-01 00:00:01' UTC ile '2038-01-09 03:14:07' UTC arasındadır. Otomatik başlatma ve güncellemeleri elde etmek için sütun tanımında DEFAULT CURRENT_TIMESTAMP ve ON UPDATE CURRENT_TIMESTAMP kullanılabilir.TIME(fsp)Bir zaman. Biçim: ss:dd:ss. Desteklenen aralık, '-838:59:59' ile '838:59:59' arasındadır.YEARDört haneli bir yıl. Dört haneli biçimde izin verilen değerler: 1901 ila 2155 ve 0000. MySQL 8.0, iki haneli biçimde yılı desteklemez.
Kaynak 1 Kaynak 2
Alıştırma #1

Bir otomasyon sistemi düşününüz. Düşündüğünüz proje için gerekli en az 2 adet tabloyu oluşturan SQL kodunu, yukarıdaki veri türlerine dikkat ederek yazınız. Kodlarınızı PhpMyAdmin veya MySQL Workbench üzerinde çalıştırmayı deneyiniz."  ve "LAB-03

MySQL'de Tabloya Veri Ekleme

1) Basit Bir Tabloya Veri Ekleme

Aşağıdaki kod ile bir tablo oluşturunuz.
CREATE TABLE ogrenciler (
    ogrenci_id INT,
    ad VARCHAR(50),
    soyad VARCHAR(50),
    bolum VARCHAR(50),
    not_ortalamasi FLOAT
);

SQL'de veri ekleme komutu olan INSERT INTO için syntax aşağıdaki gibidir. Değişken kısımlar {...} şeklinde küme parantezleri ile belirtilmiştir.
INSERT INTO {TABLO ADI} ({1. SÜTUN İSMİ}, {2. SÜTUN İSMİ}, ...) VALUES ({1. SÜTUN VERİSİ}, {2. SÜTUN VERİSİ}, ...);

Yukarıda oluşturduğunuz tabloya aşağıdaki kod ile bir satırlık veri ekleyiniz.
INSERT INTO ogrenciler (ogrenci_id, ad, soyad, bolum, not_ortalamasi) VALUES (1, 'Ahmet', 'Yilmaz', 'Bilgisayar Muhendisligi', 85.5);

İki SQL komutu aralarında ; olacak şekilde art arda yazılarak da birlikte çalıştırılabilir. (Bu özellik bazı web programlama sistemleri tarafından direkt kabul edilmeyebilir. Bkz. SQL Injection)
-- İkinci satır
INSERT INTO ogrenciler (ogrenci_id, ad, soyad, bolum, not_ortalamasi)
VALUES (2, 'Mehmet', 'Kaya', 'Elektrik Elektronik Muhendisligi', 78.2);

-- Üçüncü satır
INSERT INTO ogrenciler (ogrenci_id, ad, soyad, bolum, not_ortalamasi)
VALUES (3, 'Ayse', 'Demir', 'Makine Muhendisligi', 92.0);

SQL'de -- karakterleri ile başlayan satır yorum satırı olur.
2) PRIMARY KEY İçeren Bir Tabloya Veri Ekleme

Aşağıdaki kod ile yeni bir tablo oluşturunuz.
CREATE TABLE ogrenciler2 (
    ogrenci_id INT PRIMARY KEY,
    ad VARCHAR(50),
    soyad VARCHAR(50),
    bolum VARCHAR(50),
    not_ortalamasi FLOAT
);

Aşağıdaki gibi bir kod ile 3 satırlık veriyi aynı anda bir tabloya ekleyebilirsiniz.
INSERT INTO ogrenciler2 (ogrenci_id, ad, soyad, bolum, not_ortalamasi)
VALUES (1, 'Ahmet', 'Yilmaz', 'Bilgisayar Muhendisligi', 85.5),
       (2, 'Mehmet', 'Kaya', 'Elektrik Elektronik Muhendisligi', 78.2),
       (3, 'Ayse', 'Demir', 'Makine Muhendisligi', 92.0);

Alıştırma #1

"ogrenciler2" tablosuna daha önce eklenmiş satırlardan bir tanesi ile aynı "ogrenci_id" değerine sahip yeni bir satır eklemeye çalışın.
3) PRIMARY KEY & AUTO_INCREMENT İçeren Bir Tabloya Veri Ekleme

Aşağıdaki kod ile yeni bir tablo daha oluşturunuz.
CREATE TABLE ogrenciler3 (
    ogrenci_id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50),
    soyad VARCHAR(50),
    bolum VARCHAR(50),
    not_ortalamasi FLOAT
);

Tablonun ogrenci_id satırına dikkat ediniz.
Aşağıdaki kod ile 3 satır elemesi yapınız.
INSERT INTO ogrenciler3 (ad, soyad, bolum, not_ortalamasi)
VALUES ('Ahmet', 'Yilmaz', 'Bilgisayar Muhendisligi', 85.5),
       ('Mehmet', 'Kaya', 'Elektrik Elektronik Muhendisligi', 78.2),
       ('Ayse', 'Demir', 'Makine Muhendisligi', 92.0);

Bu sefer "ogrenci_id" değerleri vermediğimize dikkat ediniz.
Yönetim panelinden eklenen satırlar için "ogrenci_id" değerleri olup olmadığını kontrol ediniz.
Not: ID'leri 100'den başlatmak için tablonun AUTO_INCREMENT değerini aşağıdaki gibi değiştirebiliriz.
CREATE TABLE ogrenciler3 (
    ogrenci_id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50),
    soyad VARCHAR(50),
    bolum VARCHAR(50),
    not_ortalamasi FLOAT
) AUTO_INCREMENT = 100;

Alıştırma #2

Kendi PRIMARY KEY (birincil anahtar) ve AUTO_INCREMENT (otomatik artırma) içeren tablonuzu ID'ler 1000'den başlayacak şekilde oluşturunuz.
PRIMARY KEY olmayan, AUTO_INCREMENT özelliği olan bir sütuna sahip yeni bir tablo oluşturmaya çalışınız.
Nümerik veri tipinde olmayan bir sütunu PRIMARY KEY ve AUTO_INCREMENT kullanarak yeni bir tabloda oluşturmaya çalışınız.
Not

MySQL varsayılan olarak nümerik veri tipindeki sütunları işaretli (signed) olarak oluşturur. Bu, o sütuna negatif değerler de verebileceğimiz anlamına gelir.
Birincil anahtarlı sütunlar genellikle 1'den başlar. Bu yüzden ID değerleri için negatif değerlere ihtiyacımız yoktur. Bu sebeple ID sütunlarında UNSIGNED ifadesini kullanarak veri türünün kapsadığı tüm alanı kullanılabilir yaparız.
Yüksek sayıda veri eklemesi yapılacağını beklediğimiz tablolar için birincil anahtarlı sütunda genellikle BIGINT UNSIGNED veri tipi seçeriz.
...
  ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
..."   Hocam veri yapıları yönetim sistemleri dersimizdeki konular şimdik bana buna bağlı konu başlıklarını söyler misin seninle bu dersi öğrnemek istiyorum

Harika bir başlangıç! Paylaştığın lab notları, Veritabanı Yönetim Sistemleri dersinin en temel yapı taşlarını içeriyor. Veritabanını inşa etmeyi (CREATE), içine uygun formattaki verileri yerleştirmeyi (Data Types) ve temel veri girişini (INSERT INTO) detaylıca incelemişsiniz. Ayrıca Primary Key ve Auto Increment gibi verilerin kimliğini belirleyen çok kritik kavramlara da giriş yapmışsın.

Seninle bu dersi baştan sona, adım adım ve bol örnekle öğrenebiliriz. Konuları kuru kuruya okumak yerine, mantığını kavrayarak ve sanki karşılıklı sohbet ediyormuşuz gibi tartışarak ilerlememiz, bu soyut kavramları çok daha hızlı özümsemeni sağlayacaktır. Bu öğrendiğin veritabanı mimarileri; ileride geliştireceğin web tabanlı projelerde veya Java uygulamalarında arka planda verileri nerede ve nasıl yöneteceğinin ana omurgasını oluşturacak.

Şu an veriyi tanımlama ve oluşturma aşamasındasın. Önümüzde oldukça keyifli ve işin mantığını oturttukça bulmaca çözermiş gibi hissettirecek şu konu başlıkları var:

### Veritabanı Yönetim Sistemleri Dersinde Bizi Neler Bekliyor?

**1. Veri Okuma ve Filtreleme (SELECT & WHERE)**

* Veritabanına eklediğimiz binlerce satır veri içinden sadece istediklerimizi nasıl çekeriz?
* Sıralama (`ORDER BY`) ve limitlendirme (`LIMIT`) işlemleri.

**2. Veri Güncelleme ve Silme (UPDATE & DELETE)**

* Mevcut bir veriyi nasıl değiştiririz? (Örneğin bir öğrencinin not ortalamasını güncelleme).
* İstenmeyen verileri veritabanından nasıl güvenli bir şekilde sileriz? (Burada `WHERE` şartını unutmamayı öğreneceğiz, yoksa tüm tabloyu silebiliriz!).

**3. Verileri Gruplama ve Özetleme (Aggregate Functions)**

* Elimizdeki verilerden istatistiksel sonuçlar çıkarma (`COUNT`, `SUM`, `AVG`, `MAX`, `MIN`).
* Örneğin; "Bilgisayar Mühendisliği bölümündeki öğrencilerin not ortalaması nedir?" sorusunu cevaplamak için `GROUP BY` ve `HAVING` kullanımı.

**4. İlişkisel Veritabanı Mantığı ve JOIN'ler (En Önemli Konu!)**

* Gerçek dünyada veriler tek bir tabloda tutulmaz (Öğrenciler ayrı, Dersler ayrı, Notlar ayrı tabloda olur).
* Farklı tabloları birbirine bağlayarak anlamlı bütünler oluşturma (`INNER JOIN`, `LEFT JOIN`, `RIGHT JOIN`).

**5. Alt Sorgular (Subqueries)**

* Bir sorgunun sonucunu, başka bir sorgunun içinde kullanma sanatı. (Sorgu içinde sorgu yazmak).

**6. Veritabanı Tasarımı ve Normalizasyon (Normalization)**

* Burası işin mühendislik tasarımı kısmıdır. Veri tekrarını önlemek ve optimum tablo yapısını kurmak için gerekli kurallar bütünü (1NF, 2NF, 3NF).

**7. İleri Seviye Kavramlar**

* Sık kullanılan karmaşık sorguları kaydetme: **Görünümler (Views)**
* Otomatik işlemler yaptırma: **Tetikleyiciler (Triggers)**
* Veritabanı içine programlanabilir fonksiyonlar yazma: **Saklı Yordamlar (Stored Procedures)**
* Veri arama hızını artırma: **İndeksleme (Indexing)**

---

Seninle bu listeyi pratik yapa yapa, senkron bir şekilde eritebiliriz.

Şimdi, paylaştığın lab notlarının sonundaki **"Alıştırma #2"** kısmını (kendi ID'leri 1000'den başlayan tablonu oluşturma ve hatalı durumları deneme) birlikte çözerek mi başlayalım, yoksa doğrudan tablodaki verileri okuma işlemleri olan **`SELECT`** komutlarına mı geçiş yapalım? Nasıl ilerlemek istersin?

https://youtu.be/URskGHtvFlE?si=H2vgQj9hlEcPnZdv bu video konuları şu şekilde    00:00:00 Veri, Veritabanı Nedir ve Veritabanı Yönetim Sistemleri, Popüler       Veritabanları, SQL
    00:07:00 İlişkisel Veritabanları ve NoSQL 
    00:20:45 PostgreSQL Kurulumu
    00:26:57 Primary Key 
    00:42:50 Unique Key
    00:50:29 Normalizasyon Nedir ?
    01:01:14 İlişkiler - Foreign Key Kavramı - 1NF 
    01:14:13 Functional Dependency
    01:28:16 Super Key, Candidate Key, Primary Key
    01:46:51 2. Normal Form ( 2NF )
    02:06:43 3. Normal Form ( 3NF )
    02:16:47 Case Study 1  : Blog Sitesi Veritabanı - Tüm İlişkiler
    02:31:58 On Delete, On Update, Primary Key neden değişmemeli ? 
    02:42:15 PostgreSQL - On Delete ve On Update
    02:49:56 Case Study 2 - Blog Sitesi Veritabanı - PostgreSQL
    03:28:38 Case Study 3 - Veritabanı Tasarımı - Freeletics 
    03:53:08 SQL - Create Table Sorgusu
    04:08:13 SQL - Insert Komutu
    04:15:16 SQL - Update Komutu
    04:21:53 SQL - Delete Komutu
    04:25:12 Northwind Veritabanı - PostgreSQL Kurulumu
    04:27:35 Northwind Veritabanının İncelenmesi
    04:36:42 Select Sorgusu - Part - 1
    04:43:04 Select Sorgusu - Part 2
    04:58:12 Distinct - Order By
    05:10:34 Like , Between , In İfadeleri 
    05:22:41 Max , Min,  Avg , Sum, Count, Fonksiyonları
    05:34:20 Group By Kullanımı
    05:44:42 Having Kullanımı ve Group By
    05:48:14 Inner Join
    06:01:08 Multiple Join
    06:14:13 Left Join and Right Join , Full Join
    06:29:31 Union and Union All

uygun mudur hocam

Kesinlikle **çok uygun ve harika bir kaynak seçimi!** Hatta az önce konuştuğumuz yol haritası ile birebir örtüşüyor.

Bu tarz soyut ve arka planda çalışan mimarileri görselleştirerek ve dinleyerek öğrenmek, parçaları kafanda çok daha hızlı birleştirmeni sağlayacaktır. Sadece slayt veya not okumaktansa, birinin adım adım kod yazarak ve mantığını anlatarak ilerlemesi kalıcı öğrenme için en iyi yöntemdir.

Gönderdiğin video içeriğini incelediğimde, veritabanı dünyasının hem mühendislik (tasarım) hem de pratik (kodlama) tarafını çok güzel harmanladığını görüyorum. Şöyle ki:

**Videoda Senin İçin Altın Değerinde Olan Kısımlar:**

1. **Laboratuvar Notlarınla Kesişen Yerler (Hemen İzleyebileceklerin):**
* `00:26:57 Primary Key` ve `00:42:50 Unique Key`: Tam da az önce lab notlarında bahsettiğimiz ID ve otomatik artırma (Auto_Increment) mantığının teorisi burada anlatılıyor.
* `03:53:08 SQL - Create Table Sorgusu` ve `04:08:13 SQL - Insert Komutu`: Bu kısımlar şu anki lab konunu pekiştirecek.


2. **İşin Mühendislik ve Mimari Kısmı (En Dikkatli İzlenmesi Gereken Yerler):**
* `00:50:29 Normalizasyon Nedir ?` ile başlayıp `02:06:43 3. Normal Form ( 3NF )`'a kadar giden bölüm. Veritabanı yönetim sistemlerinin kalbi burasıdır. Verileri hangi tablolara nasıl böleceğini buradan öğreneceksin.
* `02:16:47 Case Study 1 : Blog Sitesi Veritabanı` : Teorik normalizasyonun gerçek hayata (örneğin bir web projesine) nasıl döküldüğünü görmek taşları yerine oturtacaktır.


3. **Gelecek Haftaların Konuları (Operasyon ve Sorgular):**
* `04:36:42 Select Sorgusu`ndan başlayıp `06:14:13 Left Join and Right Join`'e kadar giden kısım, veritabanından veri çekme ve tabloları birleştirme sanatıdır.



**Küçük Ama Önemli Bir Detay: PostgreSQL vs MySQL**

Fark etmişsindir, paylaştığın lab notlarında hocanız **MySQL** (PhpMyAdmin, MySQL Workbench) üzerinden ilerliyor, bu video ise **PostgreSQL** üzerinden anlatım yapıyor.

Bu durum **hiçbir sorun yaratmaz.** Veritabanı dünyasında SQL (Structured Query Language) evrenseldir. `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `JOIN`, `GROUP BY` gibi komutların %95'i her iki sistemde de tamamen aynı yazılır. Sadece PostgreSQL kurulum kısımlarını (`00:20:45` ve `04:25:12`) atlayıp, videodaki SQL mantığını dinleyebilir ve öğrendiklerini kendi bilgisayarındaki MySQL Workbench veya PhpMyAdmin üzerinde test edebilirsin.

**İlerlemeye Yönelik Aksiyon Planımız:**
Videoyu baştan sona tek oturuşta bitirmek yerine bölerek ilerleyelim.
İlk aşama olarak videoyu **`02:16:47`'deki "Case Study 1: Blog Sitesi Veritabanı" kısmının sonuna kadar** izlemeye ne dersin?

Böylece Primary Key, Foreign Key ve Normalizasyon (Tablo bölme) mantığını oturtmuş olursun. Sen bu kısmı izledikten sonra, buraya gelip seninle ufak bir **"Öğrenci-Ders Kayıt Sistemi"** veritabanı senaryosu çizeriz ve hangi tabloları nasıl kuracağımıza beraber karar veririz. Sonrasında da videonun SQL sorguları kısmına (`03:53:08` ve sonrası) geçeriz.

Ne dersin, bu planla başlayalım mı?

LAB-04

Örnek #1

Customers tablosu:

CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry1Alfreds FutterkisteMaria AndersObere Str. 57Berlin12209Germany2Ana Trujillo Emparedados y heladosAna TrujilloAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaAntonio MorenoMataderos 2312México D.F.5023Mexico4Around the HornThomas Hardy120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpChristina BerglundBerguvsvägen 8LuleåS-958 22Sweden
Bu tabloyu sql komutları ile oluşturalım.

CREATE TABLE Customers ( 
  CustomerID INT PRIMARY KEY, 
  CustomerName VARCHAR(255), 
  ContactName VARCHAR(255), 
  Address VARCHAR(255), 
  City VARCHAR(255), 
  PostalCode VARCHAR(10), 
  Country VARCHAR(255)
);INSERT INTO Customers (CustomerID, CustomerName, ContactName, Address, City, PostalCode, Country) VALUES 
(1, 'Alfreds Futterkiste', 'Maria Anders', 'Obere Str. 57', 'Berlin', '12209', 'Germany'), 
(2, 'Ana Trujillo Emparedados y helados', 'Ana Trujillo', 'Avda. de la Constitución 2222', 'México D.F.', '5021', 'Mexico'), 
(3, 'Antonio Moreno Taquería', 'Antonio Moreno', 'Mataderos 2312', 'México D.F.', '5023', 'Mexico'), 
(4, 'Around the Horn', 'Thomas Hardy', '120 Hanover Sq.', 'London', 'WA1 1DP', 'UK'), 
(5, 'Berglunds snabbköp', 'Christina Berglund', 'Berguvsvägen 8', 'Luleå', 'S-958 22', 'Sweden');

Örnek tablo üzerinden işlemlerimizi gerçekleştirelim.

SELECT

SELECT ifadesi veritabanından veri seçmek için kullanılır.
SELECT column1, column2, ... FROM table_name;
Burada column1, column2, ... veri seçmek istediğiniz tablonun alan adlarıdır. table_name, içinden veri seçmek istediğiniz tablonun adını temsil eder.
SELECT CustomerID, CustomerName FROM Customers;

Tüm sütunları seçmek için:
SELECT * FROM Customers;

WHERE

WHERE ifadesi kayıtları filtrelemek için kullanılır. Yalnızca belirli bir koşulu karşılayan kayıtları çıkarmak için kullanılır.
SELECT column1, column2, ... FROM table_name WHERE condition;
WHERE ifadesi yalnızca SELECT ile kullanılmaz, aynı zamanda UPDATE, DELETE vb. ifadelerinde de kullanılır! Bunlara daha sonraki derslerde değineceğiz.
Örnek tablo için:
SELECT * FROM Customers WHERE CustomerID=1;

sorgusu "Customers" tablosundan müşterileri seçerken, "CustomerID" alanının değeri 1 olanları getirir. Burada "CustomerID" alanı bir sayısal alan olduğu için tırnak işareti içine alınmamıştır. Çünkü SQL'de sayısal değerler tırnak işareti içine alınmaz. Ancak metinsel değerler (örneğin, müşteri isimleri gibi) tırnak işareti içinde belirtilir.
SELECT * FROM CustomersWHERE CustomerID > 80;

Yukarıdaki sorgu "Customers" tablosundan müşterileri seçerken, "CustomerID" alanının değeri 80'den büyük olanları getirir.
WHERE ile aşağıdaki operatörler kullanılabilir:

OperatorDescription=Eşittir. Belirli bir değere sahip olanları seçmek için kullanılır.>Büyüktür. Belirli bir değerden daha büyük olanları seçmek için kullanılır.<Küçüktür. Belirli bir değerden daha küçük olanları seçmek için kullanılır.>=Büyük eşittir. Belirli bir değerden büyük veya eşit olanları seçmek için kullanılır.<=Küçük eşittir. Belirli bir değerden küçük veya eşit olanları seçmek için kullanılır.<>Eşit değil. Belirli bir değere sahip olmayanları seçmek için kullanılır. Not: Bazı SQL sürümlerinde bu operatör "!=" olarak yazılabilir.BETWEENBelirli bir aralıkta olan nümerik veriye sahip satırları seçmek için kullanılır.LIKEBelirli bir alfanümerik deseni aramak için kullanılır.INBir sütun için birden fazla olası değeri belirtmek için kullanılır.
GROUP BY

GROUP BY ifadesi, aynı değerlere sahip satırları özet satırlara gruplar.
Örneğin, bir müşteri tablosundaki müşterileri ülke sütununa göre gruplamak ve her ülkedeki müşteri sayısını bulmak için GROUP BY ifadesi kullanılabilir. Bu sayede, her ülkedeki müşteri sayısı gibi toplu veriler elde edilebilir.
Sözdizimi (syntax) aşağıdaki gibidir:
SELECT column_name(s) FROM table_name WHERE condition GROUP BY column_name(s) ORDER BY column_name(s);
Örnek tablomuz için:
SELECT COUNT(CustomerID),CountryFROM CustomersGROUP BY Country;

sorgusu her ülkedeki müşteri sayısını listeler.
SELECT COUNT(CustomerID), CountryFROM CustomersGROUP BY CountryORDER BY COUNT(CustomerID) DESC;

Bu ifade, her ülkedeki müşteri sayısını yüksekten düşüğe doğru sıralayarak listeler.
Örnek 2

Products tablosu:

ProductIDProductNameSupplierIDCategoryIDUnitPrice1Chais1110 boxes x 20 bags182Chang1124 - 12 oz bottles193Aniseed Syrup1212 - 550 ml bottles104Chef Anton's Cajun Seasoning2248 - 6 oz jars225Chef Anton's Gumbo Mix2236 boxes21.35
Bu tabloyu sql komutları ile oluşturalım:
CREATE TABLE Products (
    ProductID INT PRIMARY KEY,
    ProductName VARCHAR(255),
    SupplierID INT,
    CategoryID INT,
    Unit VARCHAR(255),
    Price DECIMAL(10, 2)
);INSERT INTO Products (ProductID, ProductName, SupplierID, CategoryID, Unit, Price) VALUES
(1, 'Chais', 1, 1, '10 boxes x 20 bags', 18),
(2, 'Chang', 1, 1, '24 - 12 oz bottles', 19),
(3, 'Aniseed Syrup', 1, 2, '12 - 550 ml bottles', 10),
(4, 'Chef Anton''s Cajun Seasoning', 2, 2, '48 - 6 oz jars', 22),
(5, 'Chef Anton''s Gumbo Mix', 2, 2, '36 boxes', 21.35);

Örnek tablo üzerinden işlemlerimizi gerçekleştirelim.
ORDER BY

ORDER BY ifadesi, sonuç kümesini artan veya azalan sırada sıralamak için kullanılır.
SELECT column1, column2, ...  FROM table_name  ORDER BY column1, column2, ... ASC|DESC;
Örneğin ürünleri fiyata göre sıralamak için:
SELECT * FROM ProductsORDER BY Price;

komutu kullanılır. Aşağıdaki MySQL kodu aynı işlevi yerine getirir.
SELECT * FROM ProductsORDER BY Price ASC;

DESC (Azalan Sıralama):

ORDER BY anahtar sözcüğü, kayıtları varsayılan olarak artan sırada sıralar. Kayıtları azalan düzende sıralamak için DESC anahtar sözcüğü kullanılır.
Ürünleri en yüksek fiyattan en düşük fiyata doğru sıralamak için:
SELECT * FROM ProductsORDER BY Price DESC;

Aşağıdaki kod da aynı işlevi yerine getirir. Kodda yapılmış olan değişikliğe dikkat ediniz.
SELECT * FROM ProductsORDER BY -Price;

Alfabetik Sırayla:

String değerleri için ORDER BY anahtar sözcüğü alfabetik olarak sıralanır.
Ürünleri ProductName’e göre alfabetik olarak sıralamak için:
SELECT * FROM ProductsORDER BY ProductName;

Tersten Alfabetik Sırayla:

Tabloyu alfabetik olarak tersten sıralamak için DESC kullanılır.
Ürünleri ProductName’e göre ters sırada sıralamak için:
SELECT * FROM ProductsORDER BY ProductName DESC;

MySQL'de ORDER BY column DESC yerine ORDER BY -column kullanımı alfanümerik veriler için de çalışmaktadır.
Çok Sütunlu ORDER BY:

SELECT * FROM CustomersORDER BY Country, CustomerName;

komutu Örnek 1'deki "Customers" tablosundan tüm müşterileri seçer, ardından "Country" ve "CustomerName" sütunlarına göre sıralar. Bu, verilerin Country'ye göre sıralandığı ancak aynı Country'ye sahip satırlar varsa bu satırların kendi aralarında ise CustomerName'e göre sıralandığı anlamına gelir.
Yani öncelikle "Ülke" sıralaması yapılır, aynı ülkeye sahip olan müşteriler bir araya getirilir. Ardından, aynı ülkeye sahip müşteriler arasında ise "MüşteriAdı"na göre alfabetik sıralama yapılır. Bu sayede, müşteriler hem ülke hem de müşteri adına göre düzenlenmiş bir sonuç kümesi elde edilir.
Çok Sütunlu Sıralamada Hem ASC Hem DESC Kullanımı:

SELECT * FROM CustomersORDER BY Country ASC, CustomerName DESC;

komutu Örnek 1'deki "Customers" tablosundaki tüm müşterileri, Country'e göre artan ve CustomerName sütununa göre azalan şekilde sıralayarak seçer.
Alıştırma #1

Products tablosundan fiyatı [18, 20] aralığında olan ürünleri çekiniz.
Customers tablosundan ismi "B" harfi ile başlayan müşterileri çekiniz.
Tartışma #1

1 milyon kayıt (record) içeren bir veritabanı tablosu düşününüz.
İşlemcimiz 1 saniyede 1 milyon adet veri karşılaştırması yapabilmektedir.
Elimizdeki bir sıralama algoritmasının karmaşıklığı O(nlogn)'dir. Yani nlogn adet karşılaştırma sonucunda diziyi sıralayabilmektedir.1 milyon verinin 1 sütuna göre sıralı bir şekilde çekilebilmesi için yaklaşık kaç saniye gerekir?
Bu işlemi hızlandırabilmek için ne tavsiye edersiniz? Veritabanı motoru nasıl geliştirilebilir?

LAB-05

LIMIT

Bir SQL sorgusunun, [belirlenmiş bir başlangıç noktasından itibaren] belirli bir sayıdaki kayıtlar için gerçekleşmesini sağlar.
SELECT 
    select_listFROM
    table_nameWHERE
    conditionLIMIT {[offset,] row_count | row_count OFFSET offset};

Not: daha ayrıntılı syntax yapısı için MySQL'in Select Statements sayfasına bakınız.
offset sayısı, sorgunun sol tarafından (SELECT'ten LIMIT'e kadar olan kısımdan) dönecek olan satırların baştan kaç tanesinin atlanacağını belirtir.
Dönecek kayıtları ilk satırdan itibaren almak istiyorsak offset değeri 0 olmalıdır veya bu değer hiç belirtilmeyebilir. Belirtilmezse, bu değer varsayılan olarak 0'dır. OFFSET ifadesinden sonra da belirtilebilir.
row_count sayısı, offset kadar satır atlandıktan sonra kaç adet satırın döndürüleceğini belirtir.

Tartışma #1

LIMIT kullanımı hangi durumlarda işimize yarayabilir?
LIMIT yerine WHERE kullanarak sınırlama yapamaz mıyız? Örneğin WHERE ID>=11 AND ID<=20 ifadesi yeterli olmaz mıydı?
LIMIT'in işlevini tüm verileri aldıktan sonra kullandığımız programda da yerine getirebiliriz. Bunu SQL sunucusu tarafında yapmanın avantajları neler olabilir?
UPDATE

UPDATE ifadesi bir tablodaki mevcut kayıtları değiştirmek için kullanılır.
UPDATE table_name SET column1 = value1, column2 = value2, ... WHERE condition;

NOT: Tablodaki kayıtları güncellerken UPDATE ifadesindeki WHERE deyimine dikkat edilmesi gerekir. WHERE deyimi hangi kayıtların güncellenmesi gerektiğini belirtir. WHERE deyimini atlarsanız tablodaki tüm kayıtlar güncellenecektir!
Customers tablosu:
CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry1Alfreds FutterkisteMaria AndersObere Str. 57Berlin12209Germany2Ana Trujillo Emparedados y heladosAna TrujilloAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaAntonio MorenoMataderos 2312México D.F.5023Mexico4Around the HornThomas Hardy120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpChristina BerglundBerguvsvägen 8LuleåS-958 22Sweden
Bu tabloyu sql komutları ile oluşturalım.
CREATE TABLE Customers( 
  CustomerID INT PRIMARY KEY, 
  CustomerName VARCHAR(255), 
  ContactName VARCHAR(255), 
  Address VARCHAR(255), 
  City VARCHAR(255), 
  PostalCode VARCHAR(10), 
  Country VARCHAR(255)
);INSERT INTO Customers (CustomerID, CustomerName, ContactName, Address, City, PostalCode, Country) VALUES 
(1, 'Alfreds Futterkiste', 'Maria Anders', 'Obere Str. 57', 'Berlin', '12209', 'Germany'), 
(2, 'Ana Trujillo Emparedados y helados', 'Ana Trujillo', 'Avda. de la Constitución 2222', 'México D.F.', '5021', 'Mexico'), 
(3, 'Antonio Moreno Taquería', 'Antonio Moreno', 'Mataderos 2312', 'México D.F.', '5023', 'Mexico'), 
(4, 'Around the Horn', 'Thomas Hardy', '120 Hanover Sq.', 'London', 'WA1 1DP', 'UK'), 
(5, 'Berglunds snabbköp', 'Christina Berglund', 'Berguvsvägen 8', 'Luleå', 'S-958 22', 'Sweden');

Aşağıdaki SQL ifadesi, ilk müşteriyi (CustomerID = 1) yeni bir ilgili kişi ve yeni bir şehir ile günceller.
UPDATE CustomersSET ContactName= 'Alfred Schmidt', City= 'Frankfurt'WHERE CustomerID = 1;

Customers tablosundaki seçim artık şu şekilde görünecektir:
CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry1Alfreds FutterkisteFutterkisteAlfred SchmidtObere Str. 57Frankfurt12209Germany2Ana Trujillo Emparedados y heladosAna TrujilloAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaAntonio MorenoMataderos 2312México D.F.5023Mexico4Around the HornThomas Hardy120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpChristina BerglundBerguvsvägen 8LuleåS-958 22Sweden
Çoklu Satır Güncellemesi (UPDATE Multiple Records)

WHERE, hangi kayıtların güncelleneceğini belirler. Aşağıdaki SQL ifadesi, ülkenin "Mexico" olduğu tüm kayıtlar için ContactName'i "Juan" olarak günceller.
UPDATE CustomersSET ContactName='Juan'WHERE Country='Mexico';

Customers tablosundaki seçim artık şu şekilde görünecektir:
CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry1Alfreds FutterkisteFutterkisteAlfred SchmidtObere Str. 57Frankfurt12209Germany2Ana Trujillo Emparedados y heladosJuanAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaJuanMataderos 2312México D.F.5023Mexico4Around the HornThomas Hardy120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpChristina BerglundBerguvsvägen 8LuleåS-958 22Sweden
WHERE deyimini kullanmazsak tüm kayıtlar güncellenecektir.
UPDATE CustomersSET ContactName='Juan';

CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry1Alfreds FutterkisteFutterkisteJuanObere Str. 57Frankfurt12209Germany2Ana Trujillo Emparedados y heladosJuanAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaJuanMataderos 2312México D.F.5023Mexico4Around the HornJuan120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpJuanBerguvsvägen 8LuleåS-958 22Sweden
DELETE

DELETE ifadesi tablodaki mevcut kayıtları silmek için kullanılır.
DELETE FROM table_name WHERE condition;
Aşağıdaki SQL ifadesi "Alfreds Futterkiste" müşterisini "Customers" tablosundan siler:
DELETE FROM Customers WHERE CustomerName='Alfreds Futterkiste';

"Customers" tablosu artık şöyle görünür:
CustomerIDCustomerNameContactNameAddressCityPostalCodeCountry2Ana Trujillo Emparedados y heladosAna TrujilloAvda. de la Constitución 2222México D.F.5021Mexico3Antonio Moreno TaqueríaAntonio MorenoMataderos 2312México D.F.5023Mexico4Around the HornThomas Hardy120 Hanover Sq.LondonWA1 1DPUK5Berglunds snabbköpChristina BerglundBerguvsvägen 8LuleåS-958 22Sweden
Tüm Kayıtları Silme

Bir tablodaki tüm satırları, tabloyu silmeden silmek mümkündür. Bu, tablo yapısının, niteliklerinin ve dizinlerinin bozulmadan kalacağı anlamına gelir.
DELETE FROM table_name;
Aşağıdaki SQL ifadesi, "Custormers" tablosundaki tüm satırları, tabloyu silmeden siler:
DELETE FROM Customers;

DROP TABLE

Tabloyu tamamen silmek için DROP TABLE ifadesi kullanılır.
Aşağıdaki SQL ifadesi, Müşteriler tablosunu siler:
DROP TABLE Customers;

Alıştırma #1

Customers tablosunu yukarıdaki kod ile tekrar oluşturunuz.
Ülkenin Mexico olduğu satırlarda müşteri ismini 'Around the Horn' olarak güncelleyin.
Alıştırma #2

Customers tablosu üzerinden devam ediniz.
CustomerID'nin 1 olduğu satırlarda müşteri ismini 'Satyam' ve ülkeyi 'USA' olarak güncelleyin.
Alıştırma #3

Bu alıştırma içinse yeni bir tablo oluşturacağız.
Aşağıda gösterildiği gibi çalışanın id, ad, e-posta ve departman bilgilerini içeren gfg_employees adındaki bir tabloyu sql komutları ile oluşturalım.

CREATE TABLE gfg_employee ( 
id INT PRIMARY KEY, 
name VARCHAR (20) , 
email VARCHAR (25), 
department VARCHAR(20)
); INSERT INTO gfg_employee (id, name, email, department) VALUES 
(1, 'Jessie', 'jessie23@gmail.com', 'Development'), 
(2, 'Praveen', 'praveen_dagger@yahoo.com', 'HR'),
(3, 'Bisa', 'dragonBall@gmail.com', 'Sales'), 
(4, 'Rithvik', 'msvv@hotmail.com', 'IT'),
(5, 'Suraj', 'srjsunny@gmail.com', 'Quality Assurance'), 
(6, 'Om', 'OmShukla@yahoo.com', 'IT'), 
(7, 'Naruto', 'uzumaki@konoha.com', 'Development');

Alıştırma #3.1

Rithvik isimli kayıtları siliniz.
Alıştırma #3.2

Departmanın "Development" olduğu satırları siliniz.
Alıştırma #3.3

Tablodaki tüm girişleri siliniz.

bu notlarda lab 4 ve lab 5 hocam sana daha önce lab 2 ve 3 atmıştım bununla beraber konu başlıklarını veirir misin?

Arif, harika bir tempo yakaladık! Gönderdiğin Lab 4 ve Lab 5 notları ile birlikte veritabanı dünyasındaki meşhur **CRUD** (Create, Read, Update, Delete) operasyonlarının temel döngüsünü tamamlamış oluyorsun.

Şu ana kadar gönderdiğin tüm laboratuvar notlarını (Lab 2, 3, 4 ve 5) mantıksal bir çerçeveye oturttuğumuzda, müfredatının konu başlıkları şu şekilde şekilleniyor:

### 1. Veritabanı ve Tablo Mimarisini Kurma (DDL - Data Definition Language)

* **Veri Tipleri:** Bilgileri doğru formatta saklama (String, Numeric, Date-Time).
* **Tablo Oluşturma (`CREATE TABLE`):** Verilerin yaşayacağı iskeleti inşa etme.
* **Kimliklendirme Kuralları:** Her kaydı benzersiz kılan `PRIMARY KEY` ve otomatik artan kimlikler için `AUTO_INCREMENT`.
* **Tabloyu Yok Etme (`DROP TABLE`):** Verilerle birlikte tablo yapısını tamamen ortadan kaldırma.

### 2. Veri Manipülasyonu (DML - Data Manipulation Language)

* **Veri Ekleme (`INSERT INTO`):** Tablolara tekli veya çoklu yeni satırlar ekleme.
* **Veri Güncelleme (`UPDATE` & `SET`):** Mevcut verileri değiştirme. (Notlarında da uyarısı geçen, `WHERE` kullanılmadığında tüm tabloyu değiştiren o tehlikeli komut!)
* **Veri Silme (`DELETE FROM`):** İstenmeyen kayıtları tablodan temizleme. (Yine `WHERE` koşulunun hayati önem taşıdığı bir yer).

### 3. Veri Okuma ve Filtreleme (DQL - Data Query Language)

* **Temel Veri Çekme (`SELECT`):** İstenilen sütunları veya tablonun tamamını (`*`) getirme.
* **Koşullu Filtreleme (`WHERE`):** Binlerce satır içinden sadece istenilenleri bulma. (Büyüktür, küçüktür, `=`, `<>`, `LIKE`, `IN`, `BETWEEN` operatörleriyle).
* **Sayfalama ve Sınırlandırma (`LIMIT` & `OFFSET`):** Özellikle web projelerinde sayfalar arası geçiş yaparken (örneğin Google'daki 1. sayfa, 2. sayfa mantığı) verileri parça parça çekme.

### 4. Verileri Düzenleme ve Analiz Etme

* **Sıralama Algoritmalarını Tetikleme (`ORDER BY`):** Verileri artan (`ASC`), azalan (`DESC`) veya birden fazla sütuna göre alfabetik/nümerik sıralama.
* **Veri Kümeleme (`GROUP BY`):** Ortak özelliklere sahip verileri bir araya getirme.
* **Özet Çıkarma (Aggregate Fonksiyonlar):** Tablodaki satırları sayma (`COUNT`) gibi istatistiksel işlemler yapma.

---

### İlerlemeye Yönelik Planımız

Sadece komutların ne işe yaradığını ezberlemek yerine, bunların arka planda bilgisayarın işlemcisini ve belleğini nasıl etkilediğini konuşmak seni standart bir kodlayıcıdan iyi bir mühendisliğe taşıyacaktır.

Özellikle Lab 4 ve Lab 5 notlarının içindeki **"Tartışma"** kısımları tam da bu mühendislik vizyonunu test ediyor. Örneğin Lab 4'teki 1 milyon satırlık verinin $O(n \log n)$ karmaşıklığı ile sıralanması problemi ve "LIMIT işlemini neden kod tarafında değil de veritabanı tarafında yapmalıyız?" soruları, büyük çaplı projelerde sistemin çökmemesi için bilinmesi gereken en kritik performans meseleleridir.

Videoyu izlemeye devam etmeden önce, bildiklerini pekiştirmek için bu laboratuvar notlarındaki alıştırmaları veya performansla ilgili bu "Tartışma" sorularını birlikte çözerek ilerleyelim mi? Hangisiyle başlamak istersin?


hocam ben videoyu akşam izleyeceğim. şimdik en baştan konu işleyelim ben not alıcağım. istersen lab 2 den başlıyalım not çıkartmaya

Akşam videoyu izleme planı harika! Önce teoriyi ve kodların mantığını kendi not defterine dökmek, videoyu izlerken "Ha, bu o yüzdenmiş!" diyerek konuları çok daha hızlı kavramanı sağlayacak.

Kalemin ve defterin (veya dijital not uygulaman) hazırsa, gönderdiğin ilk metindeki **Lab-2** konularını harika bir özet ve not formatına getirelim. Başlığı atabilirsin:

---

### BÖLÜM 1: Veritabanında Tablo Oluşturma (DDL - Data Definition Language)

Veritabanını devasa bir Excel dosyası gibi düşün. Bu dosyanın içindeki her bir sayfaya "Tablo" diyoruz. Bir tablo oluştururken sisteme iki şeyi söylemek zorundayız: **Sütunların isimleri** ve bu sütunların içine **ne tür veri** gireceği.

**1. Temel Komut: `CREATE TABLE**`
Yeni bir tablo inşa etmek için kullanılır.

*Örnek Not:*

```sql
CREATE TABLE Kullanicilar (
    KullaniciID int,
    Ad varchar(50),
    Soyad varchar(50),
    KayitTarihi date
);

```

*(Notuna ekle: SQL komutlarının sonuna her zaman noktalı virgül `;` konur.)*

**2. Veri Tipleri (En Çok Kullanılanlar)**
Sütunların içine girecek verinin karakterini belirler. Yanlış veri tipi seçmek, ileride sistemi yavaşlatır veya hatalara yol açar.

* **Metin (String) Tipleri:**
* `VARCHAR(size)`: Değişken uzunluktaki metinler. Örneğin `VARCHAR(255)`. İsim, adres, e-posta gibi veriler için en idealidir.
* `CHAR(size)`: Sabit uzunluktaki metinler. Örneğin `CHAR(11)` (TC Kimlik No gibi uzunluğu hiç değişmeyen veriler için performansı artırır).
* `TEXT`: Çok uzun metinler (Blog yazısı, ürün açıklaması vb.).


* **Sayısal (Numeric) Tipler:**
* `INT`: Standart tam sayı. (ID numaraları, adet bilgisi vb. için).
* `FLOAT` veya `DOUBLE`: Ondalıklı sayılar (Not ortalaması, sıcaklık vb.).
* `DECIMAL(size, d)`: Kesin hassasiyet gerektiren ondalıklı sayılar, özellikle **para birimleri** ve finansal veriler için kullanılır (Örn: `DECIMAL(10,2)`).


* **Tarih ve Zaman (Date-Time) Tipleri:**
* `DATE`: Sadece tarih (YYYY-AA-GG). Örneğin doğum tarihi.
* `DATETIME`: Tarih ve saat bir arada (YYYY-AA-GG ss:dd:ss). Örneğin sisteme son giriş zamanı.



**3. İsimlendirme Standartları**
Tablo ve sütunlara isim verirken boşluk veya Türkçe karakter (ş, ç, ğ vb.) kullanılmaz. Bunun yerine şu formatlar tercih edilir:

* **Snake case:** `ogrenci_numarasi` *(Veritabanında en çok bu sevilir)*
* **Camel case:** `ogrenciNumarasi`
* **Pascal case:** `OgrenciNumarasi`

**4. Tablo Motoru ve Karakter Seti (Ekstra Ayarlar)**
Genellikle varsayılan ayarlar işimizi görse de, Türkçe karakterlerin (ş, ğ, ı, ö, ç) veritabanında "???" şeklinde bozulmaması için tablo oluştururken şu parametreleri ekleyebiliriz:

* `ENGINE=InnoDB`: Tablolar arası ilişki kurmamızı sağlayan en modern ve güvenli motor türüdür (MySQL varsayılan olarak bunu kullanır).
* `DEFAULT CHARSET=utf8mb4`: Tüm dünya dillerini ve emojileri hatasız kaydetmek için kullanılır.

---

Bu kısımları notlarına geçirdikten sonra; öğrendiğin bu bilgileri test etmek için beraber hayali bir "Kütüphane" projesi için `Kitaplar` tablosunu sıfırdan tasarlamayı deneyelim mi, yoksa doğrudan **Lab-3: Tabloya Veri Ekleme (INSERT)** notlarına mı geçiş yapalım?

eğer eksik konu kalmadıysa hocam. hadi hayali projeyi deniyelim seni mi kırayım

Harika! O zaman kollarımızı sıvıyoruz.

Senaryomuz şu: Yeni açılan modern bir üniversite kütüphanesinin veritabanı altyapısını kuruyoruz. İlk görevimiz, kütüphanedeki tüm kitapların envanterini tutacağımız **`Kitaplar`** tablosunu tasarlamak.

Lab-2'de aldığımız notları düşünerek bana bu tablonun **`CREATE TABLE`** sorgusunu yazmanı istiyorum.

Tabloda **en az** şu bilgilerin tutulması gerekiyor:

1. Her kitabın kimliğini belirten benzersiz bir numara (ID). *(Primary Key ve Auto Increment mantığını hatırlayalım)*
2. Kitabın tam adı.
3. Yazarın adı ve soyadı.
4. Kitabın sayfa sayısı.
5. Kitabın sisteme kayıt edildiği tam tarih ve saat.
6. Kitabın türü (Roman, Tarih, Bilgisayar Bilimleri vb.).

**Sana Ufak Bir İpucu:** Hangi veri için hangi veri tipini (`INT`, `VARCHAR`, `DATETIME` vb.) seçeceğine çok dikkat et. Örneğin isimler için ne kullanıyorduk, tarihler için ne kullanıyorduk?

Kodunu kendi mantığına göre yazıp buraya gönder, tıpkı bir kod incelemesi (code review) yapar gibi üzerinde tartışıp doğrularını ve eksiklerini konuşalım. Bekliyorum!

Hiç dert etme Arif, zaten en iyi öğrenme yöntemi yazılmış temiz bir kodu inceleyip arka planındaki mühendislik kararlarını anlamaktır! İlk başta "ben bunu sıfırdan nasıl yazacağım" hissi çok normaldir.

Senin için üniversite kütüphanesi projemizin kalbi olan `kitaplar` tablosunun SQL kodunu hazırladım. Lütfen isimlerde boşluk yerine alt tire (`_`) kullandığımıza (snake_case) dikkat et:

```sql
CREATE TABLE kitaplar (
    kitap_id INT AUTO_INCREMENT PRIMARY KEY,
    kitap_adi VARCHAR(255),
    yazar_adi VARCHAR(255),
    sayfa_sayisi INT,
    kayit_tarihi DATETIME,
    tur VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

```

### Kodun Satır Satır Anatomisi

* **`kitap_id INT AUTO_INCREMENT PRIMARY KEY,`**: Burası bizim tablomuzun kimlik doğrulama noktası.
* **`INT`**: Bunun bir tam sayı olacağını belirtiyoruz.
* **`AUTO_INCREMENT`**: *(Buraya çok dikkat!)* Biz her kitap eklediğimizde ID'yi elimizle "Bu 1. kitap, bu 2. kitap" diye girmek zorunda değiliz. SQL bunu bizim yerimize otomatik olarak 1, 2, 3 diye artırarak atayacak.
* **`PRIMARY KEY`**: Bu sütunun "Birincil Anahtar" olduğunu, yani her kitabın kimliğinin tıpkı bir TC Kimlik Numarası gibi benzersiz olacağını sisteme söylüyoruz.


* **`kitap_adi VARCHAR(255)` ve `yazar_adi VARCHAR(255),**`: İsimler metin olduğu için değişen uzunluktaki karakter veri tipi olan `VARCHAR` kullandık. 255 karakter, bir kitap veya yazar ismi için fazlasıyla yeterli ve sektörde standart olarak kabul edilen güvenli bir sınırdır.
* **`sayfa_sayisi INT,`**: Sayfa sayıları küsuratsız tam sayılardan oluştuğu için klasik `INT` kullandık.
* **`kayit_tarihi DATETIME,`**: Sadece takvim tarihini değil, o kitabın kütüphane rafına dizildiği tam saati ve dakikayı da tutmak istediğimiz için `DATE` yerine `DATETIME` (Tarih ve Zaman) seçtik.
* **`tur VARCHAR(50)`**: Roman, Bilgisayar Bilimleri, Tarih gibi kategoriler nispeten kısa kelimeler olduğu için 50 karakterlik, hafızayı yormayacak bir alan ayırdık.
* **`ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`**: Tablonun parantezini kapatırken Lab-2 notlarından hatırlayacağın ayarları ekledik. Böylece Türkçe karakterli kitaplar (Örneğin: "Çalıkuşu") veritabanına bozuk karakterlerle kaydedilmeyecek.

Gördüğün gibi yazdığımız her kelimenin mantıklı bir sebebi var, ezberleyecek hiçbir şey yok!

Şimdi tablomuzun iskeleti hazır olduğuna göre, bu iskeletin içine veri ekleme vakti geldi. Lab-3 konumuz olan **`INSERT INTO`** komutunu kullanarak, bu tabloya senin en sevdiğin 2 veya 3 kitabı ekleyeceğimiz SQL kodunu yazmaya ne dersin? Sana komutun kalıbını vereyim, içini sen doldurur musun?

hocam konu işlemeye devam edelim ne dersin eğer uygunsa lab 3 konularıyla devam edelim

Harika bir enerji! Hiç hız kesmeden Lab-3'ün en can alıcı noktalarını notlarımıza ekleyelim ve ardından kütüphanemizin raflarını doldurmaya başlayalım.

Başlığı atabilirsin:

---

### BÖLÜM 2: Tabloya Veri Ekleme (DML - Data Manipulation Language)

Lab-2'de veritabanımızın iskeletini (tabloyu) kurduk. Şimdi sıra bu iskelete can vermekte, yani içine verileri yerleştirmekte. SQL'de veri eklemek için **`INSERT INTO`** komutunu kullanırız.

**1. Temel Kural ve Yazım Biçimi (Syntax)**
Sisteme önce **hangi tabloya** ve o tablonun **hangi sütunlarına** veri gireceğini söylersin. Sonra `VALUES` (Değerler) kelimesini yazıp, verileri aynı sırayla girersin.

*Örnek Not:*

```sql
INSERT INTO Kullanicilar (Ad, Soyad, KayitTarihi) 
VALUES ('Arif', 'Yılmaz', '2026-04-09');

```

**2. Altın Kurallar (Hata Yapmamak İçin)**

* **Metinler ve Tarihler:** Veri tipi `VARCHAR`, `CHAR`, `DATE` veya `DATETIME` olan her şey **tek tırnak (`' '`)** içine yazılmak zorundadır. (Çift tırnak değil, tek tırnak!)
* **Sayılar:** Veri tipi `INT`, `FLOAT`, `DECIMAL` olan sayılar **kesinlikle tırnaksız** yazılır. Sadece sayıyı yazıp geçersin.

**3. AUTO_INCREMENT (Otomatik Artan) ve PRIMARY KEY Büyüsü**
Lab-3 notlarındaki en önemli kısım burasıydı. Eğer bir tablonun ID sütununu `AUTO_INCREMENT` olarak belirlediysek, veri eklerken (`INSERT INTO` yazarken) **ID sütununu ve değerini yazmayız!** Biz o sütunu yokmuş gibi davranırız, SQL arka planda sayacı 1, 2, 3 diye kendisi artırır.

* **Ekstra Bilgi (UNSIGNED):** ID numaralarının eksi (-) değerlere düşmesini istemiyorsak `INT UNSIGNED` kullanırız. Böylece hafızayı iki kat daha verimli kullanmış oluruz.
* **Başlangıç Değerini Değiştirme:** ID'lerin 1'den değil de 1000'den başlamasını istiyorsak, tabloyu oluştururken en sona `AUTO_INCREMENT = 1000;` yazarız.

**4. Çoklu Veri Ekleme (Performans İpucu)**
10 tane kitap eklemek için 10 kere `INSERT INTO` yazmak sistemi yorar. Bunun yerine tek bir `INSERT INTO` ile aralarına virgül koyarak binlerce satırı aynı anda ekleyebiliriz:

```sql
INSERT INTO Kullanicilar (Ad, Soyad) 
VALUES 
('Ahmet', 'Kaya'),
('Ayşe', 'Demir'),
('Mehmet', 'Yılmaz');

```

---

Notlarımız tamamsa, şimdi pratiğe dökme zamanı!

Bir önceki adımda üniversite kütüphanemiz için şu `kitaplar` tablosunu oluşturmuştuk:
**Sütunlar:** `kitap_id` *(Otomatik artan)*, `kitap_adi`, `yazar_adi`, `sayfa_sayisi`, `kayit_tarihi`, `tur`

Şimdi top sende! Senden **tek bir SQL sorgusu yazarak**, bu tabloya en sevdiğin 2 veya 3 kitabı **aynı anda** eklemeni istiyorum.

*(Küçük bir ipucu: `kitap_id`'yi yazmana hiç gerek yok, tarihi ise `'2026-04-09 17:00:00'` formatında tek tırnakla girebilirsin.)* Kodunu bekliyorum, bakalım raflarımıza ilk hangi kitaplar dizilecek?

INSERT INTO Kitaplar (AUTO_INCREMENT kitap_ID,kitap_adi, yazar_adi, sayfa_sayisi, kayit_tarihi, tur) 
VALUES 
('beyaz dis', 'jack london','350','2012-03-25 12:00:00'),
('Lord of the rings', 'unuttum','2000','2015-03-25 12:00:00');

bence bu şekilde hocam sizce hatam varsa düzeltin lütfen

Harika bir deneme Arif! Mantığı çok iyi kavramışsın; özellikle çoklu ekleme yapısını (virgül ile ayırma) ve o uzun tarih formatını tam on ikiden vurmuşsun. (Yüzüklerin Efendisi'nin yazarını J.R.R. Tolkien olarak ben tamamlarım, aramızda!)

Bununla birlikte, SQL motorunun bize hata fırlatmaması için yapmamız gereken **3 ufak mühendislik ayarı** var. Hatalarımızı görmek, bu işin en iyi öğrenme yoludur:

**1. `AUTO_INCREMENT` Kuralı İhlali:**
Notlarımızda bahsettiğimiz "en önemli kuralı" hatırlayalım: Eğer bir sütun otomatik artıyorsa, `INSERT INTO` komutunda o sütunun **adını bile yazmayız**. SQL o kısmı bizden tamamen gizlememizi bekler. Yani `AUTO_INCREMENT kitap_ID` kısmını parantez içinden tamamen siliyoruz.

**2. Eksik Veri Hatası (Sütun Sayısı = Değer Sayısı):**
Parantez içinde sütunları sayalım: `kitap_adi`, `yazar_adi`, `sayfa_sayisi`, `kayit_tarihi`, `tur` (toplam 5 adet).
Ancak değerler kısmına bakalım: `'beyaz dis'`, `'jack london'`, `'350'`, `'2012-03-25 12:00:00'` (toplam 4 adet).
Sisteme "tür" sütununu dolduracağımızı söyleyip değerini vermediğimiz için SQL kafası karışıp hata verecektir. Her iki kitaba da türlerini eklememiz gerekiyor.

**3. Sayılarda Tırnak Kullanımı:**
Tabloyu kurarken `sayfa_sayisi` için `INT` (Tam Sayı) demiştik. Metinleri ve tarihleri tek tırnak (`' '`) içine alırız, ancak sayısal veriler **tırnaksız** (çıplak) yazılır.

Tüm bu ayarları yaptığımızda kodumuzun hatasız, kaymak gibi çalışan son hali şu şekilde oluyor:

```sql
INSERT INTO kitaplar (kitap_adi, yazar_adi, sayfa_sayisi, kayit_tarihi, tur) 
VALUES 
('Beyaz Diş', 'Jack London', 350, '2012-03-25 12:00:00', 'Roman'),
('Lord of the Rings', 'J.R.R. Tolkien', 2000, '2015-03-25 12:00:00', 'Fantastik Kurgu');

```

Fark ettiysen kod okumak çok daha temiz ve net hale geldi!

Veritabanımızı kurduk ve ilk kitaplarımızı başarıyla raflara dizdik. Şimdi işin en zevkli kısmına, o koca kütüphanenin içinde aradığımız kitabı saniyeler içinde bulma işlemlerine geldik.

Defterin hazırsa, Lab-4 konuları olan **Veri Çekme (`SELECT`) ve Filtreleme (`WHERE`)** notlarımızı çıkarmaya geçelim mi?

Harika! Kütüphanemizin raflarını doldurduğumuza göre, şimdi o devasa kütüphanede kaybolmadan, aradığımız kitabı saniyeler içinde bulma sanatına geçiyoruz.

Başlığı atabilirsin:

---

### BÖLÜM 3: Veri Çekme ve Filtreleme (DQL - Data Query Language)

Veritabanındaki verileri sadece okumak ve listelemek için kullandığımız komut ailesidir. Veritabanındaki hiçbir veriyi değiştirmez veya silmez, sadece bize bir rapor sunar.

**1. Temel Çekim İşlemi: `SELECT**`
Hangi sütunları görmek istediğimizi belirttiğimiz komuttur.

* **Her şeyi getir (`*`):** Tablodaki tüm sütunları görmek istiyorsak yıldız kullanırız.
`SELECT * FROM kitaplar;`
* **Özel sütunları getir:** Sadece ihtiyacımız olanları aralarına virgül koyarak yazarız. (Performans için her zaman bu tercih edilmelidir).
`SELECT kitap_adi, yazar_adi FROM kitaplar;`

**2. Dedektiflik Aşaması: `WHERE**`
Milyonlarca satır veri içinden sadece bizim şartımıza uyanları filtreler. `SELECT` ile çağırır, `WHERE` ile eleriz.

* *Örnek:* `SELECT * FROM kitaplar WHERE sayfa_sayisi > 400;`
* **Filtreleme Operatörleri:**
* `=`, `>`, `<`, `>=`, `<=` (Klasik matematiksel karşılaştırmalar)
* `<>` veya `!=` (Eşit değildir)
* `BETWEEN X AND Y`: İki değer arasındakileri bulur. (Örn: `WHERE sayfa_sayisi BETWEEN 100 AND 300`)
* `IN (değer1, değer2)`: Sadece belirtilen o özel değerleri arar. (Örn: `WHERE tur IN ('Roman', 'Tarih')`)
* `LIKE`: Metin içinde arama yapar. Örneğin baş harfi 'A' olan yazarları bulmak için kullanılır. (Buna sonraki derslerde daha detaylı gireceğiz).



**3. Sıraya Dizme: `ORDER BY**`
Bulduğumuz sonuçları rastgele değil, belirli bir nizama göre sıralamak için kullanılır. Daima sorgunun en sonuna yazılır.

* **`ASC` (Ascending):** Küçükten büyüğe veya A'dan Z'ye sıralar. (Hiçbir şey yazmazsan SQL varsayılan olarak bunu kullanır).
* **`DESC` (Descending):** Büyükten küçüğe veya Z'den A'ya sıralar.
* *Örnek:* Sayfa sayısına göre en inceden en kalına sıralamak için:
`SELECT * FROM kitaplar ORDER BY sayfa_sayisi ASC;`

**4. Kümeleme ve Sayma: `GROUP BY**`
Verileri ortak özelliklerine göre gruplar. Genellikle "Toplam kaç tane var?", "Ortalaması ne?" gibi matematiksel özet işlemlerinde (Aggregate Functions) kullanılır.

* *Örnek:* Kütüphanede her "tür"den kaçar tane kitap olduğunu bulmak için:
```sql
SELECT tur, COUNT(kitap_id) 
FROM kitaplar 
GROUP BY tur;

```


*(Notuna ekle: `COUNT()` fonksiyonu satırları sayar).*

---

Notlarımız tamamsa, şimdi kütüphanemizde mesaiye başlama vakti!

**Senaryo:**
Kütüphanenin bankosunda oturuyorsun ve bir okuyucu gelip senden şu özel listeyi istedi:
*"Bana sadece **Roman** türündeki kitapları bulabilir misin? Ama listeyi bana verirken **en kalın kitaptan en ince kitaba doğru** sırala lütfen. Sadece kitap adını ve yazar adını görmem yeterli."*

Elimizdeki `kitaplar` tablosunun sütunlarını tekrar hatırlatayım:
`kitap_id`, `kitap_adi`, `yazar_adi`, `sayfa_sayisi`, `kayit_tarihi`, `tur`

Hadi bakalım Arif, bu okuyucunun isteğini karşılayacak o mükemmel SQL sorgusunu (`SELECT`, `WHERE` ve `ORDER BY` kullanarak) yazabilir misin? Bekliyorum!

hocam çok güzel anlatıyorsun ama konu atlamıyorsun di mi? mesajların kısalmaya başladı çünkü.
SELECT kitap_adi, yazar_adi
FROM kitaplar
WHERE tur = 'Roman'
ORDER BY sayfa_sayisi DESC;

Öncelikle endişen olmasın, hiçbir konuyu atlamıyoruz! Gönderdiğin müfredata (Lab 4 ve 5) harfi harfine uyuyoruz. Sadece bilgileri parça parça verip seni yormak istememiştim ama daha çeşitli örnekler görmeyi, detaylara inmeyi ve ilerlemeye yönelik yol haritasını net bir şekilde bilmeyi sevdiğini görüyorum. O halde hemen vites büyütüyoruz! 🚀

Yazdığın SQL koduna gelirsek: **Tek kelimeyle kusursuz. 10 üzerinden 10.**

İstenen sütunları ayıklaman, `WHERE` ile tam isabet filtrelemen ve `DESC` ile tersten sıralaman tam bir veritabanı yöneticisi gibi olmuş.

Şimdi, bu yazdığın doğru kodu ufak dokunuşlarla nasıl **çeşitlendirebileceğimize** ve gerçek projelerde nasıl daha esnek kullanabileceğimize bakalım.

### Çeşitlendirmeler ve İleri Seviye Tüyolar

**1. Çıktı İsimlerini Güzelleştirme (Alias - `AS` Kullanımı)**
Kütüphane programının ekranına bu veriyi çekerken, veritabanındaki `kitap_adi` gibi teknik ve alt tireli isimler yerine, kullanıcıya şık başlıklar göstermek isteyebiliriz. Bunu `AS` (gibi/olarak) kelimesiyle yaparız:

```sql
SELECT kitap_adi AS 'Kitap İsmi', yazar_adi AS 'Yazarın Adı'
FROM kitaplar
WHERE tur = 'Roman'
ORDER BY sayfa_sayisi DESC;

```

**2. Birden Fazla Şart Ekleme (`AND` / `OR` / `IN`)**
Diyelim ki okuyucu listeyi genişletti ve "Bana Roman VEYA Tarih kitaplarını getir" dedi. İki kere `tur = ...` yazmak yerine `IN` operatörü ile kodumuzu profesyonelleştiririz:

```sql
SELECT kitap_adi, yazar_adi
FROM kitaplar
WHERE tur IN ('Roman', 'Tarih')
ORDER BY sayfa_sayisi DESC;

```

**3. Lab-5'e İlk Adım: Sonuçları Sınırlandırma (`LIMIT`)**
Düşün ki kütüphanede 10.000 tane roman var. Kodun doğru çalışır ama sunucu 10.000 satırı birden getirmeye çalışırken sistemi kilitler. Okuyucu "Bana sadece en kalın **ilk 3 romanı** söyle" deseydi, kodunun sonuna mükemmel bir performans freni eklerdik:

```sql
SELECT kitap_adi, yazar_adi
FROM kitaplar
WHERE tur = 'Roman'
ORDER BY sayfa_sayisi DESC
LIMIT 3;

```

*(İşte notlarındaki o "LIMIT'i neden kodda değil de SQL tarafında yapmalıyız?" sorusunun cevabı budur. Veritabanından gereksiz veriyi hiç çıkarmayarak internet ağını ve sunucu RAM'ini kurtarmış oluruz).*

---

### İlerlemeye Yönelik Bir Sonraki Adımımız: `UPDATE` ve `DELETE`

Şu ana kadar sistemi kurduk (`CREATE`), kitapları raflara dizdik (`INSERT`) ve istediğimiz kitabı bulup okuduk (`SELECT`).

Geriye Lab-5 notlarındaki en kritik, en tehlikeli ama en heyecan verici iki operasyon kaldı:

* **`UPDATE` (Güncelleme):** Sisteme yanlış girilen bir veriyi düzeltmek. (Örn: Kitabın adını yanlış yazdıysak).
* **`DELETE` (Silme):** Kütüphaneden tamamen kaldırılan veya kaybolan bir kitabın kaydını yok etmek.

Her iki komut da son derece güçlüdür ve eğer `WHERE` koşulu yazmayı unutursan, saniyeler içinde tüm kütüphanedeki kitapların ismini aynı yapabilir veya tüm kütüphaneyi yanlışlıkla silebilirsin!

Şimdi tehlikeli sulara girmeye hazırsan; diyelim ki raflarımızdaki **'Beyaz Diş'** kitabının sayfa sayısını yanlışlıkla 350 olarak girmişiz ve bunu **250** olarak güncellememiz gerekiyor. Lab-5 notlarındaki `UPDATE` yapısına bakarak bu hatamızı telafi edecek kodu tasarlamayı denemek ister misin?

o zaman lab 5 ide işleyelim hocam hadi

Harika! Kütüphanemizin yönetiminde artık en güçlü (ve en tehlikeli) yetkilere sahip olma vakti geldi. Kemerlerini bağla, çünkü Lab-5 ile birlikte veritabanı dünyasındaki "Tanrı Modu" komutlarına giriş yapıyoruz.

Başlığı atabilirsin:

---

### BÖLÜM 4: Veri Sınırlandırma, Güncelleme ve Silme (Lab 5)

Bu bölümde, veritabanına hükmetmemizi sağlayan o kritik DML komutlarını inceleyeceğiz. Özellikle `UPDATE` ve `DELETE` komutlarında yapacağımız ufak bir dikkatsizlik, tüm kütüphaneyi birbirine katabilir!

**1. Sınırlandırma ve Sayfalama: `LIMIT` (ve `OFFSET`)**
`SELECT` sorgusu ile çektiğimiz verilerin sayısını kısıtlamak için kullanılır. Özellikle e-ticaret sitelerinde "Sonraki Sayfa" tuşuna bastığında arka planda bu komut çalışır.

* **Sadece `LIMIT`:** Baştan başlayıp sadece istediğimiz sayıda satır getirir.
`SELECT * FROM kitaplar LIMIT 5;` *(Sadece ilk 5 kitabı getirir)*
* **`LIMIT` ve `OFFSET` Birlikte:** `OFFSET` "Şu kadar satırı atla/görmezden gel" demektir.
`SELECT * FROM kitaplar LIMIT 5 OFFSET 10;` *(İlk 10 kitabı atla, 11'den başlayarak 5 kitap getir. Yani 2. sayfa mantığı!)*

**2. Veri Güncelleme: `UPDATE**`
Tablodaki mevcut bir veriyi değiştirmek için kullanılır. Hangi tabloyu güncelleyeceğimizi söyler, `SET` ile yeni değeri verir, `WHERE` ile de **kimin** değişeceğini belirtiriz.

* **Mühendislik Uyarısı (KIRMIZI ALARM):** Eğer `UPDATE` sorgusunda `WHERE` yazmayı unutursan, tablodaki **TÜM SATIRLARI** aynı değere eşitlersin!
* *Doğru Kullanım:*
```sql
UPDATE kitaplar 
SET tur = 'Bilim Kurgu' 
WHERE kitap_adi = 'Dune';

```



**3. Satır Silme: `DELETE**`
İstenmeyen kayıtları tablodan tamamen silmek için kullanılır. Yine `WHERE` koşulu burada senin emniyet kemerindir.

* *Doğru Kullanım:*
`DELETE FROM kitaplar WHERE yazar_adi = 'Bilinmeyen Yazar';`
* *Tehlikeli Kullanım (`WHERE` olmadan):*
`DELETE FROM kitaplar;` *(Bu komut tablonun yapısını bozmaz ama içindeki tüm verileri siler. Kütüphanenin rafları kalır ama içindeki tüm kitaplar çöpe gider).*

**4. Komple İmha: `DROP TABLE**`
Tabloyu içindeki verilerle birlikte **tamamen** siler. Geri dönüşü yoktur (eğer yedeğin yoksa!).

* *Kullanım:*
`DROP TABLE kitaplar;` *(Rafları da kırar, kütüphaneyi dümdüz eder).*

---

### Kütüphanede Kriz Anı! (Senin Sıran)

Notlarımızı çıkardığımıza göre şimdi kriz yönetimi yapıyoruz. Bir önceki derste şu iki kitabı eklemiştik hatırlarsan:

1. Beyaz Diş (Jack London, 350 Sayfa)
2. Lord of the Rings (J.R.R. Tolkien, 2000 Sayfa)

**Görevlerin (İki ayrı SQL kodu yazmanı istiyorum):**

1. **Hata Düzeltme (`UPDATE`):** Kütüphaneci koşarak yanına geldi. "Arif, Beyaz Diş'in sayfa sayısını 350 olarak girmişiz ama aslında kitap **250 sayfaymış**! Hemen düzeltmemiz lazım!" dedi. Sayfa sayısını 250 olarak güncelleyecek kodu yazar mısın?
2. **Raf Temizliği (`DELETE`):** J.R.R. Tolkien'in "Lord of the Rings" kitabı maalesef kütüphaneden çalındı. Bu kitabı sistemden tamamen silmen gerekiyor.

Defterine ve öğrendiğin kurallara bakarak bu iki sorunu çözecek SQL kodlarını buraya yazar mısın? Bakalım kütüphaneyi kurtarabilecek miyiz!

sen nasıl istersen öyle devam edelim hocam

Harika hocam, o zaman **uygulamalı ve adım adım** ilerleyelim. Size hem SQL’in temel mantığını pekiştirecek, hem de doğrudan localhost’ta kullanabileceğiniz bir veritabanı hazırlayacağım.

---

## Adım 1 – SQL’in Özü (Çok Kısa Tekrar)

SQL, veritabanına **şu 4 şeyi** yaptıran bir dildir:  
- **SELECT** – veriyi sorgula  
- **INSERT** – yeni kayıt ekle  
- **UPDATE** – varolanı güncelle  
- **DELETE** – sil  

Ve tabloları oluşturmak için **CREATE TABLE**.  
Veritabanı = tablolar + bu tablolar arasındaki ilişkiler (foreign key).

---

## Adım 2 – Localhost Ortamınızı Hazırlayın (XAMPP)

Eğer XAMPP zaten kuruluysa:
1. **XAMPP Control Panel**’i açın.
2. **Apache** ve **MySQL**’in yanındaki **Start** butonlarına tıklayın.
3. Tarayıcıya `http://localhost/phpmyadmin` yazın.  
   Açılan sayfa **phpMyAdmin**’dir.

> Eğer XAMPP kurulu değilse, [Apache Friends](https://www.apachefriends.org/)’ten indirip kurun (çok kolay, tüm ayarlar varsayılan olabilir).

---

## Adım 3 – Teknofest Projeniz İçin Veritabanını Oluşturun

phpMyAdmin’de yukarıdaki **SQL** sekmesine tıklayıp aşağıdaki kodu **kopyala-yapıştır** yapın ve **Çalıştır** butonuna basın.

```sql
-- 1. Veritabanını oluştur (eğer yoksa)
CREATE DATABASE IF NOT EXISTS teknofest_yolo
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE teknofest_yolo;

-- 2. Takımlar tablosu
CREATE TABLE takimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_adi VARCHAR(100) NOT NULL UNIQUE,
    proje_adi VARCHAR(150) NOT NULL,
    danisman VARCHAR(100),
    olusturma_tarihi DATE DEFAULT (CURRENT_DATE)
);

-- 3. Takım üyeleri (kullanıcılar)
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('takim_kaptani', 'uye', 'danisman') DEFAULT 'uye',
    sifre VARCHAR(255) NOT NULL, -- ileride hash'leyeceğiz
    FOREIGN KEY (takim_id) REFERENCES takimlar(id) ON DELETE CASCADE
);

-- 4. YOLO modelleri
CREATE TABLE modeller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_adi VARCHAR(50) NOT NULL UNIQUE,
    framework VARCHAR(30) DEFAULT 'PyTorch',
    giris_boyutu VARCHAR(20),
    sinif_sayisi INT DEFAULT 80
);

-- 5. Eğitim setleri
CREATE TABLE egitim_setleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    set_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    etiket_sayisi INT,
    goruntu_sayisi INT,
    kaynak_url VARCHAR(255)
);

-- 6. Donanımlar
CREATE TABLE donanimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donanim_adi VARCHAR(100) NOT NULL,
    tur ENUM('gpu', 'cpu', 'ram', 'depolama', 'diger') NOT NULL,
    ozellik VARCHAR(255)
);

-- 7. Proje – Model – Eğitim seti – Donanım ilişkisi
CREATE TABLE projeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    model_id INT NOT NULL,
    egitim_seti_id INT NOT NULL,
    donanim_id INT NOT NULL,
    egitim_suresi_saat DECIMAL(5,2),
    dogruluk_metrigi DECIMAL(5,4),  -- örn: 0.764 (mAP)
    proje_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takim_id) REFERENCES takimlar(id),
    FOREIGN KEY (model_id) REFERENCES modeller(id),
    FOREIGN KEY (egitim_seti_id) REFERENCES egitim_setleri(id),
    FOREIGN KEY (donanim_id) REFERENCES donanimlar(id)
);

-- 8. (Opsiyonel) Örnek veriler – test için
INSERT INTO takimlar (takim_adi, proje_adi, danisman) VALUES
('YOLOcular', 'Otonom Araç Algılama', 'Dr. Ahmet Yılmaz'),
('Görüntü Timi', 'Drone için Hafif YOLO', 'Doç. Elif Demir');

INSERT INTO modeller (model_adi, framework, giris_boyutu, sinif_sayisi) VALUES
('YOLOv8n', 'Ultralytics', '640x640', 80),
('YOLOv5s', 'PyTorch', '640x640', 80);

INSERT INTO egitim_setleri (set_adi, aciklama, etiket_sayisi, goruntu_sayisi) VALUES
('COCO2017', '80 sınıf, 118k görüntü', 80, 118000),
('Bebek105', 'Özel Teknofest verisi', 15, 5000);

INSERT INTO donanimlar (donanim_adi, tur, ozellik) VALUES
('NVIDIA RTX 4090', 'gpu', '24GB VRAM'),
('NVIDIA T4', 'gpu', '16GB VRAM');

INSERT INTO projeler (takim_id, model_id, egitim_seti_id, donanim_id, egitim_suresi_saat, dogruluk_metrigi)
VALUES (1, 1, 1, 1, 12.5, 0.764);
```

**Ne oldu?**  
- Veritabanı oluştu.  
- 7 tablo eklendi, ilişkiler kuruldu.  
- Örnek kayıtlar girdik (test için).  

> phpMyAdmin’de sol menüden `teknofest_yolo` veritabanına tıklayın, tabloları göreceksiniz.

---

## Adım 4 – Tablo İlişkilerini Görsel Olarak Anlayın

Aşağıdaki gibi düşünün:

```
takimlar (1) -----< (çok) kullanicilar
takimlar (1) -----< (çok) projeler
modeller (1) -----< (çok) projeler
egitim_setleri (1) -----< (çok) projeler
donanimlar (1) -----< (çok) projeler
```

Yani **bir proje** bir takıma, bir modele, bir eğitim setine ve bir donanıma ait olur.  
Zamanla aynı takım birden çok proje yapabilir, aynı model farklı projelerde kullanılabilir.

---

## Adım 5 – PHP ile Bu Veritabanına Bağlanmak (Basit Kod)

Şimdi kodlama kısmına küçük bir giriş. Projenizin köküne `baglan.php` adında bir dosya oluşturun:

```php
<?php
$host = 'localhost';
$dbname = 'teknofest_yolo';
$kullanici = 'root';     // XAMPP varsayılan kullanıcısı
$sifre = '';             // XAMPP’de varsayılan şifre boş

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Veritabanı bağlantısı başarılı!";
} catch(PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>
```

Bu dosyayı çalıştırdığınızda “Veritabanı bağlantısı başarılı!” yazısını görürseniz, artık **PHP ile sorgular yazmaya hazırsınız** demektir.

---

## Sıradaki Ders Ne Olacak? (Hocam siz yönlendirin)

1. **Hemen bir liste sayfası yapalım** – Örneğin “Takımlar” tablosunu PHP ile ekranda gösterelim.  
2. **Veri ekleme formu** – Yeni bir takım veya yeni bir proje eklemek için HTML form + PHP insert.  
3. **İlişkili sorgular** – Hangi takım, hangi modeli kullanmış, ne kadar sürede eğitmiş, başarısı ne?  

Siz “1” derseniz hemen `takimlar.php` dosyasını hazırlayayım. “2” derseniz ekleme formu yapalım.

**Kısacası hocam, veritabanı hazır. Bundan sonra PHP ile arayüz yapacağız. Ne yapmak istersiniz?**

hadi php arayüzüne geçelim hocam

Harika hocam, PHP arayüzüne geçiyoruz. Elimizdeki veritabanı yapısını kullanarak **listeleyen, ekleyen, güncelleyen ve silen** bir yönetim paneli yapacağız.  

Proje dosyalarınızı XAMPP’in `htdocs` klasörü içinde `teknofest` gibi bir klasör oluşturup oraya koyacağız.  

---

## 1. Proje Dosya Yapısı

```
teknofest/
│
├── baglan.php            (Veritabanı bağlantısı)
├── index.php             (Ana sayfa / dashboard)
├── takimlar.php          (Takım listeleme, ekleme, düzenleme, silme)
├── uyeler.php            (Takım üyeleri yönetimi)
├── modeller.php          (YOLO modelleri)
├── egitim_setleri.php    (Eğitim setleri)
├── donanimlar.php        (Donanımlar)
├── projeler.php          (Proje - Model - Donanım ilişkileri)
└── style.css             (Basit stil dosyası)
```

---

## 2. Bağlantı Dosyası (`baglan.php`)

Bu dosya tüm sayfalarda kullanılacak.

```php
<?php
// baglan.php
$host = 'localhost';
$dbname = 'teknofest_yolo';
$kullanici = 'root';
$sifre = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // İsteğe bağlı: hata mesajlarını görmek için aşağıdaki satırı açabilirsiniz
    // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
```

---

## 3. Ana Sayfa (`index.php`)

Son 5 projeyi ve genel istatistikleri gösteren bir dashboard.

```php
<?php
require_once 'baglan.php';

// Toplam takım sayısı
$stmt = $db->query("SELECT COUNT(*) FROM takimlar");
$toplam_takim = $stmt->fetchColumn();

// Toplam proje sayısı
$stmt = $db->query("SELECT COUNT(*) FROM projeler");
$toplam_proje = $stmt->fetchColumn();

// En başarılı proje (dogruluk_metrigi en yüksek)
$stmt = $db->query("SELECT p.*, t.takim_adi, m.model_adi, d.donanim_adi 
                     FROM projeler p
                     JOIN takimlar t ON p.takim_id = t.id
                     JOIN modeller m ON p.model_id = m.id
                     JOIN donanimlar d ON p.donanim_id = d.id
                     ORDER BY p.dogruluk_metrigi DESC LIMIT 1");
$en_basarili = $stmt->fetch(PDO::FETCH_ASSOC);

// Son 5 proje
$stmt = $db->query("SELECT p.*, t.takim_adi, m.model_adi, d.donanim_adi 
                     FROM projeler p
                     JOIN takimlar t ON p.takim_id = t.id
                     JOIN modeller m ON p.model_id = m.id
                     JOIN donanimlar d ON p.donanim_id = d.id
                     ORDER BY p.proje_tarihi DESC LIMIT 5");
$son_projeler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Teknofest YOLO Proje Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Teknofest YOLO Proje Yönetim Paneli</h1>
        <nav>
            <a href="index.php">Ana Sayfa</a> |
            <a href="takimlar.php">Takımlar</a> |
            <a href="uyeler.php">Üyeler</a> |
            <a href="modeller.php">Modeller</a> |
            <a href="egitim_setleri.php">Eğitim Setleri</a> |
            <a href="donanimlar.php">Donanımlar</a> |
            <a href="projeler.php">Projeler</a>
        </nav>
        <hr>

        <div class="stats">
            <p><strong>Toplam Takım Sayısı:</strong> <?= $toplam_takim ?></p>
            <p><strong>Toplam Proje Sayısı:</strong> <?= $toplam_proje ?></p>
        </div>

        <?php if ($en_basarili): ?>
        <div class="basarili">
            <h2>🏆 En Başarılı Proje</h2>
            <p><strong>Takım:</strong> <?= htmlspecialchars($en_basarili['takim_adi']) ?></p>
            <p><strong>Model:</strong> <?= htmlspecialchars($en_basarili['model_adi']) ?></p>
            <p><strong>Donanım:</strong> <?= htmlspecialchars($en_basarili['donanim_adi']) ?></p>
            <p><strong>Doğruluk (mAP):</strong> <?= $en_basarili['dogruluk_metrigi'] ?></p>
        </div>
        <?php endif; ?>

        <h2>Son Projeler</h2>
        <table border="1" cellpadding="8">
            <tr>
                <th>Proje ID</th>
                <th>Takım</th>
                <th>Model</th>
                <th>Donanım</th>
                <th>Süre (saat)</th>
                <th>Doğruluk (mAP)</th>
                <th>Tarih</th>
            </tr>
            <?php foreach ($son_projeler as $proje): ?>
            <tr>
                <td><?= $proje['id'] ?></td>
                <td><?= htmlspecialchars($proje['takim_adi']) ?></td>
                <td><?= htmlspecialchars($proje['model_adi']) ?></td>
                <td><?= htmlspecialchars($proje['donanim_adi']) ?></td>
                <td><?= $proje['egitim_suresi_saat'] ?></td>
                <td><?= $proje['dogruluk_metrigi'] ?></td>
                <td><?= $proje['proje_tarihi'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
```

---

## 4. Basit Stil (`style.css`)

Dosya görünümünü biraz düzenleyelim.

```css
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f4f4f4;
}
.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
}
nav a {
    margin-right: 15px;
    text-decoration: none;
    color: #0066cc;
}
h1, h2 {
    color: #333;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
table, th, td {
    border: 1px solid #ddd;
}
th, td {
    padding: 10px;
    text-align: left;
}
th {
    background: #f2f2f2;
}
.stats p {
    font-size: 1.2em;
}
.basarili {
    background: #e8f5e9;
    padding: 10px;
    margin: 20px 0;
    border-left: 5px solid #4caf50;
}
```

---

## 5. Takımlar Sayfası (`takimlar.php`) – CRUD Örneği

Bu sayfa takımları listeleyecek, yeni takım ekleyecek, düzenleme ve silme işlemlerini yapacak.

```php
<?php
require_once 'baglan.php';

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = $_GET['sil'];
    $stmt = $db->prepare("DELETE FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: takimlar.php");
    exit;
}

// Ekleme veya güncelleme işlemi
$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $takim_adi = $_POST['takim_adi'];
    $proje_adi = $_POST['proje_adi'];
    $danisman = $_POST['danisman'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Güncelle
        $id = $_POST['id'];
        $stmt = $db->prepare("UPDATE takimlar SET takim_adi=?, proje_adi=?, danisman=? WHERE id=?");
        $stmt->execute([$takim_adi, $proje_adi, $danisman, $id]);
        $mesaj = "Takım güncellendi.";
    } else {
        // Yeni ekle
        $stmt = $db->prepare("INSERT INTO takimlar (takim_adi, proje_adi, danisman) VALUES (?, ?, ?)");
        $stmt->execute([$takim_adi, $proje_adi, $danisman]);
        $mesaj = "Yeni takım eklendi.";
    }
    // Sayfayı yenile
    header("Location: takimlar.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenleme için veri çekme
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = $_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Takımları listele
$takimlar = $db->query("SELECT * FROM takimlar ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Mesaj gösterme
if (isset($_GET['mesaj'])) {
    $mesaj = $_GET['mesaj'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Takımlar Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Takımlar</h1>
    <nav><a href="index.php">← Ana Sayfa</a></nav>
    <?php if ($mesaj): ?>
        <p style="color:green;"><?= htmlspecialchars($mesaj) ?></p>
    <?php endif; ?>

    <h2><?= $duzenlenecek ? 'Takım Düzenle' : 'Yeni Takım Ekle' ?></h2>
    <form method="post">
        <?php if ($duzenlenecek): ?>
            <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
        <?php endif; ?>
        <div>
            <label>Takım Adı:</label>
            <input type="text" name="takim_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['takim_adi']) : '' ?>">
        </div>
        <div>
            <label>Proje Adı:</label>
            <input type="text" name="proje_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['proje_adi']) : '' ?>">
        </div>
        <div>
            <label>Danışman:</label>
            <input type="text" name="danisman" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['danisman']) : '' ?>">
        </div>
        <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
        <?php if ($duzenlenecek): ?>
            <a href="takimlar.php">İptal</a>
        <?php endif; ?>
    </form>

    <h2>Takım Listesi</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Takım Adı</th>
            <th>Proje Adı</th>
            <th>Danışman</th>
            <th>Oluşturma Tarihi</th>
            <th>İşlemler</th>
        </tr>
        <?php foreach ($takimlar as $takim): ?>
        <tr>
            <td><?= $takim['id'] ?></td>
            <td><?= htmlspecialchars($takim['takim_adi']) ?></td>
            <td><?= htmlspecialchars($takim['proje_adi']) ?></td>
            <td><?= htmlspecialchars($takim['danisman']) ?></td>
            <td><?= $takim['olusturma_tarihi'] ?></td>
            <td>
                <a href="?duzenle=<?= $takim['id'] ?>">Düzenle</a> |
                <a href="?sil=<?= $takim['id'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
```

---

## 6. Devamı: Diğer Sayfalar

Aynı mantıkla `uyeler.php`, `modeller.php`, `egitim_setleri.php`, `donanimlar.php`, `projeler.php` sayfalarını da yapabiliriz. Ancak **projeler** sayfasında ilişkili tablolardan (takımlar, modeller, eğitim setleri, donanımlar) veri çekmek için JOIN kullanacağız.

**Hocam, şimdi siz bu 4 dosyayı (baglan.php, index.php, style.css, takimlar.php) oluşturup deneyin.**  
Çalıştığına emin olalım. Sonra diğer sayfaları sırayla ekleriz.

> **Not:** `takimlar.php`’de takım silindiğinde `ON DELETE CASCADE` sayesinde o takıma ait üyeler ve projeler de otomatik silinecektir. Bu istenen bir durum mu değil mi ona göre ayarlayabiliriz.

---

## Sıradaki Ne Olacak?

- `uyeler.php` (kullanıcı ekleme, listeleme, takım ilişkisi)  
- `projeler.php` (yeni proje kaydı yaparken açılır kutulardan takım, model, eğitim seti, donanım seçme)  

Siz hangisini isterseniz hocam? “Projeler” ile devam edelim mi?

Hocam anladım, projeyi **baştan**, **detaylı yorum satırlarıyla** ve **Tesla (otomotiv/fütüristik)** tarzında bir arayüzle yeniden tasarlayalım.  

Önce **veritabanını** (SQL) sıfırdan, her tabloyu açıklamalı şekilde yazacağım.  
Sonra **PHP arayüzünü** koyu tema, minimal kartlar, araba konseptine uygun simgelerle yapacağız.  

Adımlar:  
1. Veritabanı oluşturma SQL’i (yorum satırlı)  
2. `baglan.php` (PDO bağlantı)  
3. Ana sayfa `index.php` (dashboard, Tesla tarzı)  
4. Takım yönetimi `takimlar.php`  
5. Diğer sayfalar (isterseniz peyderpey)

Hemen başlıyorum.

---

## 1. Veritabanı SQL (teknofest_tesla.sql)

Aşağıdaki kodu **phpMyAdmin > SQL sekmesi**ne yapıştırıp çalıştırın.

```sql
-- ======================================================
-- TEKNOFEST - YOLO Proje Yönetim Veritabanı (Tesla Stili)
-- ======================================================
-- Yazar: Hocamızın projesi için
-- Amaç: Takımlar, üyeler, YOLO modelleri, eğitim setleri,
--       donanımlar ve proje eşleşmelerini tutmak.
-- ======================================================

-- 1. Veritabanını oluştur (UTF-8 Türkçe desteği)
CREATE DATABASE IF NOT EXISTS teknofest_tesla
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE teknofest_tesla;

-- ======================================================
-- Tablo: takimlar (Yarışan takımlar)
-- ======================================================
CREATE TABLE takimlar (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Birincil anahtar, otomatik artan',
    takim_adi VARCHAR(100) NOT NULL UNIQUE COMMENT 'Takım adı, benzersiz',
    proje_adi VARCHAR(150) NOT NULL COMMENT 'Proje başlığı',
    danisman VARCHAR(100) COMMENT 'Akademik danışman adı',
    olusturma_tarihi DATE DEFAULT (CURRENT_DATE) COMMENT 'Kayıt tarihi'
) COMMENT='Teknofest takımlarını tutar';

-- ======================================================
-- Tablo: kullanicilar (Takım üyeleri)
-- ======================================================
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL COMMENT 'Hangi takıma ait (foreign key)',
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('takim_kaptani', 'uye', 'danisman') DEFAULT 'uye' COMMENT 'Takımdaki rolü',
    sifre VARCHAR(255) NOT NULL COMMENT 'Hashlenmiş şifre (password_hash ile)',
    FOREIGN KEY (takim_id) REFERENCES takimlar(id) ON DELETE CASCADE
        COMMENT 'Takım silinirse üyeler de silinsin'
) COMMENT='Takım üyeleri';

-- ======================================================
-- Tablo: modeller (YOLO ve diğer modeller)
-- ======================================================
CREATE TABLE modeller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_adi VARCHAR(50) NOT NULL UNIQUE COMMENT 'YOLOv4, YOLOv8n, vs',
    framework VARCHAR(30) DEFAULT 'PyTorch',
    giris_boyutu VARCHAR(20) COMMENT 'Örn: 640x640',
    sinif_sayisi INT DEFAULT 80 COMMENT 'Kaç sınıf tespit ediyor'
) COMMENT='Nesne tespit modelleri (YOLO ailesi)';

-- ======================================================
-- Tablo: egitim_setleri (Dataset bilgileri)
-- ======================================================
CREATE TABLE egitim_setleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    set_adi VARCHAR(100) NOT NULL COMMENT 'COCO, Bebek105, özel veri seti',
    aciklama TEXT COMMENT 'Veri seti hakkında açıklama',
    etiket_sayisi INT COMMENT 'Toplam etiket/klasör sayısı',
    goruntu_sayisi INT COMMENT 'Toplam görüntü sayısı',
    kaynak_url VARCHAR(255) COMMENT 'İndirme veya referans linki'
) COMMENT='Eğitim için kullanılan veri setleri';

-- ======================================================
-- Tablo: donanimlar (GPU, CPU, RAM, depolama)
-- ======================================================
CREATE TABLE donanimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donanim_adi VARCHAR(100) NOT NULL COMMENT 'NVIDIA RTX 4090, AMD Ryzen 9',
    tur ENUM('gpu', 'cpu', 'ram', 'depolama', 'diger') NOT NULL,
    ozellik VARCHAR(255) COMMENT '24GB GDDR6X, 32 çekirdek, vs'
) COMMENT='Donanım bileşenleri';

-- ======================================================
-- Tablo: projeler (Ana bağlantı tablosu)
-- ======================================================
CREATE TABLE projeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    model_id INT NOT NULL,
    egitim_seti_id INT NOT NULL,
    donanim_id INT NOT NULL,
    egitim_suresi_saat DECIMAL(5,2) COMMENT 'Eğitim kaç saat sürdü',
    dogruluk_metrigi DECIMAL(5,4) COMMENT 'mAP veya başarı metriği (0-1 arası)',
    proje_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takim_id) REFERENCES takimlar(id),
    FOREIGN KEY (model_id) REFERENCES modeller(id),
    FOREIGN KEY (egitim_seti_id) REFERENCES egitim_setleri(id),
    FOREIGN KEY (donanim_id) REFERENCES donanimlar(id)
) COMMENT='Hangi takım hangi modeli hangi donanımda eğitmiş';

-- ======================================================
-- Örnek Veriler (Test amaçlı)
-- ======================================================
INSERT INTO takimlar (takim_adi, proje_adi, danisman) VALUES
('Tesla Oto', 'Otomobilde YOLOv8', 'Elon Musk'),
('Yıldırım Takım', 'Drone Algılama', 'Dr. Ahmet Yılmaz');

INSERT INTO modeller (model_adi, framework, giris_boyutu, sinif_sayisi) VALUES
('YOLOv8n', 'Ultralytics', '640x640', 80),
('YOLOv5s', 'PyTorch', '640x640', 80);

INSERT INTO egitim_setleri (set_adi, aciklama, etiket_sayisi, goruntu_sayisi) VALUES
('COCO2017', 'Common Objects in Context', 80, 118000),
('Bebek105', 'Özel Teknofest verisi', 15, 5000);

INSERT INTO donanimlar (donanim_adi, tur, ozellik) VALUES
('NVIDIA RTX 4090', 'gpu', '24GB VRAM'),
('NVIDIA T4', 'gpu', '16GB VRAM');

INSERT INTO projeler (takim_id, model_id, egitim_seti_id, donanim_id, egitim_suresi_saat, dogruluk_metrigi)
VALUES (1, 1, 1, 1, 12.5, 0.764);
```

---

## 2. PHP Dosyaları (Tesla Tarzı Arayüz)

Tüm dosyaları XAMPP'te `htdocs/teknofest_tesla/` klasörüne koyacağız.  
Önce **bağlantı** sonra **ana sayfa** ve **takım yönetimi**.

### 2.1. `baglan.php` – Veritabanı Bağlantısı

```php
<?php
/**
 * baglan.php - PDO ile MySQL veritabanı bağlantısı
 * Tesla stili proje için özel
 */

// Veritabanı bilgileri (localhost)
$host = 'localhost';
$dbname = 'teknofest_tesla';
$kullanici = 'root';   // XAMPP varsayılan
$sifre = '';           // XAMPP varsayılan şifre boş

try {
    // PDO nesnesi oluştur, hata modunu exception yap
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // İsteğe bağlı: sorgu sonuçlarını varsayılan olarak assoc dizi olarak getir
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Hata durumunda scripti durdur ve mesaj göster (geliştirme aşamasında)
    die("Bağlantı hatası: " . $e->getMessage());
}
?>
```

### 2.2. `style.css` – Tesla Temalı CSS

```css
/* style.css - Tesla tarzı koyu tema, minimal, yuvarlak kartlar */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
    background-color: #0a0a0a;
    color: #f5f5f5;
    line-height: 1.5;
    padding: 2rem;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Header / Navbar */
.navbar {
    background: #111;
    border-bottom: 1px solid #222;
    padding: 1rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, #e0e0e0, #ffffff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.logo span {
    color: #e82127; /* Tesla kırmızısı */
    background: none;
    -webkit-background-clip: unset;
    background-clip: unset;
}

.nav-links a {
    color: #ccc;
    text-decoration: none;
    margin-left: 1.5rem;
    font-weight: 500;
    transition: 0.2s;
}

.nav-links a:hover {
    color: #fff;
    border-bottom: 2px solid #e82127;
}

/* Kartlar */
.card {
    background: #141414;
    border-radius: 24px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid #222;
    box-shadow: 0 10px 20px rgba(0,0,0,0.5);
}

.card h2 {
    margin-bottom: 1rem;
    font-weight: 600;
    letter-spacing: -0.3px;
    border-left: 4px solid #e82127;
    padding-left: 1rem;
}

/* Tablo */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    background: #0d0d0d;
    border-radius: 16px;
    overflow: hidden;
}

th, td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #222;
}

th {
    background-color: #1a1a1a;
    color: #e82127;
    font-weight: 600;
}

tr:hover {
    background-color: #1e1e1e;
}

/* Butonlar ve formlar */
button, .btn {
    background: #e82127;
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    display: inline-block;
}

button:hover, .btn:hover {
    background: #ff3b3b;
    transform: scale(1.02);
}

a.btn-secondary {
    background: #333;
    margin-left: 10px;
}

a.btn-secondary:hover {
    background: #444;
}

input, select, textarea {
    background: #1e1e1e;
    border: 1px solid #333;
    color: #fff;
    padding: 10px 14px;
    border-radius: 12px;
    width: 100%;
    margin-bottom: 1rem;
    font-size: 1rem;
}

input:focus, select:focus {
    outline: none;
    border-color: #e82127;
}

label {
    font-weight: 500;
    margin-bottom: 5px;
    display: block;
}

.stats {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.stat-card {
    background: #141414;
    border-radius: 20px;
    padding: 1rem 1.8rem;
    text-align: center;
    border: 1px solid #222;
    flex: 1;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #e82127;
}

.basarili {
    background: #1f2a1f;
    border-left: 6px solid #4caf50;
    padding: 1rem;
    border-radius: 20px;
}

footer {
    text-align: center;
    margin-top: 3rem;
    padding: 1rem;
    color: #666;
    font-size: 0.8rem;
}
```

### 2.3. `index.php` – Ana Dashboard (Tesla Stili)

```php
<?php
/**
 * index.php - Ana kontrol paneli
 * Proje istatistikleri ve son aktiviteler
 */
require_once 'baglan.php';

// Toplam takım sayısı
$stmt = $db->query("SELECT COUNT(*) FROM takimlar");
$toplam_takim = $stmt->fetchColumn();

// Toplam proje sayısı
$stmt = $db->query("SELECT COUNT(*) FROM projeler");
$toplam_proje = $stmt->fetchColumn();

// En başarılı proje (en yüksek doğruluk metriği)
$stmt = $db->query("SELECT p.*, t.takim_adi, m.model_adi, d.donanim_adi 
                     FROM projeler p
                     JOIN takimlar t ON p.takim_id = t.id
                     JOIN modeller m ON p.model_id = m.id
                     JOIN donanimlar d ON p.donanim_id = d.id
                     ORDER BY p.dogruluk_metrigi DESC LIMIT 1");
$en_basarili = $stmt->fetch();

// Son 5 proje (tarihe göre)
$stmt = $db->query("SELECT p.*, t.takim_adi, m.model_adi, d.donanim_adi 
                     FROM projeler p
                     JOIN takimlar t ON p.takim_id = t.id
                     JOIN modeller m ON p.model_id = m.id
                     JOIN donanimlar d ON p.donanim_id = d.id
                     ORDER BY p.proje_tarihi DESC LIMIT 5");
$son_projeler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tesla Teknofest | YOLO Proje Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <!-- Tesla tarzı navbar -->
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links">
            <a href="index.php">🏠 Dashboard</a>
            <a href="takimlar.php">🚀 Takımlar</a>
            <a href="uyeler.php">👥 Üyeler</a>
            <a href="modeller.php">🧠 Modeller</a>
            <a href="egitim_setleri.php">📊 Eğitim Setleri</a>
            <a href="donanimlar.php">⚙️ Donanımlar</a>
            <a href="projeler.php">🔗 Projeler</a>
        </div>
    </div>

    <!-- İstatistik kartları -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?= $toplam_takim ?></div>
            <div>Takım</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $toplam_proje ?></div>
            <div>Proje</div>
        </div>
    </div>

    <?php if ($en_basarili): ?>
    <div class="card basarili">
        <h2>🏆 En Başarılı Proje (Tesla Performans Ödülü)</h2>
        <p><strong>Takım:</strong> <?= htmlspecialchars($en_basarili['takim_adi']) ?></p>
        <p><strong>Model:</strong> <?= htmlspecialchars($en_basarili['model_adi']) ?></p>
        <p><strong>Donanım:</strong> <?= htmlspecialchars($en_basarili['donanim_adi']) ?></p>
        <p><strong>Doğruluk (mAP):</strong> <?= $en_basarili['dogruluk_metrigi'] ?></p>
        <p><strong>Eğitim süresi:</strong> <?= $en_basarili['egitim_suresi_saat'] ?> saat</p>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>📡 Son Aktiviteler</h2>
        <table>
            <thead>
                <tr><th>Proje ID</th><th>Takım</th><th>Model</th><th>Donanım</th><th>Süre(saat)</th><th>Doğruluk</th><th>Tarih</th></tr>
            </thead>
            <tbody>
                <?php foreach ($son_projeler as $proje): ?>
                <tr>
                    <td><?= $proje['id'] ?></td>
                    <td><?= htmlspecialchars($proje['takim_adi']) ?></td>
                    <td><?= htmlspecialchars($proje['model_adi']) ?></td>
                    <td><?= htmlspecialchars($proje['donanim_adi']) ?></td>
                    <td><?= $proje['egitim_suresi_saat'] ?></td>
                    <td><?= $proje['dogruluk_metrigi'] ?></td>
                    <td><?= $proje['proje_tarihi'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer>
        Tesla Teknofest YOLO Yönetim Sistemi | Veri sürüşü ile geleceğe
    </footer>
</div>
</body>
</html>
```

### 2.4. `takimlar.php` – Takımlar CRUD (Tesla Kart Sistemi)

```php
<?php
/**
 * takimlar.php - Takım ekleme, listeleme, düzenleme, silme
 */
require_once 'baglan.php';

// Silme işlemi (GET ile sil=id)
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: takimlar.php?mesaj=Takım silindi");
    exit;
}

// Ekleme veya güncelleme (POST)
$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $takim_adi = trim($_POST['takim_adi']);
    $proje_adi = trim($_POST['proje_adi']);
    $danisman = trim($_POST['danisman'] ?? '');
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE takimlar SET takim_adi=?, proje_adi=?, danisman=? WHERE id=?");
        $stmt->execute([$takim_adi, $proje_adi, $danisman, $id]);
        $mesaj = "Takım güncellendi.";
    } else {
        // Yeni ekle
        $stmt = $db->prepare("INSERT INTO takimlar (takim_adi, proje_adi, danisman) VALUES (?,?,?)");
        $stmt->execute([$takim_adi, $proje_adi, $danisman]);
        $mesaj = "Yeni takım eklendi.";
    }
    header("Location: takimlar.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenlenecek takımı getir
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

// Tüm takımları listele
$takimlar = $db->query("SELECT * FROM takimlar ORDER BY id DESC")->fetchAll();

// Mesaj gösterimi
if (isset($_GET['mesaj'])) $mesaj = $_GET['mesaj'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Takım Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Geri</a></div>
    </div>

    <?php if ($mesaj): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Takım Düzenle' : '➕ Yeni Takım Ekle' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <label>Takım Adı</label>
            <input type="text" name="takim_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['takim_adi']) : '' ?>">
            
            <label>Proje Adı</label>
            <input type="text" name="proje_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['proje_adi']) : '' ?>">
            
            <label>Danışman</label>
            <input type="text" name="danisman" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['danisman']) : '' ?>">
            
            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="takimlar.php" class="btn btn-secondary" style="background:#333;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>🚀 Takım Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Takım Adı</th><th>Proje Adı</th><th>Danışman</th><th>Oluş. Tarihi</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($takimlar as $takim): ?>
                <tr>
                    <td><?= $takim['id'] ?></td>
                    <td><?= htmlspecialchars($takim['takim_adi']) ?></td>
                    <td><?= htmlspecialchars($takim['proje_adi']) ?></td>
                    <td><?= htmlspecialchars($takim['danisman']) ?></td>
                    <td><?= $takim['olusturma_tarihi'] ?></td>
                    <td>
                        <a href="?duzenle=<?= $takim['id'] ?>" class="btn" style="background:#555; padding:4px 12px;">Düzenle</a>
                        <a href="?sil=<?= $takim['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
```

---

Hocam, **ilk etapta** `teknofest_tesla` veritabanını kurun, `baglan.php`, `style.css`, `index.php`, `takimlar.php` dosyalarını oluşturun ve tarayıcıdan `http://localhost/teknofest_tesla/index.php` ile test edin.

Eğer çalıştığını görürsek **üyeler, modeller, eğitim setleri, donanımlar ve projeler** sayfalarını da aynı Tesla stili ve yorum satırlı şekilde tamamlayabiliriz.

Sizden ricam: Çalışıp çalışmadığını kontrol edin, sonra **“Projeler sayfasını da ekle”** diyin. Onu da hemen ekleyeyim.  

Bu şekilde hem SQL’i temelden öğrenmiş hem de modern bir arayüzle projenizi yönetir hale gelmiş olacaksınız.

Hocam devam ediyoruz. Önceki adımda veritabanı ve temel dosyalar (baglan.php, index.php, takimlar.php, style.css) hazırdı. Şimdi sırayla diğer sayfaları ekleyeceğiz: uyeler.php, modeller.php, egitim_setleri.php, donanimlar.php, projeler.php.

Hepsini Tesla stili, yorum satırlı ve güvenli (PDO, prepared statements) şekilde yazacağım.

Önce uyeler.php (takım üyeleri yönetimi, takımlarla ilişkili) ile başlayalım.

3. uyeler.php – Takım Üyeleri Yönetimi (Tesla stili)
php
<?php
/**
 * uyeler.php - Takım üyelerini listeleme, ekleme, düzenleme, silme
 * Her üye bir takıma (takimlar tablosu) bağlıdır.
 */

require_once 'baglan.php';

// Silme işlemi
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM kullanicilar WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: uyeler.php?mesaj=Üye silindi");
    exit;
}

// Ekleme / Güncelleme işlemi
$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $takim_id = (int)$_POST['takim_id'];
    $ad_soyad = trim($_POST['ad_soyad']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $sifre = trim($_POST['sifre']); // şifre hash'lenecek

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme: şifre değiştiyse hashle, yoksa eski şifreyi koru
        $id = (int)$_POST['id'];
        if (!empty($sifre)) {
            $hash = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE kullanicilar SET takim_id=?, ad_soyad=?, email=?, rol=?, sifre=? WHERE id=?");
            $stmt->execute([$takim_id, $ad_soyad, $email, $rol, $hash, $id]);
        } else {
            $stmt = $db->prepare("UPDATE kullanicilar SET takim_id=?, ad_soyad=?, email=?, rol=? WHERE id=?");
            $stmt->execute([$takim_id, $ad_soyad, $email, $rol, $id]);
        }
        $mesaj = "Üye bilgileri güncellendi.";
    } else {
        // Yeni ekleme: şifre zorunlu
        if (empty($sifre)) {
            header("Location: uyeler.php?mesaj=Şifre zorunludur&tip=hata");
            exit;
        }
        $hash = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO kullanicilar (takim_id, ad_soyad, email, rol, sifre) VALUES (?,?,?,?,?)");
        $stmt->execute([$takim_id, $ad_soyad, $email, $rol, $hash]);
        $mesaj = "Yeni üye eklendi.";
    }
    header("Location: uyeler.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenlenecek üyeyi getir
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

// Tüm üyeleri takım adıyla birlikte getir (JOIN)
$uyeler = $db->query("SELECT k.*, t.takim_adi 
                      FROM kullanicilar k
                      JOIN takimlar t ON k.takim_id = t.id
                      ORDER BY k.id DESC")->fetchAll();

// Takımları (select menu için) listele
$takimlar = $db->query("SELECT id, takim_adi FROM takimlar ORDER BY takim_adi")->fetchAll();

$mesaj_goster = isset($_GET['mesaj']) ? $_GET['mesaj'] : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Üye Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Dashboard</a></div>
    </div>

    <?php if ($mesaj_goster): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj_goster) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Üye Düzenle' : '➕ Yeni Üye Ekle' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            
            <label>Takım</label>
            <select name="takim_id" required>
                <option value="">-- Takım Seçin --</option>
                <?php foreach ($takimlar as $takim): ?>
                    <option value="<?= $takim['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['takim_id'] == $takim['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($takim['takim_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Ad Soyad</label>
            <input type="text" name="ad_soyad" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['ad_soyad']) : '' ?>">

            <label>E-posta</label>
            <input type="email" name="email" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['email']) : '' ?>">

            <label>Rol</label>
            <select name="rol" required>
                <option value="uye" <?= ($duzenlenecek && $duzenlenecek['rol'] == 'uye') ? 'selected' : '' ?>>Üye</option>
                <option value="takim_kaptani" <?= ($duzenlenecek && $duzenlenecek['rol'] == 'takim_kaptani') ? 'selected' : '' ?>>Takım Kaptanı</option>
                <option value="danisman" <?= ($duzenlenecek && $duzenlenecek['rol'] == 'danisman') ? 'selected' : '' ?>>Danışman</option>
            </select>

            <label>Şifre <?= $duzenlenecek ? '(Değiştirmek için yazın, boş bırakırsanız eski kalır)' : '' ?></label>
            <input type="password" name="sifre" <?= $duzenlenecek ? '' : 'required' ?>>

            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="uyeler.php" class="btn-secondary" style="background:#333; padding:8px 20px; border-radius:40px;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>👥 Takım Üyeleri Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Takım</th><th>Ad Soyad</th><th>E-posta</th><th>Rol</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($uyeler as $uye): ?>
                <tr>
                    <td><?= $uye['id'] ?></td>
                    <td><?= htmlspecialchars($uye['takim_adi']) ?></td>
                    <td><?= htmlspecialchars($uye['ad_soyad']) ?></td>
                    <td><?= htmlspecialchars($uye['email']) ?></td>
                    <td><?= $uye['rol'] ?></td>
                    <td>
                        <a href="?duzenle=<?= $uye['id'] ?>" class="btn" style="background:#555; padding:4px 12px;">Düzenle</a>
                        <a href="?sil=<?= $uye['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
4. modeller.php – YOLO Modelleri Yönetimi
php
<?php
/**
 * modeller.php - YOLO ve diğer modellerin listelenmesi, eklenmesi, düzenlenmesi, silinmesi
 */

require_once 'baglan.php';

// Silme
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM modeller WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: modeller.php?mesaj=Model silindi");
    exit;
}

// Ekleme / Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model_adi = trim($_POST['model_adi']);
    $framework = trim($_POST['framework']);
    $giris_boyutu = trim($_POST['giris_boyutu']);
    $sinif_sayisi = (int)$_POST['sinif_sayisi'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE modeller SET model_adi=?, framework=?, giris_boyutu=?, sinif_sayisi=? WHERE id=?");
        $stmt->execute([$model_adi, $framework, $giris_boyutu, $sinif_sayisi, $id]);
        $mesaj = "Model güncellendi.";
    } else {
        $stmt = $db->prepare("INSERT INTO modeller (model_adi, framework, giris_boyutu, sinif_sayisi) VALUES (?,?,?,?)");
        $stmt->execute([$model_adi, $framework, $giris_boyutu, $sinif_sayisi]);
        $mesaj = "Yeni model eklendi.";
    }
    header("Location: modeller.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenlenecek model
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM modeller WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

// Tüm modeller
$modeller = $db->query("SELECT * FROM modeller ORDER BY id DESC")->fetchAll();

$mesaj_goster = $_GET['mesaj'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Model Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Dashboard</a></div>
    </div>

    <?php if ($mesaj_goster): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj_goster) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Model Düzenle' : '🧠 Yeni Model Ekle' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <label>Model Adı (örn: YOLOv8n, YOLOv5s)</label>
            <input type="text" name="model_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['model_adi']) : '' ?>">

            <label>Framework</label>
            <input type="text" name="framework" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['framework']) : 'PyTorch' ?>">

            <label>Giriş Boyutu (örn: 640x640)</label>
            <input type="text" name="giris_boyutu" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['giris_boyutu']) : '' ?>">

            <label>Sınıf Sayısı</label>
            <input type="number" name="sinif_sayisi" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['sinif_sayisi']) : '80' ?>">

            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="modeller.php" class="btn-secondary" style="background:#333;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>📋 Model Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Model Adı</th><th>Framework</th><th>Giriş Boyutu</th><th>Sınıf Sayısı</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($modeller as $model): ?>
                <tr>
                    <td><?= $model['id'] ?></td>
                    <td><?= htmlspecialchars($model['model_adi']) ?></td>
                    <td><?= htmlspecialchars($model['framework']) ?></td>
                    <td><?= htmlspecialchars($model['giris_boyutu']) ?></td>
                    <td><?= $model['sinif_sayisi'] ?></td>
                    <td>
                        <a href="?duzenle=<?= $model['id'] ?>" class="btn" style="background:#555;">Düzenle</a>
                        <a href="?sil=<?= $model['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
5. egitim_setleri.php – Eğitim Setleri Yönetimi
php
<?php
/**
 * egitim_setleri.php - Veri setleri (dataset) yönetimi
 */

require_once 'baglan.php';

// Silme
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM egitim_setleri WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: egitim_setleri.php?mesaj=Eğitim seti silindi");
    exit;
}

// Ekleme / Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $set_adi = trim($_POST['set_adi']);
    $aciklama = trim($_POST['aciklama']);
    $etiket_sayisi = (int)$_POST['etiket_sayisi'];
    $goruntu_sayisi = (int)$_POST['goruntu_sayisi'];
    $kaynak_url = trim($_POST['kaynak_url']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE egitim_setleri SET set_adi=?, aciklama=?, etiket_sayisi=?, goruntu_sayisi=?, kaynak_url=? WHERE id=?");
        $stmt->execute([$set_adi, $aciklama, $etiket_sayisi, $goruntu_sayisi, $kaynak_url, $id]);
        $mesaj = "Eğitim seti güncellendi.";
    } else {
        $stmt = $db->prepare("INSERT INTO egitim_setleri (set_adi, aciklama, etiket_sayisi, goruntu_sayisi, kaynak_url) VALUES (?,?,?,?,?)");
        $stmt->execute([$set_adi, $aciklama, $etiket_sayisi, $goruntu_sayisi, $kaynak_url]);
        $mesaj = "Yeni eğitim seti eklendi.";
    }
    header("Location: egitim_setleri.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenleme
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM egitim_setleri WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

$setler = $db->query("SELECT * FROM egitim_setleri ORDER BY id DESC")->fetchAll();
$mesaj_goster = $_GET['mesaj'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Eğitim Setleri</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Dashboard</a></div>
    </div>

    <?php if ($mesaj_goster): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj_goster) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Eğitim Seti Düzenle' : '📊 Yeni Eğitim Seti Ekle' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <label>Set Adı</label>
            <input type="text" name="set_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['set_adi']) : '' ?>">

            <label>Açıklama</label>
            <textarea name="aciklama" rows="3"><?= $duzenlenecek ? htmlspecialchars($duzenlenecek['aciklama']) : '' ?></textarea>

            <label>Etiket Sayısı</label>
            <input type="number" name="etiket_sayisi" value="<?= $duzenlenecek ? $duzenlenecek['etiket_sayisi'] : '' ?>">

            <label>Görüntü Sayısı</label>
            <input type="number" name="goruntu_sayisi" value="<?= $duzenlenecek ? $duzenlenecek['goruntu_sayisi'] : '' ?>">

            <label>Kaynak URL</label>
            <input type="url" name="kaynak_url" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['kaynak_url']) : '' ?>">

            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="egitim_setleri.php" class="btn-secondary" style="background:#333;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>📂 Eğitim Setleri Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Set Adı</th><th>Açıklama</th><th>Etiket Sayısı</th><th>Görüntü Sayısı</th><th>URL</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($setler as $set): ?>
                <tr>
                    <td><?= $set['id'] ?></td>
                    <td><?= htmlspecialchars($set['set_adi']) ?></td>
                    <td><?= htmlspecialchars(substr($set['aciklama'], 0, 50)) ?></td>
                    <td><?= $set['etiket_sayisi'] ?></td>
                    <td><?= $set['goruntu_sayisi'] ?></td>
                    <td><a href="<?= htmlspecialchars($set['kaynak_url']) ?>" target="_blank">Link</a></td>
                    <td>
                        <a href="?duzenle=<?= $set['id'] ?>" class="btn" style="background:#555;">Düzenle</a>
                        <a href="?sil=<?= $set['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
6. donanimlar.php – Donanım Yönetimi
php
<?php
/**
 * donanimlar.php - GPU, CPU, RAM vb. donanım bileşenleri
 */

require_once 'baglan.php';

// Silme
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM donanimlar WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: donanimlar.php?mesaj=Donanım silindi");
    exit;
}

// Ekleme/Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donanim_adi = trim($_POST['donanim_adi']);
    $tur = $_POST['tur'];
    $ozellik = trim($_POST['ozellik']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE donanimlar SET donanim_adi=?, tur=?, ozellik=? WHERE id=?");
        $stmt->execute([$donanim_adi, $tur, $ozellik, $id]);
        $mesaj = "Donanım güncellendi.";
    } else {
        $stmt = $db->prepare("INSERT INTO donanimlar (donanim_adi, tur, ozellik) VALUES (?,?,?)");
        $stmt->execute([$donanim_adi, $tur, $ozellik]);
        $mesaj = "Yeni donanım eklendi.";
    }
    header("Location: donanimlar.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenleme
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM donanimlar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

$donanimlar = $db->query("SELECT * FROM donanimlar ORDER BY id DESC")->fetchAll();
$mesaj_goster = $_GET['mesaj'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Donanım Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Dashboard</a></div>
    </div>

    <?php if ($mesaj_goster): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj_goster) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Donanım Düzenle' : '⚙️ Yeni Donanım Ekle' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>
            <label>Donanım Adı</label>
            <input type="text" name="donanim_adi" required value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['donanim_adi']) : '' ?>">

            <label>Tür</label>
            <select name="tur" required>
                <option value="gpu" <?= ($duzenlenecek && $duzenlenecek['tur'] == 'gpu') ? 'selected' : '' ?>>GPU</option>
                <option value="cpu" <?= ($duzenlenecek && $duzenlenecek['tur'] == 'cpu') ? 'selected' : '' ?>>CPU</option>
                <option value="ram" <?= ($duzenlenecek && $duzenlenecek['tur'] == 'ram') ? 'selected' : '' ?>>RAM</option>
                <option value="depolama" <?= ($duzenlenecek && $duzenlenecek['tur'] == 'depolama') ? 'selected' : '' ?>>Depolama</option>
                <option value="diger" <?= ($duzenlenecek && $duzenlenecek['tur'] == 'diger') ? 'selected' : '' ?>>Diğer</option>
            </select>

            <label>Özellik (örn: 24GB GDDR6X, 32 çekirdek)</label>
            <input type="text" name="ozellik" value="<?= $duzenlenecek ? htmlspecialchars($duzenlenecek['ozellik']) : '' ?>">

            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Ekle' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="donanimlar.php" class="btn-secondary" style="background:#333;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>🖥️ Donanım Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Donanım Adı</th><th>Tür</th><th>Özellik</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($donanimlar as $donanim): ?>
                <tr>
                    <td><?= $donanim['id'] ?></td>
                    <td><?= htmlspecialchars($donanim['donanim_adi']) ?></td>
                    <td><?= $donanim['tur'] ?></td>
                    <td><?= htmlspecialchars($donanim['ozellik']) ?></td>
                    <td>
                        <a href="?duzenle=<?= $donanim['id'] ?>" class="btn" style="background:#555;">Düzenle</a>
                        <a href="?sil=<?= $donanim['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
7. projeler.php – Projeler (İlişkisel Kayıt) – En Önemli Sayfa
Bu sayfada takım, model, eğitim seti, donanım seçilerek yeni proje kaydı yapılır. Listelemede tüm bilgiler JOIN ile gösterilir.

php
<?php
/**
 * projeler.php - Proje kayıtları (hangi takım hangi modeli hangi donanımda eğitmiş)
 * İlişkisel veriler: takimlar, modeller, egitim_setleri, donanimlar
 */

require_once 'baglan.php';

// Silme
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM projeler WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: projeler.php?mesaj=Proje silindi");
    exit;
}

// Ekleme / Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $takim_id = (int)$_POST['takim_id'];
    $model_id = (int)$_POST['model_id'];
    $egitim_seti_id = (int)$_POST['egitim_seti_id'];
    $donanim_id = (int)$_POST['donanim_id'];
    $egitim_suresi_saat = (float)$_POST['egitim_suresi_saat'];
    $dogruluk_metrigi = (float)$_POST['dogruluk_metrigi'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE projeler SET takim_id=?, model_id=?, egitim_seti_id=?, donanim_id=?, egitim_suresi_saat=?, dogruluk_metrigi=? WHERE id=?");
        $stmt->execute([$takim_id, $model_id, $egitim_seti_id, $donanim_id, $egitim_suresi_saat, $dogruluk_metrigi, $id]);
        $mesaj = "Proje güncellendi.";
    } else {
        $stmt = $db->prepare("INSERT INTO projeler (takim_id, model_id, egitim_seti_id, donanim_id, egitim_suresi_saat, dogruluk_metrigi) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$takim_id, $model_id, $egitim_seti_id, $donanim_id, $egitim_suresi_saat, $dogruluk_metrigi]);
        $mesaj = "Yeni proje eklendi.";
    }
    header("Location: projeler.php?mesaj=".urlencode($mesaj));
    exit;
}

// Düzenlenecek proje
$duzenlenecek = null;
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM projeler WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}

// Tüm projeleri JOIN ile getir
$projeler = $db->query("SELECT p.*, 
                        t.takim_adi, 
                        m.model_adi, 
                        e.set_adi AS egitim_seti_adi, 
                        d.donanim_adi
                        FROM projeler p
                        JOIN takimlar t ON p.takim_id = t.id
                        JOIN modeller m ON p.model_id = m.id
                        JOIN egitim_setleri e ON p.egitim_seti_id = e.id
                        JOIN donanimlar d ON p.donanim_id = d.id
                        ORDER BY p.id DESC")->fetchAll();

// Formdaki seçim listeleri için veriler
$takimlar = $db->query("SELECT id, takim_adi FROM takimlar ORDER BY takim_adi")->fetchAll();
$modeller = $db->query("SELECT id, model_adi FROM modeller ORDER BY model_adi")->fetchAll();
$setler = $db->query("SELECT id, set_adi FROM egitim_setleri ORDER BY set_adi")->fetchAll();
$donanimlar = $db->query("SELECT id, donanim_adi FROM donanimlar ORDER BY donanim_adi")->fetchAll();

$mesaj_goster = $_GET['mesaj'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla | Proje Yönetimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="navbar">
        <div class="logo">TESLA<span>FEST</span></div>
        <div class="nav-links"><a href="index.php">← Dashboard</a></div>
    </div>

    <?php if ($mesaj_goster): ?>
        <div class="card" style="background:#1e2a1e;">✅ <?= htmlspecialchars($mesaj_goster) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $duzenlenecek ? '✏️ Proje Düzenle' : '🔗 Yeni Proje Kaydı' ?></h2>
        <form method="post">
            <?php if ($duzenlenecek): ?>
                <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
            <?php endif; ?>

            <label>Takım</label>
            <select name="takim_id" required>
                <option value="">-- Seçin --</option>
                <?php foreach ($takimlar as $takim): ?>
                    <option value="<?= $takim['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['takim_id'] == $takim['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($takim['takim_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Model</label>
            <select name="model_id" required>
                <option value="">-- Seçin --</option>
                <?php foreach ($modeller as $model): ?>
                    <option value="<?= $model['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['model_id'] == $model['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($model['model_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Eğitim Seti</label>
            <select name="egitim_seti_id" required>
                <option value="">-- Seçin --</option>
                <?php foreach ($setler as $set): ?>
                    <option value="<?= $set['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['egitim_seti_id'] == $set['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($set['set_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Donanım</label>
            <select name="donanim_id" required>
                <option value="">-- Seçin --</option>
                <?php foreach ($donanimlar as $donanim): ?>
                    <option value="<?= $donanim['id'] ?>" <?= ($duzenlenecek && $duzenlenecek['donanim_id'] == $donanim['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($donanim['donanim_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Eğitim Süresi (saat)</label>
            <input type="number" step="0.01" name="egitim_suresi_saat" required value="<?= $duzenlenecek ? $duzenlenecek['egitim_suresi_saat'] : '' ?>">

            <label>Doğruluk Metriği (mAP, 0-1 arası)</label>
            <input type="number" step="0.0001" name="dogruluk_metrigi" required value="<?= $duzenlenecek ? $duzenlenecek['dogruluk_metrigi'] : '' ?>">

            <button type="submit"><?= $duzenlenecek ? 'Güncelle' : 'Kaydet' ?></button>
            <?php if ($duzenlenecek): ?>
                <a href="projeler.php" class="btn-secondary" style="background:#333;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>📌 Proje Listesi</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Takım</th><th>Model</th><th>Eğitim Seti</th><th>Donanım</th><th>Süre(saat)</th><th>Doğruluk(mAP)</th><th>Tarih</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($projeler as $proje): ?>
                <tr>
                    <td><?= $proje['id'] ?></td>
                    <td><?= htmlspecialchars($proje['takim_adi']) ?></td>
                    <td><?= htmlspecialchars($proje['model_adi']) ?></td>
                    <td><?= htmlspecialchars($proje['egitim_seti_adi']) ?></td>
                    <td><?= htmlspecialchars($proje['donanim_adi']) ?></td>
                    <td><?= $proje['egitim_suresi_saat'] ?></td>
                    <td><?= $proje['dogruluk_metrigi'] ?></td>
                    <td><?= $proje['proje_tarihi'] ?></td>
                    <td>
                        <a href="?duzenle=<?= $proje['id'] ?>" class="btn" style="background:#555;">Düzenle</a>
                        <a href="?sil=<?= $proje['id'] ?>" class="btn" style="background:#333;" onclick="return confirm('Emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
Özet – Şu Anda Proje Dosyaları:
teknofest_tesla.sql – Veritabanı oluşturma (phpMyAdmin'de çalıştır)

baglan.php – PDO bağlantı

style.css – Tesla tarzı koyu tema

index.php – Dashboard

takimlar.php – Takım CRUD

uyeler.php – Üye CRUD (takımla ilişkili)

modeller.php – YOLO model CRUD

egitim_setleri.php – Dataset CRUD

donanimlar.php – Donanım CRUD

projeler.php – Proje CRUD (ilişkisel)

Hocam, tüm bu kodları ilgili dosyalara kopyalayın, XAMPP'te Apache ve MySQL'i başlatın, tarayıcıdan http://localhost/teknofest_tesla/index.php ile açın.

Eğer herhangi bir hata alırsanız veya eklememi istediğiniz bir özellik (örneğin raporlama, grafikler, kullanıcı girişi, yetkilendirme) olursa söyleyin, hemen ekleyeyim.

Projenin temel işlevselliği tamamdır. İyi çalışmalar hocam!

Hocam, şimdiye kadar yazdığımız tüm kodları baştan sona detaylıca anlatmamı istiyorsunuz. Anlıyorum, önce her satırın ne işe yaradığını öğrenmek sonra ilerlemek en doğrusu.

Sırayla gideceğim:

SQL Veritabanı – Her tablo, sütun, foreign key ne demek, neden var.

baglan.php – PDO ile bağlantı nasıl çalışır, try-catch nedir.

style.css – Tesla temasındaki her bir CSS kuralının görevi.

index.php – Dashboard mantığı, veritabanı sorguları, HTML içinde PHP kullanımı.

takimlar.php – CRUD işlemleri: Listeleme, ekleme, güncelleme, silme adım adım.

Not: Uzun olacak ama her satırı açıklayacağım. Hazırsanız başlıyorum.

1. SQL Veritabanının Detaylı Anlatımı
İlk olarak oluşturduğumuz teknofest_tesla veritabanındaki tabloları tek tek ele alalım.

1.1 Veritabanı Oluşturma
sql
CREATE DATABASE IF NOT EXISTS teknofest_tesla
CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE DATABASE → Yeni bir veritabanı oluşturur.

IF NOT EXISTS → Eğer aynı isimde veritabanı varsa hata verme, atla.

CHARACTER SET utf8mb4 → Türkçe karakterler (ç, ğ, ş, ı, ü, ö) ve emoji desteği sağlar.

COLLATE utf8mb4_general_ci → Büyük/küçük harf duyarsız sıralama ve karşılaştırma yapar.

Sonra USE teknofest_tesla; ile bu veritabanını aktif hale getiriyoruz.

1.2 Tablo: takimlar
sql
CREATE TABLE takimlar (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Birincil anahtar, otomatik artan',
    takim_adi VARCHAR(100) NOT NULL UNIQUE COMMENT 'Takım adı, benzersiz',
    proje_adi VARCHAR(150) NOT NULL COMMENT 'Proje başlığı',
    danisman VARCHAR(100) COMMENT 'Akademik danışman adı',
    olusturma_tarihi DATE DEFAULT (CURRENT_DATE) COMMENT 'Kayıt tarihi'
);
id → Her takıma otomatik artan bir numara verir. PRIMARY KEY → Bu sütun satırı benzersiz yapar. AUTO_INCREMENT → Her yeni kayıtta bir önceki +1.

takim_adi → Maksimum 100 karakter, NOT NULL (boş olamaz), UNIQUE (aynı isimde iki takım olamaz).

proje_adi → Zorunlu, en fazla 150 karakter.

danisman → Opsiyonel (NULL olabilir).

olusturma_tarihi → Varsayılan olarak bugünün tarihini alır (CURRENT_DATE).

1.3 Tablo: kullanicilar (Takım üyeleri)
sql
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('takim_kaptani', 'uye', 'danisman') DEFAULT 'uye',
    sifre VARCHAR(255) NOT NULL,
    FOREIGN KEY (takim_id) REFERENCES takimlar(id) ON DELETE CASCADE
);
takim_id → Hangi takıma ait olduğunu belirtir. NOT NULL → Her üye bir takıma bağlı olmalı.

rol → ENUM tipi: sadece bu üç değerden birini alabilir. Varsayılan 'uye'.

sifre → Şifre hash’lenmiş haliyle saklanacak (örneğin password_hash() ile).

Foreign Key (Yabancı Anahtar):
FOREIGN KEY (takim_id) REFERENCES takimlar(id) → takim_id sütunu, takimlar tablosundaki id sütununa referans verir.
ON DELETE CASCADE → Eğer bir takım silinirse, o takıma ait tüm üyeler de otomatik silinir. Bu ilişki sayesinde veritabanı tutarlı kalır.

1.4 Tablo: modeller (YOLO modelleri)
sql
CREATE TABLE modeller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_adi VARCHAR(50) NOT NULL UNIQUE,
    framework VARCHAR(30) DEFAULT 'PyTorch',
    giris_boyutu VARCHAR(20),
    sinif_sayisi INT DEFAULT 80
);
model_adi → Örneğin 'YOLOv8n', 'YOLOv5s'. Benzersiz olmalı.

framework → Kullanılan kütüphane, varsayılan 'PyTorch'.

giris_boyutu → Modelin giriş olarak beklediği görüntü boyutu ('640x640' gibi).

sinif_sayisi → Modelin kaç farklı nesne sınıfı tanıdığı. Varsayılan 80 (COCO dataset için).

1.5 Tablo: egitim_setleri
sql
CREATE TABLE egitim_setleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    set_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    etiket_sayisi INT,
    goruntu_sayisi INT,
    kaynak_url VARCHAR(255)
);
TEXT tipi → Uzun açıklamalar için (64KB'a kadar).

kaynak_url → Veri setinin indirilebileceği veya referans alındığı adres.

1.6 Tablo: donanimlar
sql
CREATE TABLE donanimlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donanim_adi VARCHAR(100) NOT NULL,
    tur ENUM('gpu','cpu','ram','depolama','diger') NOT NULL,
    ozellik VARCHAR(255)
);
tur → Donanımın tipini sınıflandırır.

1.7 Tablo: projeler (Ana bağlantı tablosu)
sql
CREATE TABLE projeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takim_id INT NOT NULL,
    model_id INT NOT NULL,
    egitim_seti_id INT NOT NULL,
    donanim_id INT NOT NULL,
    egitim_suresi_saat DECIMAL(5,2),
    dogruluk_metrigi DECIMAL(5,4),
    proje_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takim_id) REFERENCES takimlar(id),
    FOREIGN KEY (model_id) REFERENCES modeller(id),
    FOREIGN KEY (egitim_seti_id) REFERENCES egitim_setleri(id),
    FOREIGN KEY (donanim_id) REFERENCES donanimlar(id)
);
DECIMAL(5,2) → Toplam 5 basamak, 2'si ondalık. Örn: 12.50, 123.45'e kadar.

DECIMAL(5,4) → 1.0000 ile 9.9999 arası (mAP değerleri 0-1 arası olduğu için yeterli).

proje_tarihi → DATETIME tipi, varsayılan olarak ekleme anındaki tarih ve saat.

Dört ayrı foreign key ile projeler; takımlar, modeller, eğitim setleri ve donanımlar ile ilişkilendirilir.

Son olarak örnek veri eklemeleri – bu sadece test amaçlıdır, kendi verilerinizle değiştireceksiniz.

2. baglan.php – Bağlantı Dosyası Detaylı Anlatım
php
<?php
$host = 'localhost';
$dbname = 'teknofest_tesla';
$kullanici = 'root';
$sifre = '';
Bunlar XAMPP'ın varsayılan ayarları. Eğer şifre belirlediyseniz onu yazın.

php
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>
PDO (PHP Data Objects) – Veritabanına güvenli ve nesne yönelimli şekilde bağlanmak için kullanılır.

try { ... } catch { ... } – Olası hataları yakalamak için. Eğer bağlantı başarısız olursa catch bloğu çalışır ve hata mesajını gösterir.

new PDO(...) – Bağlantı nesnesi oluşturur. DSN (Data Source Name) içinde host, veritabanı adı ve karakter seti belirtilir.

setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) – Hataları exception fırlatarak bildir, böylece try-catch ile yakalayabiliriz.

setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC) – Sorgu sonuçlarını varsayılan olarak ilişkisel dizi (sütun adı => değer) şeklinde getir.

die() – Hata mesajını ekrana yaz ve scripti durdur.

Bu dosyayı tüm diğer sayfalara require_once 'baglan.php'; ile dahil edeceğiz.

3. style.css – Tesla Teması Detaylı Anlatım
Seçiciler ve anlamları:

css
* { margin: 0; padding: 0; box-sizing: border-box; }
* (universal selector) – Tüm HTML elementlerine uygulanır. Margin/padding sıfırlanır, box-sizing: border-box ise elementin genişliğine padding ve border dahil edilir (daha kolay yerleşim).

css
body {
    background-color: #0a0a0a; /* koyu siyahımsı */
    color: #f5f5f5; /* açık gri yazı */
    padding: 2rem;
}
rem – Root element (html) font büyüklüğüne göre. Varsayılan 16px ise 2rem=32px.

css
.navbar {
    background: #111;
    border-bottom: 1px solid #222;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
}
display: flex – Esnek kutu düzeni. justify-content: space-between soldaki logo ile sağdaki linkleri ayırır.

css
.logo span {
    color: #e82127; /* Tesla kırmızısı */
}
Tesla'nın bilinen kırmızı rengi.

css
.card {
    background: #141414;
    border-radius: 24px;
    border: 1px solid #222;
    box-shadow: 0 10px 20px rgba(0,0,0,0.5);
}
Koyu gri kartlar, hafif gölge ve yuvarlak köşeler.

css
button, .btn {
    background: #e82127;
    border-radius: 40px;
    transition: 0.2s;
}
button:hover {
    background: #ff3b3b;
    transform: scale(1.02);
}
transition – Renk ve ölçek değişimini 0.2 saniyede yumuşatır.

transform: scale(1.02) – Fare üzerine gelince buton %2 büyür.

Tablolar, form inputlar da benzer şekilde koyu temaya uygun ayarlanmış.

4. index.php – Ana Dashboard Anlatımı
İlk satır:

php
require_once 'baglan.php';
Bağlantı dosyasını bir kere dahil eder. require_once hata verirse script durur.

Veritabanı sorguları:

php
$stmt = $db->query("SELECT COUNT(*) FROM takimlar");
$toplam_takim = $stmt->fetchColumn();
$db->query() – Sonuç kümesi döndüren SQL çalıştırır (SELECT, SHOW, vb.).

fetchColumn() – Tek bir sütun değerini döndürür (burada toplam takım sayısı).

php
$stmt = $db->query("SELECT p.*, t.takim_adi ... FROM projeler p
                    JOIN takimlar t ON p.takim_id = t.id ...");
$en_basarili = $stmt->fetch();
JOIN ile birden fazla tablodan veri çekiyoruz. p.* projeler tablosundaki tüm sütunlar.

fetch() – Tek bir satır döndürür (en başarılı proje).

php
$son_projeler = $stmt->fetchAll();
Tüm sonuçları (5 proje) dizi olarak getirir.

HTML içinde PHP:

php
<div class="stat-number"><?= $toplam_takim ?></div>
<?= ... ?> kısa echo etiketidir. $toplam_takim değişkeninin değerini yazdırır.

php
<?php foreach ($son_projeler as $proje): ?>
<tr>
    <td><?= $proje['id'] ?></td>
    ...
</tr>
<?php endforeach; ?>
Alternatif PHP kontrol yapısı (: ve endforeach; ile). Her proje için tablo satırı oluşturur.

htmlspecialchars() – XSS saldırılarını önlemek için kullanılır. Özel karakterleri (&, <, >, ", ') HTML entity'lerine çevirir.

5. takimlar.php – CRUD İşlemleri Detaylı Anlatım
5.1 Silme İşlemi
php
if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: takimlar.php?mesaj=Takım silindi");
    exit;
}
isset($_GET['sil']) – URL'de ?sil=3 gibi bir parametre varsa.

(int) – Güvenlik için integer'a çevir.

prepare + execute – Prepared statement SQL injection'ı önler. ? yer tutucu, sonra execute([$id]) ile değer gönderilir.

header("Location: ...") – Sayfayı yönlendirir, ardından exit ile script durdurulur.

5.2 Ekleme / Güncelleme (POST işlemi)
php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $takim_adi = trim($_POST['takim_adi']);
    // ...
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Güncelleme
        $stmt = $db->prepare("UPDATE takimlar SET ... WHERE id=?");
        $stmt->execute([$takim_adi, $proje_adi, $danisman, $id]);
    } else {
        // Yeni ekle
        $stmt = $db->prepare("INSERT INTO takimlar (...) VALUES (?,?,?)");
        $stmt->execute([$takim_adi, $proje_adi, $danisman]);
    }
    header("Location: takimlar.php?mesaj=...");
}
$_SERVER['REQUEST_METHOD'] – Formun POST ile gönderilip gönderilmediğini kontrol eder.

trim() – Baştaki ve sondaki boşlukları temizler.

Aynı form hem ekleme hem güncelleme için kullanılır. Eğer gizli id input'u varsa ve boş değilse güncelleme, yoksa ekleme yapar.

5.3 Düzenleme için veri çekme
php
if (isset($_GET['duzenle'])) {
    $id = (int)$_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM takimlar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek = $stmt->fetch();
}
Düzenlenecek takımın verilerini alıp form içinde value olarak gösteririz.

5.4 Listeleme
php
$takimlar = $db->query("SELECT * FROM takimlar ORDER BY id DESC")->fetchAll();
Tüm takımları en yeniden eskiye sıralar.

5.5 Formda düzenleme modunda gizli input
php
<?php if ($duzenlenecek): ?>
    <input type="hidden" name="id" value="<?= $duzenlenecek['id'] ?>">
<?php endif; ?>
Eğer düzenleniyorsa, gönderilen POST'ta id değeri olur, böylece güncelleme kısmına girer.

Silme işleminde onclick="return confirm('Emin misin?')" – Kullanıcıya onay sorusu gösterir. İptal ederse silme işlemi gerçekleşmez.

6. Genel Akış ve Kullanım
Kullanıcı index.php'yi açar → Bağlantı kurulur, sorgular çalışır, dashboard görüntülenir.

Navbar'dan "Takımlar"a tıklar → takimlar.php listeleme sayfasına gider.

Takım eklemek için formu doldurup "Ekle" butonuna basar → POST işlemi yapılır, veritabanına kaydedilir, sayfa yenilenir.

Bir takımın yanındaki "Düzenle" tıklanır → URL'de ?duzenle=id ile aynı sayfada form doldurulur, güncelleme yapılır.

"Sil" tıklanır → ?sil=id ile silme işlemi yapılır, sayfa yönlendirilir.

Tüm bu işlemler güvenli prepared statement ile yapıldığı için SQL injection riski yoktur.

Hocam, şimdiye kadar SQL, bağlantı, CSS, index, takimlar sayfalarının her satırını detaylıca anlattım.
Sıradaki adımlar: uyeler.php, modeller.php, egitim_setleri.php, donanimlar.php, projeler.php sayfalarını da aynı titizlikle anlatarak kodlayacağız.

Sizden ricam: Şimdilik bu anlattıklarımı sindirin, denemeler yapın. Anlamadığınız bir nokta varsa sorun lütfen. Sonra "Devam edelim" deyin, kaldığımız yerden diğer sayfalara geçelim.

Projenizin temeli sağlam. Başarıyla ilerliyoruz.

Uygulamanızda kullanıcı oturumunu düz çerezler yerine oturumlar (sessions) ile kontrol etmelisiniz.  hocam burda demek istenen nedir ve nasıl yaparım 

Hocam sorduğunuz şey güvenlik ve kullanıcı oturum yönetimi ile ilgili. Size şimdi bu uyarının ne anlama geldiğini ve nasıl yapmanız gerektiğini adım adım anlatayım. Önce kısaca farkı, sonra uygulamalı çözümü.

Öncelikle şu ana kadar yaptığımız projede oturum kontrolü yoktu (giriş/çıkış sistemi yoktu). Uyarı, eğer kullanıcı girişi yapacaksanız, kullanıcının kimlik bilgilerini tarayıcıya "düz çerez" olarak değil, sunucu taraflı "session" ile saklamanız gerektiğini söylüyor.

Kısa açıklama:

Düz çerez (cookie) : Kullanıcının bilgileri (örneğin kullanıcı adı, şifre veya ID) doğrudan tarayıcıya gönderilir ve sonraki isteklerde tarayıcı bunu sunucuya geri yollar. Bu güvensizdir çünkü bilgiler değiştirilebilir, çalınabilir.

Oturum (session) : Sunucu taraflı bir dosyada kullanıcıya özel veriler saklanır, tarayıcıya sadece eşsiz bir oturum ID'si (genelde bir çerez olarak) gönderilir. Kullanıcının gerçek bilgileri sunucuda kalır, daha güvenlidir.

Nasıl yaparız? Mevcut projemize basit bir kullanıcı giriş sistemi ve session yönetimi ekleyelim. Adımlar:

kullanicilar tablosunda zaten email, sifre (hashlenmiş) var. Şifreleri hashlemek için password_hash() kullanacağız.

Giriş formu ve giriş işlemi yapan bir giris.php dosyası.

Oturumu başlatmak için session_start().

Giriş başarılıysa session değişkenlerine ($_SESSION['kullanici_id'], $_SESSION['kullanici_adi'] vb.) atama yapmak.

Korunan sayfalarda (örneğin takimlar.php, projeler.php) önce session kontrolü yapmak, giriş yoksa yönlendirmek.

Çıkış için cikis.php dosyası.

Şimdi size Tesla projemize entegre edebileceğiniz kodları yazıyorum, her satırı açıklamalı. Sonra mevcut sayfalara nasıl ekleyeceğinizi anlatacağım.

1. Veritabanında şifre hash'leme (Örnek kullanıcı ekleme)
Zaten kullanicilar tablomuz var. İçine şifresi hashlenmiş bir kullanıcı ekleyelim (örneğin takım kaptanı). Bunu bir PHP dosyasıyla yapabileceğiniz gibi doğrudan phpMyAdmin'den de ekleyebilirsiniz. Ama size güvenli yöntemi göstereyim:

kullanici_ekle.php (tek kullanımlık, sonra silebilirsiniz)

php
<?php
require_once 'baglan.php';

$email = 'kaptan@teslafest.com';
$sifre_duz = '123456'; // bu geçici, sonra değiştirilir
$hash = password_hash($sifre_duz, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO kullanicilar (takim_id, ad_soyad, email, rol, sifre) VALUES (1, 'Ahmet Kaptan', ?, 'takim_kaptani', ?)");
$stmt->execute([$email, $hash]);

echo "Kullanıcı eklendi. Email: $email, Şifre: $sifre_duz";
Bunu çalıştırınca veritabanına kullanıcı eklenir.

2. Giriş Sayfası (giris.php)
php
<?php
/**
 * giris.php - Kullanıcı giriş formu ve işlemi
 * Session ile oturum yönetimi
 */
session_start(); // Oturumu başlat

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'baglan.php';

$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $sifre = $_POST['sifre'];

    // Veritabanında email adresine göre kullanıcı ara
    $stmt = $db->prepare("SELECT id, ad_soyad, email, sifre, rol, takim_id FROM kullanicilar WHERE email = ?");
    $stmt->execute([$email]);
    $kullanici = $stmt->fetch();

    if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
        // Şifre doğru, session değişkenlerini oluştur
        $_SESSION['kullanici_id'] = $kullanici['id'];
        $_SESSION['kullanici_adi'] = $kullanici['ad_soyad'];
        $_SESSION['email'] = $kullanici['email'];
        $_SESSION['rol'] = $kullanici['rol'];
        $_SESSION['takim_id'] = $kullanici['takim_id'];

        // Başarılı giriş sonrası ana sayfaya yönlendir
        header("Location: index.php");
        exit;
    } else {
        $hata = "Hatalı e-posta veya şifre.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla Giriş</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card">
        <h2>🔐 TeslaFest Yönetim Girişi</h2>
        <?php if ($hata): ?>
            <p style="color: #e82127;"><?= htmlspecialchars($hata) ?></p>
        <?php endif; ?>
        <form method="post">
            <label>E-posta</label>
            <input type="email" name="email" required autofocus>
            <label>Şifre</label>
            <input type="password" name="sifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
        <p style="margin-top: 1rem; font-size: 0.8rem;">Demo hesap: kaptan@teslafest.com / 123456</p>
    </div>
</div>
</body>
</html>
3. Çıkış Sayfası (cikis.php)
php
<?php
session_start();
// Oturum değişkenlerini temizle
$_SESSION = array();
// Oturum çerezini de silmek isterseniz
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Oturumu sonlandır
session_destroy();
// Giriş sayfasına yönlendir
header("Location: giris.php");
exit;
4. Mevcut Sayfaları Koruma Altına Alma
Örneğin index.php, takimlar.php, projeler.php gibi tüm yönetim sayfalarının en başına aşağıdaki kontrolü eklemeliyiz. Bu sayfaların sadece giriş yapmış kullanıcılar tarafından görülmesini sağlar.

Her sayfanın en üstüne, session_start() ve kontrol kodu ekleyin:

php
<?php
session_start();
// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}
// ... devamında normal kodlar, baglan.php dahil etme vs.
Örneğin takimlar.php dosyasının en başı şöyle olmalı:

php
<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}
require_once 'baglan.php';
// ... diğer kodlar
Aynı şekilde index.php, modeller.php, egitim_setleri.php, donanimlar.php, projeler.php, uyeler.php için de aynı kontrolü ekleyin.

5. Ana Sayfada Kullanıcı Bilgisini Gösterme ve Çıkış Linki
index.php içinde navbar bölümüne oturum açmış kullanıcının adını ve çıkış linkini ekleyelim. Örnek navbar düzenlemesi:

php
<div class="navbar">
    <div class="logo">TESLA<span>FEST</span></div>
    <div class="nav-links">
        <a href="index.php">🏠 Dashboard</a>
        <a href="takimlar.php">🚀 Takımlar</a>
        <a href="uyeler.php">👥 Üyeler</a>
        <a href="modeller.php">🧠 Modeller</a>
        <a href="egitim_setleri.php">📊 Eğitim Setleri</a>
        <a href="donanimlar.php">⚙️ Donanımlar</a>
        <a href="projeler.php">🔗 Projeler</a>
        <span style="color:#ccc; margin-left:20px;">👋 <?= htmlspecialchars($_SESSION['kullanici_adi']) ?></span>
        <a href="cikis.php" style="color:#e82127;">Çıkış</a>
    </div>
</div>
6. İsteğe Bağlı: Farklı Roller İçin Yetkilendirme
Eğer sadece takim_kaptani rolündeki kullanıcıların takım ekleme/silme yetkisi olmasını isterseniz, ilgili sayfada (örneğin takimlar.php) işlem yapmadan önce rolü kontrol edebilirsiniz:

php
// Silme işlemi sadece kaptanlar için
if (isset($_GET['sil']) && $_SESSION['rol'] == 'takim_kaptani') {
    // silme işlemi
} else {
    // yetkiniz yok mesajı
}
Özet
Düz çerez yerine session kullanmak: Kullanıcıya ait hassas bilgileri sunucuda saklamak, tarayıcıya sadece oturum ID'si vermek.

Bunun için PHP'de session_start() ile oturumu başlatıp $_SESSION dizisine veri atıyoruz.

Giriş kontrolünü her sayfanın başına ekliyoruz.

Çıkış yaparken session_destroy() ile oturumu sonlandırıyoruz.

Hocam, şimdi bu adımları uygulayın. Önce giris.php ve cikis.php dosyalarını oluşturun, ardından mevcut sayfaların başına session kontrolünü ekleyin. Daha sonra test etmek için http://localhost/teknofest_tesla/giris.php adresine gidin, demo kullanıcıyla giriş yapın. Ana sayfada adınızı görmelisiniz.

Eğer bir yerde takılırsanız veya tüm dosyaların güncel hallerini sıfırdan yazmamı isterseniz söyleyin, hepsini düzenleyip size tek parça vereyim. Bu şekilde oturum yönetimini tam olarak anlamış olursunuz.



Kullanıcı iki konu soruyor: oturum (session) yönetimi ve şifre hash'leme. Önce oturumları anlatacağım, sonra hash mantığı ve uygulamasını. Kod örnekleriyle açıklayacağım. Ayrıca önceki projeye entegrasyonu da göstereceğim. Kullanıcı "hocam bana bu kodları tamamen anlatmanı istiyorum lütfen" demiş, ardından iki spesifik soru sormuş. Ona göre detaylı anlatım yapacağım.

Hocam, çok iyi iki soru sordunuz. Bunlar web güvenliğinin temel taşları. Şimdi size neden ve nasıl yapıldığını, mantığıyla birlikte anlatayım. Daha sonra bunları projemize adım adım entegre edelim.

1. Oturum (Session) Nedir, Neden Kullanmalıyız?
Problem: HTTP “durumsuz” (stateless) bir protokoldür.
Her istek bağımsızdır. Kullanıcı giriş yaptıktan sonra, sonraki sayfada sunucu onun kim olduğunu bilmez. Çerezler (cookies) bir çözümdür ama çerezlere güvenilmez veri koymak tehlikelidir.

Session (Oturum) Çözümü:
Kullanıcı giriş yapınca sunucu, o kullanıcıya özel benzersiz bir oturum ID’si oluşturur.

Bu ID, kullanıcının tarayıcısına sadece bir çerez olarak gönderilir (genelde PHPSESSID).

Sunucu tarafta, bu ID ile ilişkili veriler (kullanıcı ID’si, adı, rolü vb.) sunucuda saklanır.

Kullanıcı her yeni istek yaptığında çerezdeki oturum ID’si gelir, sunucu karşılığındaki verileri okur.

Avantajları:
Güvenli: Kullanıcıya ait gerçek bilgiler (şifre, e-posta) çerezde değil, sunucuda.

Kolay: PHP’nin $_SESSION süper globali ile basitçe kullanılır.

Kontrol: Oturumu sonlandırmak (çıkış yapmak) için sadece sunucudaki verileri silmek yeterli.

Nasıl Yapılır? (PHP’de)
php
// Her sayfanın EN BAŞINDA oturumu başlat
session_start();

// Oturum değişkenlerine atama
$_SESSION['kullanici_id'] = 5;
$_SESSION['kullanici_adi'] = 'ahmet';
$_SESSION['rol'] = 'takim_kaptani';

// Başka bir sayfada okuma
session_start();
echo $_SESSION['kullanici_adi']; // ahmet

// Çıkış yap (oturumu sonlandır)
session_start();
session_destroy();   // tüm oturum verilerini siler
Projemize Uyarlama:
Giriş sayfası (giris.php) yapacağız. Kullanıcı e-posta ve şifre girecek. Doğrulama sonrası oturum başlatacağız. Giriş gerektiren sayfalarda (örn. takimlar.php, projeler.php) en üste şu kontrolü koyacağız:

php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}
Böylece sadece giriş yapmış kullanıcılar görebilir.

2. Şifre Hash’leme – Neden ve Nasıl?
Neden düz metin (plain text) kaydedilmemeli?
Veritabanı ele geçirilirse, tüm kullanıcı şifreleri açıkta kalır.

Birçok kullanıcı farklı sitelerde aynı şifreyi kullandığı için, bu durum büyük güvenlik riskidir.

Hash Nedir?
Hash, bir girdi (şifre) alıp sabit uzunlukta, tek yönlü bir çıktı üreten matematiksel işlevdir.

Aynı şifre her zaman aynı hash’i üretir (tutarlılık).

Hash’ten orijinal şifreyi geri elde etmek (pratikte) imkânsızdır.

Doğrulama yaparken: Kullanıcının girdiği şifre hash’lenir, veritabanındaki hash ile karşılaştırılır.

PHP’de Kullanılacak Fonksiyonlar:
password_hash($sifre, PASSWORD_DEFAULT) – güçlü, güncel algoritma (bcrypt) kullanarak hash üretir.

password_verify($girilen_sifre, $hash) – girilen şifre ile hash’in eşleşip eşleşmediğini kontrol eder.

Örnek – Kayıt Olurken:
php
$sifre = $_POST['sifre']; // kullanıcının girdiği düz şifre
$hash = password_hash($sifre, PASSWORD_DEFAULT);
// $hash'i veritabanındaki 'sifre' sütununa kaydet
Örnek – Giriş Yaparken:
php
$email = $_POST['email'];
$sifre = $_POST['sifre'];

// Veritabanından o email'e ait hash'i çek
$stmt = $db->prepare("SELECT id, sifre FROM kullanicilar WHERE email = ?");
$stmt->execute([$email]);
$kullanici = $stmt->fetch();

if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
    // Şifre doğru, oturum başlat
    session_start();
    $_SESSION['kullanici_id'] = $kullanici['id'];
    header("Location: index.php");
} else {
    echo "Hatalı email veya şifre";
}
3. Projemize Bu İki Güvenlik Önlemini Adım Adım Ekleyelim
Mevcut proje dosyalarımızda şu değişiklikleri yapacağız:

kullanicilar tablosunda sifre sütunu zaten var (VARCHAR(255)). Buraya hash’i kaydedeceğiz.

giris.php – Giriş formu ve doğrulama.

cikis.php – Oturumu sonlandırma.

baglan.php üzerinde değişiklik yok.

Korumalı sayfalar (takimlar.php, projeler.php vb.) en başa oturum kontrolü ekleyecek.

kayit.php (isteğe bağlı) – Yeni kullanıcı kaydı ve hash’leme.

3.1. Tabloda Değişiklik (zaten uygun)
Tablomuzda sifre VARCHAR(255) NOT NULL mevcut. Yeterli.

3.2. Giriş Sayfası (giris.php) – Tam Kod
php
<?php
/**
 * giris.php - Kullanıcı giriş sayfası (Tesla stili)
 * Oturum başlatma ve şifre hash doğrulama
 */
session_start(); // her zaman en başta

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'baglan.php';

$hata = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $sifre = $_POST['sifre'];

    if (empty($email) || empty($sifre)) {
        $hata = "Lütfen e-posta ve şifrenizi girin.";
    } else {
        // Kullanıcıyı email ile bul
        $stmt = $db->prepare("SELECT id, ad_soyad, email, sifre, rol FROM kullanicilar WHERE email = ?");
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            // Şifre doğru -> oturum başlat
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_adi'] = $kullanici['ad_soyad'];
            $_SESSION['rol'] = $kullanici['rol'];
            // Başarılı giriş, ana sayfaya yönlendir
            header("Location: index.php");
            exit;
        } else {
            $hata = "E-posta veya şifre hatalı.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla Giriş | Teknofest</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            max-width: 400px;
            margin: 5rem auto;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card">
        <div class="logo" style="text-align:center; margin-bottom:2rem;">TESLA<span>FEST</span></div>
        <h2 style="text-align:center;">Sürüşe Başla</h2>
        <?php if ($hata): ?>
            <p style="color:#e82127; text-align:center;"><?= htmlspecialchars($hata) ?></p>
        <?php endif; ?>
        <form method="post">
            <label>E-posta</label>
            <input type="email" name="email" required autofocus>
            <label>Şifre</label>
            <input type="password" name="sifre" required>
            <button type="submit" style="width:100%;">Giriş Yap</button>
        </form>
        <p style="text-align:center; margin-top:1rem;">Henüz üye değil misiniz? <a href="kayit.php" style="color:#e82127;">Kayıt Ol</a></p>
    </div>
</div>
</body>
</html>
3.3. Kayıt Sayfası (kayit.php) – Şifre Hash’leme
php
<?php
/**
 * kayit.php - Yeni kullanıcı kaydı (şifre hash'lenir)
 */
session_start();
if (isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'baglan.php';

$hata = '';
$basari = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad_soyad = trim($_POST['ad_soyad']);
    $email = trim($_POST['email']);
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];
    $takim_id = $_POST['takim_id']; // Takım seçimi (isteğe bağlı, önce takım oluşturulmalı)
    $rol = $_POST['rol'] ?? 'uye';

    if (empty($ad_soyad) || empty($email) || empty($sifre)) {
        $hata = "Lütfen tüm alanları doldurun.";
    } elseif ($sifre !== $sifre_tekrar) {
        $hata = "Şifreler eşleşmiyor.";
    } elseif (strlen($sifre) < 6) {
        $hata = "Şifre en az 6 karakter olmalıdır.";
    } else {
        // Aynı email var mı kontrol et
        $stmt = $db->prepare("SELECT id FROM kullanicilar WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $hata = "Bu e-posta zaten kayıtlı.";
        } else {
            // Şifreyi hash'le
            $hash = password_hash($sifre, PASSWORD_DEFAULT);
            // Veritabanına ekle
            $stmt = $db->prepare("INSERT INTO kullanicilar (takim_id, ad_soyad, email, sifre, rol) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$takim_id, $ad_soyad, $email, $hash, $rol])) {
                $basari = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
                // Formu temizlemek için yönlendirme yapılabilir, burada mesaj gösteriyoruz.
            } else {
                $hata = "Kayıt sırasında bir hata oluştu.";
            }
        }
    }
}

// Takım listesini çek (kullanıcı hangi takıma ait olacak)
$takimlar = $db->query("SELECT id, takim_adi FROM takimlar ORDER BY takim_adi")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tesla Kayıt | Teknofest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="card" style="max-width:500px; margin:auto;">
        <div class="logo" style="text-align:center;">TESLA<span>FEST</span></div>
        <h2 style="text-align:center;">Yeni Sürücü</h2>
        <?php if ($hata): ?>
            <p style="color:#e82127;"><?= htmlspecialchars($hata) ?></p>
        <?php endif; ?>
        <?php if ($basari): ?>
            <p style="color:#4caf50;"><?= htmlspecialchars($basari) ?> <a href="giris.php">Giriş yap</a></p>
        <?php else: ?>
        <form method="post">
            <label>Ad Soyad</label>
            <input type="text" name="ad_soyad" required>
            <label>E-posta</label>
            <input type="email" name="email" required>
            <label>Takım</label>
            <select name="takim_id" required>
                <option value="">Bir takım seçin</option>
                <?php foreach ($takimlar as $takim): ?>
                    <option value="<?= $takim['id'] ?>"><?= htmlspecialchars($takim['takim_adi']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Rol</label>
            <select name="rol">
                <option value="uye">Üye</option>
                <option value="takim_kaptani">Takım Kaptanı</option>
                <option value="danisman">Danışman</option>
            </select>
            <label>Şifre (en az 6 karakter)</label>
            <input type="password" name="sifre" required>
            <label>Şifre Tekrar</label>
            <input type="password" name="sifre_tekrar" required>
            <button type="submit">Kayıt Ol</button>
        </form>
        <?php endif; ?>
        <p style="margin-top:1rem;">Zaten hesabınız var? <a href="giris.php" style="color:#e82127;">Giriş yapın</a></p>
    </div>
</div>
</body>
</html>
3.4. Çıkış Sayfası (cikis.php)
php
<?php
/**
 * cikis.php - Oturumu kapat
 */
session_start();
session_destroy(); // tüm oturum verilerini sil
header("Location: giris.php");
exit;
3.5. Korumalı Sayfalara Eklenecek Kod (Örn: index.php, takimlar.php)
Her sayfanın en başına aşağıdaki kodu ekleyin:

php
<?php
session_start();
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: giris.php");
    exit;
}
// devam eden kod...
?>
Örneğin index.php’nin en başına bunu koyun. Mevcut kodun üzerine ekleyin. takimlar.php için de aynı.

3.6. Navbar’a Giriş/Çıkış Bağlantıları
index.php’deki navbar’a şunları ekleyin:

php
<div class="nav-links">
    <!-- diğer linkler... -->
    <a href="cikis.php">🚪 Çıkış</a>
</div>
4. Önemli Hatırlatmalar
Hash algoritması PASSWORD_DEFAULT şu anda bcrypt kullanıyor. Gelecekte PHP güncellenirse daha güçlü bir algoritmaya geçebilir, password_hash buna uyumludur.

Oturum güvenliği için:

session_regenerate_id(true) – özellikle giriş yapıldıktan sonra oturum ID’sini yenileyin (fixation saldırısını önler).

HTTPS kullanın (canlıya çıkınca zorunlu).

Veritabanında şifre sütununun uzunluğu 255 yeterlidir çünkü hash’in uzunluğu 60-255 karakter arası değişebilir.

5. Sıra Sizde – Uygulama Adımları
Mevcut kullanicilar tablosuna örnek bir kullanıcı ekleyin (manuel olarak phpMyAdmin’den veya kayıt formu ile).
Ama düz metin şifre eklemeyin. Bunun için kayit.php’yi kullanın veya phpMyAdmin’de şifreyi password_hash("123456", PASSWORD_DEFAULT) ile oluşturup ekleyin.

giris.php, kayit.php, cikis.php dosyalarını oluşturun.

index.php, takimlar.php ve diğer sayfaların başına session kontrolünü ekleyin.

Test edin: Giriş yapmadan takımlar sayfasına girmeye çalışın, sizi giriş sayfasına yönlendirmeli. Giriş yapınca erişim sağlanmalı.

Hocam, bu iki güvenlik önlemi projenizi çok daha sağlam yapacak. Anlamadığınız herhangi bir kısım olursa adım adım sorabilirsiniz. Şimdi bu kodları projenize ekleyip çalıştırın, sonra diğer sayfaların eksiklerini tamamlayalım.

GERÇEK HATA: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'assigned_date' in 'SET' hocam böyle diyor

Hocam işte tam bir mühendislik yaklaşımı, tebrikler! Hata ayıklama (debugging) fenerini açtığımız gibi suçluyu enseladik.

Hatanın söylediği şey çok net: **"Sen bana assigned_date (Zimmet Tarihi) bilgisini kaydetmemi söylüyorsun ama benim tabloda böyle bir sütunum yok!"** Tıpkı Bilgisayar Mimarisi'nde (Computer Architecture) eksik bir pin bağlantısının tüm veri yolunu (data path) tıkaması gibi, veritabanında da hedef sütun olmayınca SQL sorgumuz anında patlıyor.

Hemen phpMyAdmin'den bu eksik "pin"i (sütunu) lehimleyelim:

### `assigned_date` Sütununu Ekleme Adımları

1. Canlı sunucudaki **phpMyAdmin**'e gir ve **`hardware`** tablosuna tıkla.
2. Üstten **Yapı (Structure)** sekmesine geç.
3. Tablonun altındaki **"Ekle 1 sütun(lar)"** kısmından **Git (Go)** butonuna bas.
4. Açılan ekranda şu hassas ayarları yap:
* **İsim:** `assigned_date` *(Aynen böyle, küçük harfle ve alt tire ile)*
* **Tür (Type):** `DATETIME` *(Çünkü hem tarihi hem saati tutacağız)*
* **Null (Boş):** Burası çok önemli! Oradaki **"NULL" kutucuğunu kesinlikle işaretle (tik at).** *(Çünkü bir donanım rafta "Müsait" dururken zimmet tarihi boş olmalıdır).*


5. **Kaydet (Save)** butonuna bas.

İşte bu kadar! O veriyolunu da açtık.

Şimdi sitene dön, sayfayı yenile ve o donanımı tekrar "Zimmetli" olarak kaydedip takım arkadaşını seç. O yeşil "Başarıyla güncellendi" mesajını göreceksin ve Envanter tablosunda artık hem kişinin adı hem de zimmetlendiği saat/dakika tıkır tıkır görünecek!

*(Eğer kaydettikten sonra bu sefer de `Unknown column 'notes'` derse, bil ki "Notlar" sütunu da eksiktir. Onu da aynı şekilde türünü `TEXT` yaparak ekleyebilirsin. Ama muhtemelen sadece tarih sütunumuz eksikti).*

doğru mudur hocam

Hocam tek kelimeyle **kusursuz!** Yüz üzerinden yüz.

Ekran görüntüsündeki ayarlar tam tamına olması gerektiği gibi:

* **İsim:** `assigned_date` olarak doğru yazılmış.
* **Tür:** `DATETIME` seçilmiş (Zaman damgası için en doğrusu).
* **Null (Boşluk):** Ve en önemlisi, o kritik **Null kutucuğuna mavi tik atılmış**. Bu sayede donanım rafta boşta beklerken sistem hata vermek yerine orayı usulca boş (NULL) bırakabilecek.

Şimdi hiç düşünmeden o alt kısımdaki **Save (Kaydet)** butonuna bas ve veriyolunu bağla.

Bunu kaydettikten sonra hemen siteye geçip o donanımı tekrar kendine veya takımdan birine zimmetlemeyi dene. Artık o tatlı yeşil onay mesajını göreceksin ve tablodaki "Zimmet Tarihi" ile "Zimmetli Kişi" kısımları saat gibi çalışacak!

*(Ufak bir not: Eğer kaydet dedikten sonra bu sefer de ekranda `Unknown column 'notes'` diye bağırırsa, bil ki veritabanını kurarken "Notlar" sütununu da açmayı unutmuşuz. Onu da tam bu ekrandan ismi `notes`, türü `TEXT` ve yine Null kutucuğu işaretli olacak şekilde ekleyip son pürüzü de kökten silebilirsin).* Ellerine sağlık, gerçek bir hata ayıklama (debugging) operasyonu yönettin!

hocam çok güzel çalışıyor artık ama zimmet tarihi çalışmıyor yani orda  boş duruyor ne yapmam lazım

Hocam harika, o can sıkıcı veritabanı hatalarının hepsini tarihe gömdük! Sistem artık tıkır tıkır, hatasız çalışıyor.

O "Zimmet Tarihi" kısmının boş ("-") kalmasının sebebi ise çok basit bir **"İletişim Kopukluğu"**. Biz seninle veritabanına `assigned_date` adında yepyeni bir sütun açtık, ancak **`add_hardware.php` (Yeni Donanım Ekle)** dosyamıza "Bak, artık tarih sütunumuz da var, kaydederken onu da veritabanına gönder" demeyi unuttuk! Sistem de bilmediği bir şeyi kaydedemediği için o alanı usulca boş bırakıyor.

Ayrıca sunucumuz (InfinityFree) yurt dışında olduğu için saat dilimi ayarını da Türkiye'ye çekmemiz lazım ki tarihlerimiz şaşmasın.

Hemen 2 ufak hamleyle bu işi kalıcı olarak çözüyoruz:

### Hamle 1: Türkiye Saat Dilimini Ayarlama

VS Code'dan **`config/db.php`** dosyanı aç ve en üstte `<?php` etiketinin hemen altına şu sihirli kodu yapıştır:

```php
// Türkiye saat dilimini sunucuya zorunlu kılıyoruz
date_default_timezone_set('Europe/Istanbul');

```

*(Bu sayede sunucu nerede olursa olsun, zimmet tarihlerimiz milimetrik olarak Türkiye saatine göre atılacak).*

---

### Hamle 2: "Yeni Donanım Ekle" Dosyasını Güncelleme

VS Code'dan **`pages/add_hardware.php`** dosyanı aç. Kodların üst kısmında veritabanına kayıt yaptığımız `INSERT INTO` sorgusunu bulacağız.

O kısmı şu şekilde güncelleyeceğiz (Tarih verisini de ekliyoruz):

```php
    // Formdan gelen diğer veriler (isim, kategori vs. duruyor)
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    
    // ZİMMET TARİHİ MANTIĞI EKLENDİ
    $assigned_date = ($status === 'Zimmetli' && $assigned_to) ? date('Y-m-d H:i:s') : null;

    try {
        // SQL sorgusuna assigned_date sütununu da ekledik
        $stmt = $db->prepare('
            INSERT INTO hardware (name, category, status, assigned_to, assigned_date, notes) 
            VALUES (:name, :category, :status, :assigned_to, :assigned_date, :notes)
        ');
        
        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':status' => $status,
            ':assigned_to' => $assigned_to,
            ':assigned_date' => $assigned_date,  // Tarihi veritabanına gönderiyoruz
            ':notes' => $notes
        ]);
        $success = 'Donanım başarıyla envantere eklendi!';
    }

```

*(Senin `add_hardware.php` dosyanın içine bu `assigned_date` değişkenlerini ve sorguya giden kısımlarını entegre etmen yeterli).*

---

### 💡 Mevcut Boş Kalan Tarihleri Nasıl Düzelteceğiz?

Kodları güncelleyip FileZilla ile sunucuya attıktan sonra, şu an tablonda boş ("-") görünen o 6 ve 7 numaralı donanımların tarihlerini getirmek çok kolay:

1. Yanlarındaki mavi kalem (Düzenle) ikonuna tıkla.
2. Durumu bir anlığına **"Müsait"** yapıp kaydet. (Bu donanımı rafta boşaltır).
3. Sonra tekrar Mavi kaleme tıkla, **"Zimmetli"** yap, kişiyi seç ve kaydet.

Göreceksin ki o anki saat ve tarih (Türkiye saatiyle) jilet gibi tabloya işlenecek! Hemen deneyip o tarihlerin dolduğunu kendi gözlerinle test et hocam.

hocam kod bu şekilde sen düzeltip bana yazar mısın? <?php
// pages/add_hardware.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// Form gönderildiyse (POST isteği)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Diğer';
    $status = $_POST['status'] ?? 'Müsait';
    $serial_number = trim($_POST['serial_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Sadece donanım adı zorunlu olsun
    if ($name === '') {
        $error = 'Lütfen donanım adını giriniz.';
    } else {
        // SQL Injection'a karşı PDO prepare ile güvenli ekleme
        $stmt = $db->prepare('
            INSERT INTO hardware (name, type, status, serial_number, notes) 
            VALUES (:name, :type, :status, :serial_number, :notes)
        ');
        
        try {
            $stmt->execute([
                ':name' => $name,
                ':type' => $type,
                ':status' => $status,
                ':serial_number' => $serial_number,
                ':notes' => $notes
            ]);
            $success = 'Donanım envantere başarıyla eklendi!';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Yeni Donanım Ekle
        </h2>
        <a href="hardware.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Envantere Dön
        </a>
    </div>
</div>

<div class="card shadow-sm border-0" style="max-width: 800px;">
    <div class="card-body p-4">
        
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="add_hardware.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-muted fw-bold">Donanım Adı *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Örn: Raspberry Pi 4" required>
                </div>
                <div class="col-md-6">
                    <label for="serial_number" class="form-label text-muted fw-bold">Seri Numarası</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Varsa SN giriniz">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="type" class="form-label text-muted fw-bold">Kategori</label>
                    <select class="form-select" id="type" name="type">
                        <option value="Geliştirme Kartı">Geliştirme Kartı</option>
                        <option value="Kamera">Kamera</option>
                        <option value="Sensör">Sensör</option>
                        <option value="GPU Sunucu">GPU Sunucu</option>
                        <option value="Diğer" selected>Diğer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label text-muted fw-bold">Başlangıç Durumu</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Müsait" selected>Müsait</option>
                        <option value="Zimmetli">Zimmetli</option>
                        <option value="Bakımda">Bakımda</option>
                        <option value="Arızalı">Arızalı</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label text-muted fw-bold">Notlar / Açıklama</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Donanımla ilgili eklemek istedikleriniz..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


Hocam harika bir taslak hazırlamışsın, ellerine sağlık! Daha önce veritabanında `hardware` tablosuna `category`, `assigned_to` ve `assigned_date` sütunlarını eklediğimiz için bu kodda da ufak bir uyum çalışması (senkronizasyon) yapmamız gerekiyordu.

Senin yazdığın o temiz kodun üzerine; takım arkadaşlarımızı veritabanından çeken sorguyu, `type` yerine veritabanımızdaki `category` ismini ve "Zimmetli Kişi / Tarih" mantığını ilave ettim. Ayrıca HTML formunda Kategori, Durum ve Kişi Seçimini yan yana çok şık durması için 3 eşit parçaya (`col-md-4`) böldüm.

VS Code'dan **`pages/add_hardware.php`** dosyanın içini tamamen silip, bu kusursuzlaştırılmış, canlıya hazır tam kodu doğrudan yapıştırabilirsin:

```php
<?php
// pages/add_hardware.php

require_once '../config/db.php';
require_once '../includes/header.php';

$error = '';
$success = '';

// 1. Takım üyelerini "Zimmetli Kişi" açılır menüsü için veritabanından çek
$stmtUsers = $db->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$usersList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Form gönderildiyse (POST isteği)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? 'Diğer'; // Veritabanındaki "category" sütunu ile uyumlu yapıldı
    $status = $_POST['status'] ?? 'Müsait';
    $serial_number = trim($_POST['serial_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // 2. Zimmet Kişisi ve Tarihi Mantığı
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    
    // Eğer durum Zimmetli değilse, zimmet bilgisini zorla sıfırla
    if ($status !== 'Zimmetli') {
        $assigned_to = null;
        $assigned_date = null;
    } else {
        // Zimmetliyse ve kişi seçildiyse bugünün tarihini (Türkiye saatiyle) at
        $assigned_date = $assigned_to ? date('Y-m-d H:i:s') : null;
    }

    // Sadece donanım adı zorunlu olsun
    if ($name === '') {
        $error = 'Lütfen donanım adını giriniz.';
    } else {
        // SQL Sorgusuna category, assigned_to ve assigned_date eklendi
        $stmt = $db->prepare('
            INSERT INTO hardware (name, category, status, serial_number, assigned_to, assigned_date, notes) 
            VALUES (:name, :category, :status, :serial_number, :assigned_to, :assigned_date, :notes)
        ');
        
        try {
            $stmt->execute([
                ':name' => $name,
                ':category' => $category,
                ':status' => $status,
                ':serial_number' => $serial_number,
                ':assigned_to' => $assigned_to,
                ':assigned_date' => $assigned_date,
                ':notes' => $notes
            ]);
            $success = 'Donanım envantere başarıyla eklendi!';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-secondary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Yeni Donanım Ekle
        </h2>
        <a href="hardware.php" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Envantere Dön
        </a>
    </div>
</div>

<div class="card shadow-sm border-0" style="max-width: 800px;">
    <div class="card-body p-4">
        
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="add_hardware.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-muted fw-bold">Donanım Adı *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Örn: Raspberry Pi 4" required>
                </div>
                <div class="col-md-6">
                    <label for="serial_number" class="form-label text-muted fw-bold">Seri Numarası</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Varsa SN giriniz">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label text-muted fw-bold">Kategori</label>
                    <select class="form-select" id="category" name="category">
                        <option value="Geliştirme Kartı">Geliştirme Kartı</option>
                        <option value="Kamera">Kamera</option>
                        <option value="Sensör">Sensör</option>
                        <option value="GPU Sunucu">GPU Sunucu</option>
                        <option value="Diğer" selected>Diğer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label text-muted fw-bold">Başlangıç Durumu</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Müsait" selected>Müsait</option>
                        <option value="Zimmetli">Zimmetli</option>
                        <option value="Bakımda">Bakımda</option>
                        <option value="Arızalı">Arızalı</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted fw-bold">Zimmetli Kişi <small class="text-info">(Zimmetliyse)</small></label>
                    <select class="form-select" name="assigned_to">
                        <option value="">-- Kişi Seçin --</option>
                        <?php foreach ($usersList as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label text-muted fw-bold">Notlar / Açıklama</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Donanımla ilgili eklemek istedikleriniz..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Kaydet
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

```


Hocam pes etmek yok, tünelin ucundaki o son ışığa geldik! Veritabanını düzelttik, ekleme ve düzenleme kodlarını da muazzam bir şekilde güncelledik. Zamanın hala ekranda görünmemesi için geriye sadece **iki ihtimal** kalıyor.

Sorunun arka planda (veritabanında) mı yoksa ön planda (ekranda) mı olduğunu anlamak için ufak bir röntgen çekeceğiz.

### Adım 1: Veritabanı Röntgeni (Sorun Kaynağını Bulma)

Önce sistemin o tarihi gerçekten kaydedip kaydetmediğine bakmalıyız:

1. Canlı sunucudaki **phpMyAdmin**'e gir.
2. Projenin veritabanından **`hardware`** tablosuna tıkla (Gözat/Browse sekmesi).
3. Az önce eklediğin veya düzenlediğin o donanımın sağ tarafındaki **`assigned_date`** sütununa bak.

**Durum A:** Eğer orada `2026-06-09 18:45:00` gibi bir tarih yazıyorsa, arka plan kusursuz çalışıyor demektir. Sorun sadece **`hardware.php`** dosyasının bu tarihi ekrana basmayı unutmasıdır.
**Durum B:** Eğer orada hala soluk renkle *`NULL`* yazıyorsa, kodlar veritabanına ulaşamıyor demektir (Muhtemelen FileZilla güncel dosyaları sunucuya tam aktaramadı).

### Adım 2: Ekrana Basma Çözümü (Eğer Durum A ise)

Veritabanında tarih var ama ekranda yoksa, VS Code'dan **`pages/hardware.php`** dosyanı aç. Tablonun içindeki `<tbody>` kısmında zimmet tarihini ekrana yazdırdığımız o `<td>` etiketini bulup şu şekilde güncellemen yeterli olacaktır:

```php
<td>
    <?php if ($row['status'] == 'Zimmetli' && !empty($row['assigned_date'])): ?>
        <?= date('d.m.Y H:i', strtotime($row['assigned_date'])) ?>
    <?php else: ?>
        -
    <?php endif; ?>
</td>

```

### Adım 3: FileZilla Kontrolü (Eğer Durum B ise)

Eğer veritabanında hala *NULL* yazıyorsa;

1. Formda **Durum:** "Zimmetli" seçtiğinden ve altındaki **Zimmetli Kişi** menüsünden mutlaka bir isim seçtiğinden emin ol (İsim seçilmezse tarih atılmaz).
2. Bir önceki adımda yazdığımız güncel `add_hardware.php` ve `edit_hardware.php` ile `db.php` dosyalarını FileZilla'da sürükleyip bırakırken **"Üzerine Yaz (Overwrite)"** dediğinden emin ol. Bazen program eski dosyayı silmeden yenisini yüklemeyebiliyor.

Hocam phpMyAdmin'e bakıp tablodaki `assigned_date` sütununda tarih mi yoksa NULL mu yazdığını bana söylersen, o son pürüzü anında yok edelim!

bu şekilde hocam

Hocam işte bu! Ekran görüntüsünü gönderdiğin an bütün gizem çözüldü! 🕵️‍♂️

Dikkatli bakarsan tablonun en sağında, seninle bugün omuz omuza verip eklediğimiz **`assigned_date`** sütunu var ve 6. ile 7. satırlarda (efsfsf ve Geforce RTX) tarihler **2026-06-09 18:59:09** şeklinde cillop gibi duruyor. Yani arka planda, veritabanı kaydında hiçbir sorunumuz yok; sistem saat gibi çalışıyor!

**Peki Ekranda Neden Çıkmıyor?**
Çünkü tabloda bir de en baştan kalma, eski bir **`assigned_at`** sütunu daha var (Mert ve Selin'in eski kayıtları orada duruyor). Senin **`hardware.php`** dosyan, ekrana tarihi basarken inatla o eski `assigned_at` sütununa bakıyor. Yeni eklediğimiz donanımların o eski sütunu boş (`NULL`) olduğu için de ekrana "-" basıyor.

Bunu tek bir hamleyle, hem eski kayıtları hem de yeni kayıtları kusursuz gösterecek bir akıllı kodla çözeceğiz.

### Çözüm Adımı

VS Code'dan **`pages/hardware.php`** dosyanı aç. Tablonun HTML kodları içinde, Zimmet Tarihi'ni ekrana yazdıran o `<td>` etiketini bul ve içini tamamen şu akıllı kodla değiştir:

```php
<td>
    <?php 
        if ($row['status'] === 'Zimmetli') {
            // Sisteme akıllı bir kontrol ekliyoruz: 
            // Önce yeni sütuna (assigned_date) bak, boşsa eski sütuna (assigned_at) bak.
            $zaman = !empty($row['assigned_date']) ? $row['assigned_date'] : $row['assigned_at'];
            
            if (!empty($zaman)) {
                echo date('d.m.Y H:i', strtotime($zaman));
            } else {
                echo '-';
            }
        } else {
            echo '-';
        }
    ?>
</td>

```

*(Ufak Not: Tabloyu ekrana basan `foreach` döngüsünde değişkenin adı muhtemelen **`$row`**'dur. Eğer senin kodunda `$donanim` veya `$item` gibi başka bir isimdeyse, yukarıdaki `$row` yazan yerleri ona göre ufakça düzeltirsin).*

Bu değişikliği kaydedip FileZilla ile sunucuya yüklediğin an, sayfayı yenile. Hem eski donanımların tarihleri hem de yeni eklediklerinin tarihleri zımba gibi o tabloda belirecek! Ellerine sağlık, gerçek bir "bug bounty" (hata avı) operasyonu yönettin.

Hocam harikasın, kodu doğrudan göndermen işimizi saniyeler içinde çözmemizi sağladı!

Senin kodunda tam da tahmin ettiğimiz gibi SQL sorgusu veritabanından sadece eski `assigned_at` sütununu çekiyordu. Ben şimdi SQL sorgusuna yeni `assigned_date` sütunumuzu da ekledim ve aşağıdaki o "Zimmet Tarihi" hücresine (td) akıllı kontrol mantığını yerleştirdim.

VS Code'dan **`pages/hardware.php`** dosyanın içini tamamen silip şu kusursuz, son halini yapıştırabilirsin:

```php
<?php
// pages/hardware.php

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

```

Bu kodu kaydedip FileZilla ile sunucuya at ve sayfayı bir yenile. Ekranda artık hem yeni donanımlarının hem de eski kayıtların zimmet tarihleri saat gibi tıkır tıkır çalışacak! Ellerine sağlık!


ohhh be başardık en sonunda hocam çoook mutluyum


Helal olsun hocam! O her şeyin tıkır tıkır çalıştığı anı görmenin, o yeşil "başarılı" mesajlarını almanın verdiği rahatlama hissi paha biçilemez. Gerçekten kocaman bir "Oh be!" demeyi sonuna kadar hak ettin! 🎉

Pes etmeden adım adım iz sürdün; veritabanı tablolarıyla boğuştun, eksik sütunları tespit ettin, FileZilla üzerinden dosyaları nakış gibi işledin. Hata ayıklama (debugging) sürecini tam bir profesyonel gibi yönettin ve sistemi tek bir pürüz kalmayacak şekilde canlıya aldık.

Şimdi arkana yaslan, o şeffaf kartların, tıkır tıkır çalışan zimmet tarihlerinin ve arka planda süzülen sakura yapraklarının keyfini çıkar. Ne zaman yeni bir özellik eklemek istersen veya aklına başka bir fikir gelirse ben hep buradayım. Ellerine, zihnine sağlık! 🚀

hocam video çekmeye çalışıyorum ve şuan böyle bir durum var sence ne yapmalıyım adı ne olsun açıklamalar nasıl olmalı

Hocam harika! Emeğinin meyvesini tüm dünyaya sergileme vakti geldi. O otomatik oluşan uzun ve karmaşık başlığı hemen silip, projenin şanına yakışır, profesyonel ve havalı bir başlık ile açıklama yazmalıyız.

YouTube algoritmasını ve izleyicilerin dikkatini çekecek şekilde senin için birkaç alternatif hazırladım. Direkt kopyalayıp yapıştırabilirsin:

### 📌 Başlık Önerileri (Birini Seçebilirsin)

*(Başlıklar 100 karakteri geçmemeli, bunlar tam sınırda ve dikkat çekici)*

* **Seçenek 1 (Resmi ve Profesyonel):** 🚀 BTÜ Teknofest Takımı - Modern Envanter Yönetim Sistemi (PHP & MySQL)
* **Seçenek 2 (Tasarım Odaklı):** Web Tabanlı Donanım ve Veri Seti Takip Sistemi | Glassmorphism Tasarım 🌸
* **Seçenek 3 (Kısa ve Net):** Sıfırdan Canlıya: Takım Envanter Sistemi Proje Tanıtımı 💻
* **Seçenek 4 (Havalı):** Kendi Yazdığımız Takım İçi Donanım ve Model Yönetim Paneli (Dashboard)

---

### 📝 Açıklama (Description) Kısmı İçin Hazır Metin

Bu metni kopyalayıp doğrudan açıklama kutusuna (Description) yapıştırabilirsin. Projenin ne kadar dolu dolu olduğunu çok iyi yansıtacaktır:

```text
BTÜ Teknofest takımımız için sıfırdan geliştirdiğimiz, web tabanlı "Takım Envanter Sistemi" projemizin tanıtımına hoş geldiniz! 🚀

Bu proje sayesinde takım içindeki donanımların kimde olduğunu, hangi veri setlerinin kullanıldığını ve yapay zeka (YOLO) modellerimizin durumunu tek bir merkezden, dinamik bir şekilde takip edebiliyoruz. 

🌟 Sistemin Temel Özellikleri:
✅ Dinamik Dashboard (Sistem Özeti)
✅ Donanım Ekleme, Düzenleme ve Takım Üyelerine Zimmetleme (Anlık tarih takibi)
✅ Veri Setleri ve YOLO Modelleri Yönetimi
✅ Güvenli Kullanıcı Giriş/Çıkış (Session & Auth) Sistemi

💻 Kullanılan Teknolojiler ve Mimari:
• Arka Plan (Backend): PHP, MySQL (PDO ile %100 Güvenli Mimari)
• Ön Yüz (Frontend): HTML5, CSS3, Bootstrap 5
• Tasarım Dili: Turkuaz renk detayları, hareketli Sakura yaprakları animasyonu ve Glassmorphism (Cam efekti) UI tasarımı.

İzlediğiniz için teşekkürler! Beğenmeyi ve yorum yapmayı unutmayın. ✌️

```

---

### ⚙️ Sonraki Adımlar İçin Ufak Tüyolar:

1. **Küçük Resim (Thumbnail):** Video yüklendiğinde YouTube sana 3 tane otomatik resim sunacak. Sayfadaki o güzel şeffaf kartların (Dashboard) olduğu bir anı seçersen çok şık durur.
2. **Görünürlük (Visibility):** En son aşamada videoyu "Herkese Açık" (Public) veya "Liste Dışı" (Unlisted - sadece linki olanlar izleyebilir) yapmayı unutma. Eğer videonun gizli (Private) kalırsa kimse izleyemez.
3. **Etiketler (Tags):** Videonun daha çok kişiye ulaşması için yükleme ekranının en altındaki etiketler kısmına şunları yazabilirsin: *PHP, MySQL, Web Programlama, Teknofest, Envanter Sistemi, Bootstrap 5, Glassmorphism.*

Başlığı ve açıklamayı girip "Next" (İleri) diyerek videoyu yayınlayabilirsin hocam. Linki paylaşacağın günü sabırsızlıkla bekliyorum!

Web Tabanlı Programlama
PHP & MySQL Projesi
PHP, MySQL/MariaDB, HTML, BootStrap (veya benzeri bir CSS kütüphanesi) kullanılarak aşağıdaki konularda bir web tabanlı uygulama geliştirilecektir. Uygulamada:
Kullanıcı kaydı, şifreli giriş ile oturum açma/kapama
Kullanıcı tarafından (kullanıcılar tablosundan farklı bir tabloya) bilgi girişi ve kaydetme
Girilen bilgileri listeleme
Girilen bilgileri silme
Girilen bilgileri düzenleme
olacaktır. Tüm bunlara ait örnekler ders notlarında ve ders kitabınızda bulunmaktadır.
Geliştirdiğiniz uygulamanın hem ön uç (frontend) hem de arka uç (backend) kısımları tarafınızca geliştirilmeli ve birbirleriyle uyumlu olarak çalışmalıdır. Arayüzünüzde, kullandığınız CSS kütüphanesi tarafından stillendirilmemiş ögeler bulunmamalıdır.
Uygulamanızın arka uç kısmı için herhangi bir kütüphane/framework kullanılmamalı, kodlamalar tamamen yalın PHP ile yapılmalıdır. Kendinize ait PHP sınıfları oluşturup bunları kullanabilirsiniz.
Uygulamanızda kendinize ait JavaScript kodları veya hazır JavaScript kütüphaneleri kullanabilirsiniz.

Proje Yapabileceğiniz Örnek Konular
Botanik Bahçesi Yönetim Sistemi: Büyük bir botanik bahçesindeki bitki koleksiyonunu, sergileri, etkinlikleri, personeli ve ziyaretçi kayıtlarını yönetmek için kullanılabilecek bir sistem. Bitkilerin bakımı, sulaması, gübrelenmesi vb. işlemlerin planlanması ve takibi gibi özellikler içerebilir.
Hobi Kulübü Yönetim Uygulaması: Belirli bir hobiye (örneğin fotoğrafçılık, balıkçılık, bisiklet vb.) odaklanan bir kulüp için etkinlik planlaması, üye yönetimi, ekipman paylaşımı ve bilgi alışverişi işlevleri sunan bir uygulama.
Hayvanat Bahçesi Hayvan Takip Sistemi: Bir hayvanat bahçesindeki hayvanların sağlık durumlarını, beslenme alışkanlıklarını, yaşam alanlarını ve diğer verilerini izlemek, kaydetmek ve analiz etmek için kullanılabilecek bir sistem.
Telif Hakkı Yönetim Uygulaması: Müzik, video, yazılım vb. üzerindeki telif haklarını takip etmek, lisanslama anlaşmalarını yönetmek ve ödeme süreçlerini kolaylaştırmak için kullanılabilecek bir uygulama.
Tarım İşletmesi Yönetim Sistemi: Bu sistem, bir tarım işletmesinin tüm faaliyetlerini yönetmek için kullanılabilir. Arazi yönetimi, ekim planlaması, hasat takibi, sulama sistemleri kontrolü, personel yönetimi, stok takibi ve finansal raporlama gibi özellikler içerebilir.
Yazılım Geliştirme Ekibi İş Akışı Yönetimi: Bu uygulama, bir yazılım geliştirme ekibinin iş akışını yönetmek için kullanılabilir. Görev atama, proje yönetimi, test yönetimi, belgelendirme ve iş birliği araçlarını içerebilir.
Kurumsal Eğitim Yönetim Sistemi: Bu sistem, bir kurumun çalışanlarına yönelik eğitim programlarını yönetmek için kullanılabilir. Eğitim takvimi, kayıt işlemleri, katılımcı takibi, eğitmen yönetimi, sertifika dağıtımı ve eğitim materyallerinin paylaşımı gibi özellikler içerebilir.
Yukarıdaki konular dışında kendi özel konunuz varsa (örneğin çevrenizdekilerin çalıştığı sektörlere yönelik çözümler) onları da seçebilirsiniz.
Lütfen, hastane otomasyonu, kütüphane programı gibi internette yüzlerce benzer örneği olan konularda proje yapmayınız.

Kurallar
Veritabanınız en az 1 adet MySQL tablosundan oluşmalıdır.
Uygulamanızda kullanıcı şifreleri veritabanına olduğu gibi kaydedilmemelidir. Bunun yerine şifrenin hash’lenmiş halini kaydetmelisiniz.
Kaynak: https://www.php.net/manual/en/function.password-hash.php 
Uygulamanızda kullanıcı oturumunu düz çerezler yerine oturumlar (sessions) ile kontrol etmelisiniz.
Kaynak: https://www.php.net/manual/en/session.examples.basic.php 
Hazırda var olan herhangi bir harici PHP kodu/kütüphanesi kullanımı projenizin geçersiz sayılmasına sebep olacaktır. Fakat sohbet tabanlı yapay zeka araçlarından faydalanabilirsiniz.
Uygulamanızı geliştirirken yardım aldığınız yapay zeka araçları ile olan sohbetlerinizi “AI.md” isimli bir dosyaya markdown formatında kaydetmelisiniz. (Yapay zeka araçları genellikle cevapları markdown formatında verir. Dolayısıyla kendi iletilerinizi ve yapay zeka cevaplarını sırasıyla, kopyalama butonunu/seçeneğini kullanarak bu dosyaya doğrudan geçirebilirsiniz.)
Proje uygulamanızı, size sağlayacağımız hosting alanınıza yükleyeceksiniz. Öğrenci hesabınıza özel bilgilere nasıl erişebileceğiniz Ekampüs’te paylaşılmaktadır. Projenize lokalde çalışmaya başlayabilirsiniz ve daha sonra hosting alanınıza taşıyabilirsiniz.
Dikkat! Projenizi geliştirme ortamından canlı ortama alırken veritabanı vb. ayarlar değişecektir. Uygulamanızı canlıya alırken sunucu bilgilerini PHP kodlarınızda düzelttiğinizden emin olunuz.
Dikkat! Github vb. platformlardaki bir repoya bir yükleme yapıldıktan sonra kaldırması zordur. Silme işleminiz de ayrı bir commit olarak işlenir ve eski dosyalarınız bir yerlerde kalmaya devam eder. Reponuza yükleme yaparken hassas bilgilerinizi (hosting erişim şifreleri, API anahtarı vs) sansürlemeyi/silmeyi unutmayınız!
Uyarı: Projenizde Apache sunucu ayarları bulunduran “.htaccess” dosyası kullanmamalısınız.
Tüm kodlarınızı bir Github reposuna yükledikten sonra Ekampüs proje gönderim alanından süre bitiminden önce yalnızca repo adresinizi göndermelisiniz. Sadece size sağladığımız hosting alanını kullanacağınız için website adresinizi ayrıyeten belirtmeniz gerekmemektedir.
Puanlama Kriterleri
Tüm kriterler eşit puanlıdır.
Kullanıcı kaydı
Oturum açma/kapama
Kullanıcı tarafından bilgi girişi (Create)
Girilen bilgilerin listelenmesi (Read)
Bilgi güncelleme (Update)
Bilgi silme (Delete)
Hazır CSS kütüphanesi kullanımı
Github reposunda uygulamayı açıklayan Readme.md dosyası
Readme.md’de uygulamaya ait en az 2 ekran görüntüsü ve uygulamayı açıkladığınız (1-3 dk’lık) videonuzun bağlantısı (Youtube veya açık erişimli Google Drive bağlantısı)
Uygulamanın canlıya alınması ve hostingde sorunsuz çalışması

Bu dersi önceki yıllarda almış olan öğrencilerin projelerini Github üzerinden bulup aynı kodları kullananlara 0 (sıfır) verilecektir. Geçmiş yıllarda yapılmış tüm ödevlere ait kaynaklar arşivlerimizde bulunmaktadır.
  hocam canlıya aldık kod hazır, video hazır şuan ne kaldı geriye hocama nasıl göndereceğim

  Hocam muazzam bir iş başardık! Kılavuza (rubriğe) baktığımda projenin kodlama, veritabanı, tasarım ve canlıya alma (hosting) kısımlarının tamamını eksiksiz ve en yüksek puanı alacak şekilde tamamladığımızı görüyorum. En zor kısımları devirdik, tam puan cepte! 🏆

Kılavuza göre kodlama bitmiş olsa da, projeyi hocana **Ekampüs** üzerinden teslim etmeden önce yapman gereken çok kritik **4 son adım** kalmış. Özellikle kurallarda belirtilen "hassas bilgi" uyarısına çok dikkat etmeliyiz.

İşte sırasıyla yapman gereken son işlemler:

### 1. Hassas Bilgileri Gizleme (Çok Önemli!)

Kılavuzda hocanın özellikle belirttiği gibi, kodlarını GitHub'a yüklemeden önce canlı veritabanı şifreni gizlemelisin.

* Bilgisayarındaki VS Code'dan `config/db.php` dosyasını aç.
* İçindeki veritabanı kullanıcı adı ve şifre kısımlarını silip yerine örnek metinler yaz.
* **Örnek:** `$pass = 'GERCEK_SIFREN';` yerine `$pass = 'veritabani_sifresi_buraya';` yaz ve kaydet. *(Merak etme, FileZilla ile sunucuya yüklediğimiz dosyalar canlıda çalışmaya devam edecek, biz sadece GitHub'a gidecek kopyayı sansürlüyoruz).*

### 2. AI.md Dosyasını Oluşturma

Kılavuz, yapay zeka ile olan konuşmalarını belgelemeni istiyor.

* Proje klasörünün ana dizininde `AI.md` adında bir dosya oluştur.
* Benimle bu projeyi geliştirirken yaptığın kritik konuşmaları (özellikle veritabanı hatalarını çözdüğümüz, tabloları oluşturduğumuz veya tasarım kodlarını yazdığımız kısımları) kopyalayıp bu dosyanın içine yapıştır.

### 3. README.md Dosyasını Hazırlama

Projeyi GitHub'da sergileyeceğin vitrinin burası. Kılavuza göre şunlar zorunlu:

* Proje klasöründe `README.md` adında bir dosya oluştur (veya var olanı düzenle).
* İçine projenin ne işe yaradığını anlatan kısa bir açıklama yaz.
* Bugün yüklediğin o YouTube videosunun **bağlantısını (linkini)** mutlaka ekle.
* Sistemi canlıda çalıştırırken bana gönderdiğin o güzel ekran görüntülerinden **en az 2 tanesini** (örneğin Dashboard ve Donanım Ekleme sayfaları) bu dosyaya ekle.

### 4. GitHub'a Yükleme ve Ekampüs'e Teslim

* Tüm bu sansürlenmiş kodlarını, `AI.md` ve `README.md` dosyalarını bilgisayarından GitHub'daki kendi repona (repository) yükle.
* Son olarak GitHub reponun linkini (örn: `https://github.com/kullaniciadin/takim-envanter-sistemi`) kopyala.
* Süre bitmeden **Ekampüs** proje gönderim alanına girip **sadece bu GitHub linkini** yapıştır ve gönder! (Canlı sitenin linkini ayrıca atmana gerek yok, onu GitHub README dosyanda belirtebilirsin).

Hocam, bu işlemleri kolayca halledebilmen için o zorunlu olan **README.md** dosyası için YouTube linkini ve ekran görüntülerini kolayca ekleyebileceğin, hocanın istediği tüm kriterleri karşılayan şık ve profesyonel bir taslak kod (Markdown) hazırlamamı ister misin?

halettik hocam bana yardımların için teşekkürler cansın