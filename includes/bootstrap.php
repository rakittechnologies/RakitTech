<?php
declare(strict_types=1);
$configPath = dirname(__DIR__) . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit('Konfigurasi belum tersedia. Salin config.example.php menjadi config.php dan isi kredensial database.');
}
$config = require $configPath;
date_default_timezone_set($config['timezone'] ?? 'Asia/Jakarta');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('ruangweb_admin');
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Strict',
    'path' => '/',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

try {
    $pdo = new PDO(
        'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa pengaturan database pada config.php.');
}
// Tambahkan kolom metode penjualan pada instalasi lama tanpa mengganggu data portofolio.
try {
    $projectColumns = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('sales_method', $projectColumns, true)) {
        $pdo->exec("ALTER TABLE projects ADD COLUMN sales_method VARCHAR(120) NOT NULL DEFAULT 'Project-Based' AFTER website_type");
    }
} catch (PDOException $migrationError) { /* Jika hosting membatasi ALTER TABLE, jalankan migration_portfolio_sales_method.sql. */ }
// Kolom status portofolio: membedakan demo, proyek internal, dan proyek yang digunakan klien.
try { $pdo->exec("ALTER TABLE projects ADD COLUMN project_status VARCHAR(80) NOT NULL DEFAULT 'Demo / contoh konsep' AFTER sales_method"); } catch (PDOException $e) { /* Kolom sudah tersedia atau jalankan migration_portfolio_status.sql. */ }
// Pastikan kolom ID proyek benar-benar AUTO_INCREMENT pada database lama.
// Beberapa instalasi lama pernah menyimpan ID=0 sehingga tombol edit/hapus tidak dapat bekerja.
try {
    $idMetaStmt = $pdo->prepare("SELECT COLUMN_TYPE, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'id' LIMIT 1");
    $idMetaStmt->execute();
    $idMeta = $idMetaStmt->fetch();
    if ($idMeta && stripos((string)$idMeta['EXTRA'], 'auto_increment') === false) {
        $pkStmt = $pdo->query("SHOW KEYS FROM projects WHERE Key_name = 'PRIMARY'");
        $primaryKeys = $pkStmt->fetchAll();
        $primaryOnId = count($primaryKeys) === 1 && ($primaryKeys[0]['Column_name'] ?? '') === 'id';
        $zeroCount = (int)$pdo->query('SELECT COUNT(*) FROM projects WHERE id = 0')->fetchColumn();
        if ($zeroCount === 1) {
            $newId = (int)$pdo->query('SELECT COALESCE(MAX(id), 0) + 1 FROM projects')->fetchColumn();
            if ($newId < 1) $newId = 1;
            // Pindahkan relasi galeri terlebih dahulu agar gambar proyek tetap terhubung.
            $rel = $pdo->prepare('UPDATE project_images SET project_id = ? WHERE project_id = 0');
            try { $rel->execute([$newId]); } catch (PDOException $ignored) { /* Tabel galeri mungkin belum dibuat pada instalasi awal. */ }
            $fix = $pdo->prepare('UPDATE projects SET id = ? WHERE id = 0');
            $fix->execute([$newId]);
        }
        if (!$primaryOnId && !$primaryKeys) {
            $pdo->exec('ALTER TABLE projects ADD PRIMARY KEY (id)');
        }
        $pdo->exec('ALTER TABLE projects MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
    }
} catch (PDOException $e) {
    // Jangan menghentikan halaman publik/admin jika user database tidak memiliki hak migrasi.
    // Operasi simpan di bawah akan menampilkan pesan yang lebih jelas bila ID belum valid.
}

// Pastikan tabel paket layanan tersedia agar instalasi lama dapat diperbarui tanpa menghapus data.
$pdo->exec("CREATE TABLE IF NOT EXISTS service_packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('Website Statis','Website Dinamis') NOT NULL DEFAULT 'Website Statis',
    name LONGTEXT NOT NULL,
    description LONGTEXT NOT NULL,
    price BIGINT UNSIGNED NOT NULL DEFAULT 0,
    discount_price BIGINT UNSIGNED DEFAULT NULL,
    features LONGTEXT NOT NULL,
    ribbon LONGTEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_order (is_active, category, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
// Kolom diskon dan detail layanan opsional. Aman untuk database yang sudah berjalan.
foreach ([
    'discount_price' => 'BIGINT UNSIGNED NULL DEFAULT NULL',
    'full_description' => 'LONGTEXT NULL',
    'suitable_for' => 'LONGTEXT NULL',
    'work_duration' => 'VARCHAR(255) NULL',
    'revision_terms' => 'LONGTEXT NULL',
    'included_items' => 'LONGTEXT NULL',
    'excluded_items' => 'LONGTEXT NULL',
] as $column => $definition) {
    try { $pdo->exec("ALTER TABLE service_packages ADD COLUMN {$column} {$definition}"); } catch (PDOException $e) { /* Kolom sudah ada atau akun database tidak memiliki izin ALTER. */ }
}
// Perbesar kolom konten agar teks tidak terpotong oleh batas VARCHAR/TEXT lama.
foreach ([
    'name' => 'LONGTEXT NOT NULL',
    'description' => 'LONGTEXT NOT NULL',
    'features' => 'LONGTEXT NOT NULL',
    'ribbon' => 'LONGTEXT NULL',
] as $column => $definition) {
    try { $pdo->exec("ALTER TABLE service_packages MODIFY COLUMN {$column} {$definition}"); } catch (PDOException $e) { /* Jika akun DB tidak memiliki izin ALTER, kolom lama tetap berlaku. */ }
}
$packageCount = (int)$pdo->query('SELECT COUNT(*) FROM service_packages')->fetchColumn();
if ($packageCount === 0) {
    $defaults = [
        ['Website Statis','Landing Page','Halaman promosi terarah untuk memperkenalkan satu produk, jasa, atau kampanye dan mendorong pengunjung menghubungi bisnis.','750000',"Desain satu halaman dengan struktur yang jelas
Hingga 5–7 bagian konten sesuai kebutuhan
Tampilan responsif untuk HP, tablet, dan desktop
Tombol WhatsApp atau CTA utama
Formulir kontak sederhana bila diperlukan
SEO dasar: title, description, dan struktur heading
Optimasi gambar dan performa dasar",null,'Halaman promosi yang fokus pada satu tujuan, seperti mendapatkan prospek, memperkenalkan layanan, atau mengarahkan pengunjung ke WhatsApp.','UMKM, promosi produk baru, jasa profesional, event, dan kampanye iklan','5–10 hari kerja setelah materi lengkap','Termasuk 2 kali revisi minor pada desain/konten sebelum final; perubahan ruang lingkup dapat dikenakan biaya tambahan.',"Desain dan implementasi halaman
Pemasangan konten yang disediakan klien
Pengaturan tampilan responsif
Panduan singkat pengelolaan konten statis","Domain dan hosting berbayar jika tidak tercantum pada penawaran
Penulisan copywriting profesional dan produksi foto/video
Integrasi berbayar atau fitur di luar ruang lingkup",10],
        ['Website Statis','Company Profile','Website profil perusahaan yang menyajikan identitas, layanan, keunggulan, portofolio, dan kontak bisnis secara profesional.','900000',"Hingga 5 halaman utama
Beranda, Tentang, Layanan, Portofolio, dan Kontak
Tampilan responsif untuk HP, tablet, dan desktop
Integrasi WhatsApp, peta, dan media sosial sesuai kebutuhan
Formulir kontak sederhana
SEO dasar dan optimasi performa
Struktur konten yang mudah dipahami",'Pilihan populer','Membangun kepercayaan dengan informasi perusahaan yang rapi, konsisten, dan mudah ditemukan oleh calon pelanggan.','Perusahaan, UMKM, konsultan, kontraktor, agensi, komunitas, dan penyedia jasa','7–14 hari kerja setelah materi lengkap','Termasuk 2 kali revisi minor per tahap desain/konten yang disepakati; perubahan besar atau penambahan halaman dihitung terpisah.',"Desain website sesuai identitas brand
Hingga 5 halaman sesuai paket
Pemasangan teks dan gambar dari klien
Pengaturan tautan kontak dan media sosial
SEO dasar","Pembuatan logo atau identitas visual baru
Penulisan konten dari nol dan sesi foto
Fitur login, dashboard, transaksi, atau sistem khusus
Biaya domain dan hosting berbayar jika tidak tercantum",20],
        ['Website Statis','Katalog Produk','Katalog online untuk menampilkan daftar produk atau jasa lengkap dengan foto, deskripsi, kategori, dan jalur pemesanan.','1000000',"Halaman katalog produk atau layanan
Kartu produk dengan foto, nama, dan deskripsi
Kategori produk sederhana
Halaman atau bagian detail produk sesuai lingkup
Tombol pemesanan melalui WhatsApp
Tampilan responsif
SEO dasar dan optimasi gambar",null,'Memudahkan calon pelanggan menjelajahi produk sebelum bertanya atau melakukan pemesanan, tanpa harus langsung membangun sistem toko online penuh.','Toko retail, pengrajin, kuliner, fashion, supplier, dan penyedia jasa dengan banyak pilihan','7–14 hari kerja setelah materi produk lengkap','Termasuk 2 kali revisi minor; perubahan struktur katalog atau penambahan data produk di luar kesepakatan dapat dikenakan biaya.',"Tata letak katalog dan kategori sederhana
Pemasangan data awal produk sesuai jumlah yang disepakati
Tautan pemesanan WhatsApp
Tampilan untuk perangkat mobile","Keranjang dan checkout otomatis
Manajemen stok atau pesanan melalui dashboard
Input produk massal di luar jumlah kesepakatan
Integrasi pembayaran dan ongkir otomatis",30],
        ['Website Dinamis','Website Bisnis + Admin','Website bisnis dengan dashboard admin agar tim dapat memperbarui konten atau data tertentu tanpa mengubah kode secara langsung.','2500000',"Halaman publik responsif sesuai kebutuhan
Login admin dengan pembatasan akses dasar
Dashboard pengelolaan konten/data yang disepakati
Tambah, edit, dan hapus data sesuai modul
Validasi formulir dan notifikasi status dasar
Struktur database untuk fitur yang disepakati
SEO dasar untuk halaman publik",'Dengan dashboard admin','Menggabungkan tampilan website publik dengan panel pengelolaan agar pembaruan informasi bisnis lebih praktis dan terkontrol.','Bisnis yang rutin memperbarui layanan, berita, katalog sederhana, profil, atau informasi operasional','14–25 hari kerja setelah alur dan materi disetujui','Termasuk 2 kali revisi minor pada alur dan tampilan; perubahan alur kerja, modul, atau hak akses setelah disetujui dapat mengubah biaya dan durasi.',"Website publik dan dashboard admin sesuai spesifikasi
Modul pengelolaan yang tercantum dalam penawaran
Pengujian fungsi utama
Panduan penggunaan dasar","Aplikasi mobile native
Integrasi eksternal berbayar
Audit keamanan khusus atau infrastruktur skala besar
Modul yang tidak tercantum dalam ruang lingkup",10],
        ['Website Dinamis','Sistem Booking','Sistem pemesanan jadwal online untuk membantu pelanggan memilih layanan dan waktu, serta membantu admin mengelola reservasi.','3000000',"Formulir booking online
Pilihan layanan dan jadwal sesuai aturan bisnis
Penyimpanan data reservasi
Status booking dan pengelolaan oleh admin
Validasi jadwal dasar untuk mengurangi benturan
Tampilan responsif untuk pelanggan dan admin
Notifikasi atau integrasi tambahan sesuai kesepakatan",null,'Membuat proses reservasi lebih teratur, mengurangi pencatatan manual, dan memudahkan tim memantau status pemesanan.','Salon, klinik non-medis, studio, penyewaan, konsultasi, kursus, dan layanan berbasis jadwal','18–30 hari kerja setelah alur booking disepakati','Termasuk 2 kali revisi minor pada alur yang telah disepakati; perubahan aturan jadwal atau penambahan integrasi dihitung sebagai pengembangan tambahan.',"Form booking dan tampilan status
Dashboard admin untuk mengelola reservasi
Pengaturan layanan dan slot sesuai spesifikasi
Pengujian alur booking utama","Biaya layanan SMS/WhatsApp/email pihak ketiga
Pembayaran online otomatis kecuali disepakati
Sinkronisasi kalender eksternal atau multi-cabang kompleks
Migrasi data lama dalam jumlah besar",20],
        ['Website Dinamis','Toko Online','Toko online untuk menampilkan produk, mengelola keranjang dan pesanan, serta menyiapkan alur pembayaran dan pengiriman sesuai kebutuhan bisnis.','5000000',"Katalog produk dan kategori
Keranjang belanja dan checkout
Data pelanggan dan pesanan
Dashboard admin untuk produk dan transaksi
Status pesanan dan pembayaran
Integrasi pembayaran sesuai penyedia yang disepakati
Integrasi ongkir/pengiriman sesuai layanan yang tersedia
Tampilan responsif dan SEO dasar",'Fitur terlengkap','Solusi penjualan online dengan alur belanja yang lebih lengkap, dari penelusuran produk hingga pengelolaan pesanan oleh admin.','Retail, brand lokal, fashion, perlengkapan, produk rumahan, dan bisnis yang ingin menerima pesanan online','25–45 hari kerja setelah data produk dan aturan toko lengkap','Termasuk 2 kali revisi minor pada tampilan dan alur yang disetujui; perubahan besar pada checkout, promosi, atau integrasi memerlukan penyesuaian estimasi.',"Katalog dan kategori produk
Keranjang dan checkout sesuai ruang lingkup
Dashboard pengelolaan produk dan pesanan
Konfigurasi metode pembayaran/pengiriman yang disepakati
Pengujian alur pembelian utama","Biaya transaksi penyedia pembayaran dan ongkir
Langganan layanan pihak ketiga
Aplikasi mobile native
ERP, akuntansi, atau integrasi khusus yang tidak tercantum",30],
    ];
    $seed = $pdo->prepare('INSERT INTO service_packages (category,name,description,price,features,ribbon,full_description,suitable_for,work_duration,revision_terms,included_items,excluded_items,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($defaults as $row) $seed->execute($row);
}


// Pengaturan tampilan website disimpan di database agar dapat diubah dari panel admin.
$pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
try { $pdo->exec("ALTER TABLE projects ADD COLUMN image_path VARCHAR(500) NULL AFTER demo_url"); } catch (PDOException $e) {
    // Kolom image_path sudah ada pada instalasi yang pernah diperbarui.
}
// Tambahan informasi detail portofolio; migrasi aman untuk database yang sudah digunakan.
foreach ([
    'advantages' => "TEXT NULL",
    'demo_access' => "TEXT NULL",
    'notes' => "TEXT NULL",
    'system_requirements' => "TEXT NULL",
] as $column => $definition) {
    try { $pdo->exec("ALTER TABLE projects ADD COLUMN {$column} {$definition}"); } catch (PDOException $e) { /* Kolom sudah tersedia. */ }
}
// Kolom teks proyek memakai LONGTEXT agar tidak dibatasi 500/5.000 karakter.
foreach ([
    'title' => 'LONGTEXT NOT NULL',
    'category' => 'LONGTEXT NULL',
    'description' => 'LONGTEXT NOT NULL',
    'demo_url' => 'LONGTEXT NOT NULL',
    'features' => 'LONGTEXT NULL',
    'advantages' => 'LONGTEXT NULL',
    'demo_access' => 'LONGTEXT NULL',
    'notes' => 'LONGTEXT NULL',
    'system_requirements' => 'LONGTEXT NULL',
] as $column => $definition) {
    try { $pdo->exec("ALTER TABLE projects MODIFY COLUMN {$column} {$definition}"); } catch (PDOException $e) { /* Instalasi tanpa izin ALTER akan tetap berjalan dengan skema lama. */ }
}
// Galeri portofolio: maksimal 9 gambar per proyek.
$pdo->exec("CREATE TABLE IF NOT EXISTS project_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_images (project_id, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
// Migrasikan gambar tunggal yang sudah ada agar tetap tampil sebagai gambar galeri.
$pdo->exec("INSERT INTO project_images (project_id, image_path, sort_order)
    SELECT p.id, p.image_path, 0 FROM projects p
    WHERE p.image_path IS NOT NULL AND p.image_path <> ''
    AND NOT EXISTS (SELECT 1 FROM project_images pi WHERE pi.project_id = p.id)");
$siteSettings = [];
foreach ($pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll() as $setting) {
    $siteSettings[$setting['setting_key']] = $setting['setting_value'];
}
function setting(string $key, ?string $fallback = null): ?string {
    global $siteSettings, $config;
    if (array_key_exists($key, $siteSettings)) return $siteSettings[$key];
    if ($key === 'site_name') return $config['site_name'] ?? $fallback;
    // Compatibility for existing installs: use the old config value until an admin saves one.
    if ($key === 'whatsapp_number') return $config['whatsapp_number'] ?? $fallback;
    return $fallback;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Silakan kembali dan muat ulang halaman.');
    }
}
function require_admin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
function normalize_http_url(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    // Pengguna boleh memasukkan domain/path tanpa protokol; gunakan HTTPS secara default.
    if (str_starts_with($url, '//')) $url = 'https:' . $url;
    elseif (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $url)) $url = 'https://' . $url;
    return $url;
}
function safe_http_url(string $url): bool {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    $host = (string)parse_url($url, PHP_URL_HOST);
    return in_array($scheme, ['http', 'https'], true) && $host !== '';
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function take_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
