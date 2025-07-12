# Loli Shop - Online Shopping Platform

Një platform modern për dyqan online i ndërtuar me PHP dhe MySQL.

## Karakteristikat

- 🛒 Sistem i plotë për dyqan online
- 👥 Sistemi i përdoruesve (klientë dhe admin)
- 📦 Menaxhimi i produkteve dhe kategorive
- 🛍️ Shporta dhe sistemi i porositë
- 📱 Dizajn responsiv
- 🔒 Sistem i sigurt autentifikimi
- 💳 Panel admin për menaxhim

## Teknologjitë e Përdorura

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6
- **Server**: XAMPP (Apache + MySQL)

## Instalimi

### 1. Përgatitja e serverit

1. Shkarkoni dhe instaloni [XAMPP](https://www.apachefriends.org/)
2. Startoni Apache dhe MySQL nga XAMPP Control Panel

### 2. Krijimi i databazës

1. Hapni `http://localhost/phpmyadmin`
2. Krijoni një databazë të re me emrin `loli_shop`
3. Ekzekutoni SQL skriptet e mëposhtme:

```sql
CREATE TABLE perdoruesit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emri VARCHAR(100),
    emaili VARCHAR(100) UNIQUE,
    fjalekalimi VARCHAR(255),
    roli ENUM('klient', 'admin') DEFAULT 'klient'
);

CREATE TABLE kategorite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emri VARCHAR(100)
);

CREATE TABLE produktet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emri VARCHAR(100),
    pershkrimi TEXT,
    cmimi DECIMAL(10,2),
    foto VARCHAR(255),
    kategoria_id INT,
    FOREIGN KEY (kategoria_id) REFERENCES kategorite(id) ON DELETE SET NULL
);

CREATE TABLE shporta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perdoruesi_id INT,
    produkti_id INT,
    sasia INT DEFAULT 1,
    FOREIGN KEY (perdoruesi_id) REFERENCES perdoruesit(id) ON DELETE CASCADE,
    FOREIGN KEY (produkti_id) REFERENCES produktet(id) ON DELETE CASCADE
);

CREATE TABLE porosite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perdoruesi_id INT,
    cmimi_total DECIMAL(10,2),
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statusi ENUM('ne_pritje', 'konfirmuar', 'derguar', 'anuluar') DEFAULT 'ne_pritje',
    FOREIGN KEY (perdoruesi_id) REFERENCES perdoruesit(id) ON DELETE CASCADE
);

CREATE TABLE porosia_produktet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    porosia_id INT,
    produkti_id INT,
    sasia INT,
    cmimi DECIMAL(10,2),
    FOREIGN KEY (porosia_id) REFERENCES porosite(id) ON DELETE CASCADE,
    FOREIGN KEY (produkti_id) REFERENCES produktet(id) ON DELETE CASCADE
);
```

### 3. Instalimi i projektit

1. Shkarkoni ose klononi projektin në `C:\xampp\htdocs\lolishop`
2. Sigurohuni që konfigurimi i databazës në `config/database.php` është i saktë:
   ```php
   $host = 'localhost';
   $dbname = 'loli_shop';
   $username = 'root';
   $password = '';
   ```

### 4. Testimi

1. Hapni shfletuesin dhe shkoni te `http://localhost/lolishop`
2. Regjistrohuni si përdorues i ri
3. Për të krijuar një admin, ndryshoni `roli` në databazë nga 'klient' në 'admin'

## Struktura e Projektit

```
lolishop/
├── admin/                  # Panel admin
│   ├── index.php          # Dashboard admin
│   └── ...
├── api/                   # API endpoints
│   └── add-to-cart.php    # AJAX për shportë
├── assets/                # Assets (CSS, JS, Images)
│   ├── css/
│   │   ├── style.css      # Stilet kryesore
│   │   └── admin.css      # Stilet e admin
│   └── js/
│       └── script.js      # JavaScript
├── config/                # Konfigurimi
│   └── database.php       # Lidhja me databazë
├── includes/              # Includes
│   ├── header.php         # Header i faqes
│   ├── footer.php         # Footer i faqes
│   └── functions.php      # Funksionet e përgjithshme
├── index.php              # Faqja kryesore
├── login.php              # Faqja e hyrjes
├── register.php           # Faqja e regjistrimit
├── cart.php               # Shporta
├── logout.php             # Dalje
└── README.md              # Ky fajl
```

## Përdorimi

### Për Klientët

1. **Regjistrimi**: Krijoni një llogari të re
2. **Shfletimi**: Shikoni produktet në faqen kryesore
3. **Shtimi në Shportë**: Klikoni butonin "Shto në Shportë"
4. **Porosia**: Shkoni te shporta dhe vazhdoni me pagesat

### Për Admin

1. **Hyrja**: Hyni me llogari admin
2. **Dashboard**: Shikoni statistikat në panel admin
3. **Menaxhimi**: Shtoni/editoni produkte, kategori, dhe porosidata

## Zhvillimi i Mëtejshëm

### Hapat e ardhshëm:

1. **Sistemi i Pagesave**: Integrimi i PayPal, Stripe
2. **Dërgesat**: Sistemi i adresave dhe llogaritja e kostove
3. **Filtrimi**: Filtrat për produkte sipas çmimeve, kategorive
4. **Kërkimi**: Motor i avancuar kërkimi
5. **Raporte**: Raporte të hollësishme për admin
6. **Emaile**: Konfirmimet dhe njoftimet përmes email
7. **Optimizimi**: Caching dhe optimizimi i performancës

### Modulet për tu shtuar:

- `products.php` - Lista e plotë e produkteve
- `categories.php` - Menaxhimi i kategorive
- `checkout.php` - Procesi i pagesës
- `profile.php` - Profili i përdoruesit
- `orders.php` - Historia e porosive

## Probleme të Mundshme

### Gabimet e shpeshta:

1. **Gabim 500**: Kontrolloni që PHP është i aktivizuar dhe databaza është e lidhur
2. **Imazhet nuk ngarkojnë**: Sigurohuni që direktoria `uploads/` ekziston dhe ka leje shkrimeje
3. **Session issues**: Kontrolloni që `session_start()` është thirrur

### Debug:

- Aktivizoni error reporting në `config/database.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```

## Siguria

- Fjalëkalimet janë hash-uar me `password_hash()`
- Input sanitization me `htmlspecialchars()`
- Prepared statements për SQL queries
- Session management i sigurt

## Kontributi

Mund të kontribuoni duke:
1. Raportuar bugs
2. Sugjeruar përmirësime
3. Shtuar karakteristika të reja

## Licensa

Ky projekt është open-source dhe mund të përdoret lirshëm për qëllime edukative dhe komerciale.

---

**Autor**: Loli Shop Team  
**Data**: 2025  
**Versioni**: 1.0.0
