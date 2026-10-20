# RuangWeb — Paket Shared Hosting

Paket ini berisi landing page responsif berbasis HTML/CSS, PHP, dan MySQL untuk mengelola katalog portofolio secara aman di sisi server.

## Fitur
- Landing page responsif untuk HP, tablet, dan desktop.
- Layanan Website Statis dan Website Dinamis.
- CTA konsultasi/pemesanan WhatsApp.
- Katalog proyek dari database.
- Panel admin untuk mengelola portofolio: tambah, edit, hapus, status publik/draft, dan urutan tampil.
- Menu **Layanan, Paket & Harga** untuk tambah/edit/hapus paket, mengatur harga, kategori, deskripsi, fitur, label khusus, status aktif, dan urutan tampil.
- Paket aktif ditampilkan otomatis pada landing page dan pilihan paket di formulir konsultasi. Tabel paket dibuat otomatis saat aplikasi dijalankan; `database.sql` juga sudah menyertakan skemanya.
- Pengunjung hanya bisa melihat proyek yang dipublikasikan dan membuka URL demo.
- Password admin disimpan dengan `password_hash`; query menggunakan PDO prepared statements; form admin memakai CSRF token.

## Persyaratan
- Shared hosting yang mendukung PHP 8.1+ (disarankan PHP 8.2/8.3), PDO MySQL, dan MySQL/MariaDB.
- SSL/HTTPS aktif.
- Akses cPanel/phpMyAdmin atau fitur database hosting.

## Instalasi
1. Buat database MySQL dan user database dari cPanel. Berikan user hak akses ke database.
2. Buka phpMyAdmin, pilih database yang dibuat, lalu import `database.sql`.
3. Edit `config.example.php` dengan kredensial database, nama brand, dan nomor WhatsApp. Simpan sebagai `config.php` di folder utama website.
4. Unggah seluruh isi paket ke `public_html` atau folder domain/subdomain yang digunakan. Jangan mengunggah paket ZIP sebagai pengganti ekstraksi.
5. Pastikan hosting memakai PHP 8.1+ dan ekstensi PDO MySQL aktif. Aktifkan SSL.
6. Buka `https://domainkamu.com/admin/create-admin.php` dan buat akun admin dengan kata sandi minimal 12 karakter.
7. **Segera hapus `admin/create-admin.php` dari hosting setelah admin berhasil dibuat.**
8. Login melalui `https://domainkamu.com/admin/login.php`.
9. Masuk ke menu **Layanan, Paket & Harga** untuk mengubah paket awal, menambah paket, memperbarui harga/fitur, atau menonaktifkan paket.
10. Tambahkan proyek di menu Tambah proyek. Hanya proyek berstatus Publik tampil di landing page.
11. Uji tampilan di HP dan desktop, tombol yang mengarah ke form pesan, login admin, CRUD paket/proyek, serta tautan demo.

## Pengaturan WhatsApp
Nomor WhatsApp untuk semua tombol chat diatur melalui menu **Admin → Pengaturan Website → Nomor WhatsApp untuk chat**. Masukkan kode negara dan nomor, misalnya `6281234567890`, lalu simpan. Tidak perlu menambahkan nomor WhatsApp ke `config.php`. Pada instalasi lama, nomor yang sebelumnya ada di konfigurasi akan digunakan sebagai nilai awal sampai admin menyimpan nomor baru.

## Keamanan dan catatan
- Jangan membagikan username/kata sandi admin kepada klien.
- Gunakan demo terpisah dari sistem produksi dan jangan berikan akses dashboard admin demo.
- Jangan menaruh kredensial database di JavaScript.
- Halaman `admin/create-admin.php` hanya mengizinkan pembuatan akun ketika tabel admin masih kosong; setelah akun pertama dibuat, akses pembuatan akun ditolak.
- Backup database secara berkala dan gunakan kata sandi unik.
- Jika `.htaccess` menyebabkan HTTP 500 pada hosting non-Apache/LiteSpeed, konsultasikan dengan penyedia hosting atau sesuaikan aturan. File tersebut hanya menambahkan header keamanan dan menonaktifkan directory listing.
- Paket ini tidak menyediakan upload gambar proyek. Kartu portofolio memakai cover warna dan teks; fitur upload thumbnail bisa ditambahkan berikutnya.


## Pengaturan nama, logo, dan gambar portofolio
- Login ke panel admin, lalu buka menu **Pengaturan Website** atau `admin/settings.php`.
- Ubah nama website dan unggah logo serta favicon. Pengaturan disimpan di tabel `site_settings`; tidak perlu mengubah `config.php` untuk nama website setelah tersimpan.
- Saat menambah/mengedit portofolio, gunakan kolom **Gambar proyek / screenshot website**. Format JPG, PNG, WEBP maksimal 5 MB. Gambar tampil di kartu portofolio, halaman detail, dan daftar admin.
- Pastikan folder `uploads/` dapat ditulisi oleh PHP di hosting. Folder dibuat otomatis saat unggah bila izin folder induk memungkinkan.
- Untuk database yang sudah terpasang, aplikasi akan membuat tabel `site_settings` dan menambahkan kolom `projects.image_path` otomatis saat pertama kali halaman dijalankan. Tidak perlu mengimpor ulang database.

## Galeri portofolio (maksimal 9 gambar)

- Form tambah/edit proyek sekarang menerima beberapa file gambar sekaligus (`JPG`, `PNG`, `WEBP`), maksimal 9 gambar per proyek.
- Ukuran maksimal tiap gambar adalah 5 MB. Batas total yang bisa dikirim sekaligus juga mengikuti konfigurasi PHP hosting (`post_max_size`, `upload_max_filesize`, dan `max_file_uploads`).
- Gambar pertama dipakai sebagai cover kartu portofolio. Pada halaman detail proyek, semua gambar tampil dalam galeri responsif; klik gambar untuk melihatnya dalam tampilan besar.
- Tabel `project_images` dibuat otomatis oleh `includes/bootstrap.php`. Gambar lama yang tersimpan di `projects.image_path` dimasukkan ke galeri secara otomatis, jadi tidak perlu impor SQL manual.
- Pastikan folder `uploads/portfolio` bisa ditulisi oleh PHP.


## Galeri portofolio dan pengelolaan gambar
- Detail proyek menggunakan slider utama, thumbnail yang dapat dipilih, navigasi sebelumnya/berikutnya, dan swipe pada layar sentuh.
- Form portofolio mendukung maksimal 9 gambar per proyek. Setiap kartu gambar menyediakan Preview, Crop, Ganti, dan Hapus.
- Editor crop menggunakan Cropper.js dari CDN, sehingga browser admin perlu koneksi internet saat membuka fitur crop.
- File gambar dibatasi 5 MB per file dengan format JPG, PNG, atau WEBP.


## Revisi informasi detail portofolio
Form proyek sekarang mendukung Deskripsi proyek, Fitur utama, Keunggulan, URL akses demo, Informasi akses demo, Catatan, dan System Requirements. Kolom database tambahan (`advantages`, `demo_access`, `notes`, `system_requirements`) akan ditambahkan otomatis saat website dijalankan. Tidak perlu impor SQL manual. Informasi tambahan hanya ditampilkan pada detail portofolio jika diisi.

### Detail tambahan paket layanan

Admin → Layanan & Harga → Tambah/Edit paket kini memiliki kolom opsional Deskripsi Lengkap, Cocok Untuk, Durasi Pengerjaan, Ketentuan Revisi, Yang Termasuk, dan Yang Tidak Termasuk. Kolom yang kosong tidak ditampilkan pada popup detail layanan. Aplikasi mencoba menambahkan kolom database secara otomatis saat dibuka; bila akun database tidak memiliki izin ALTER, jalankan `migration_service_package_details.sql` melalui phpMyAdmin satu kali (hanya pada database yang belum memiliki kolom tersebut).


## Pembaruan v12 — harga diskon dan konten layanan

- Admin → Layanan & Harga kini memiliki kolom **Harga awal** dan **Harga diskon (opsional)**. Jika harga diskon diisi, nilainya wajib lebih rendah dari harga awal. Kosongkan untuk menampilkan harga normal saja.
- Kartu layanan dan popup detail menampilkan harga awal dengan coretan dan harga diskon sebagai harga utama. Form konsultasi juga menggunakan harga diskon bila tersedia.
- Konten enam paket bawaan (Landing Page, Company Profile, Katalog Produk, Website Bisnis + Admin, Sistem Booking, dan Toko Online) diperjelas: deskripsi, fitur, sasaran pengguna, durasi, revisi, yang termasuk, dan yang tidak termasuk.
- Untuk database lama, unggah file ini lalu jalankan `migration_service_discount_and_content_v12.sql` melalui phpMyAdmin. File migrasi memperbarui teks pada enam paket bawaan yang namanya cocok. Jika kolom `discount_price` sudah ada, hapus/lewati baris `ALTER TABLE` pertama sebelum menjalankan sisa perintah SQL.
- Migrasi konten tidak mengubah harga atau label khusus yang sudah tersimpan. Tinjau detail paket sebelum dipublikasikan karena durasi, revisi, domain/hosting, dan integrasi harus disesuaikan dengan penawaran aktual RuangWeb.


## Metode penjualan portofolio
Admin dapat memilih satu skema untuk menandai skema proyek yang sedang ditampilkan: **Project-Based**, **Website Maintenance**, atau **Website as a Service (WaaS)**. Detail portofolio menampilkan tabel perbandingan metode, skema pembayaran, dan hak milik website serta menandai skema yang dipilih untuk proyek tersebut. Untuk database lama, jalankan `migration_portfolio_sales_method.sql` satu kali melalui phpMyAdmin agar data proyek yang sudah ada terisi; proyek biasa diatur ke Project-Based, sedangkan proyek yang secara jelas menyebut maintenance atau sewa/berlangganan dipetakan berdasarkan nama, kategori, dan deskripsi. Periksa hasil pemetaan sebelum dipublikasikan.


## Model bisnis dan metode penjualan portofolio
- Halaman utama menjelaskan Project-Based, Website Retainer, WaaS, dan SaaS.
- Detail portofolio hanya menampilkan metode yang dipilih untuk proyek tersebut.
- Untuk database lama, jalankan `migration_portfolio_sales_method.sql` satu kali melalui phpMyAdmin.


## Revisi tata letak metode penjualan
- Penjelasan empat model kerja sama (Project-Based, Website Retainer, WaaS, dan SaaS) sekarang menjadi satu bagian dengan section **Cara Kerja**, setelah langkah pengerjaan.
- Pada Detail Portofolio, metode penjualan ditampilkan sebagai metadata sejajar dengan **Kategori** dan **Jenis website**.


## Pembaruan landing page dan status portofolio
- Hero diperjelas untuk layanan pembuatan website dan aplikasi custom bagi UMKM, bisnis lokal, dan perusahaan.
- CTA utama mengarah langsung ke WhatsApp dengan teks konsultasi gratis.
- Bagian layanan, proses pengerjaan, transparansi harga, dan FAQ diperjelas.
- Status proyek tersedia di admin: Demo / contoh konsep, Proyek internal, atau Digunakan klien.
- Pada database lama, jalankan `migration_portfolio_status.sql` satu kali jika kolom `project_status` belum ada. Jika bootstrap memiliki izin ALTER, kolom akan dicoba dibuat otomatis.
- Isi status dan studi kasus sesuai kondisi nyata; jangan menambahkan klaim hasil yang belum terverifikasi.

## Catatan revisi NEXORA
- Harga promo menampilkan harga normal yang dicoret, harga promo, dan nominal hemat agar tidak tertukar.
- Ketentuan domain/hosting diperjelas pada catatan harga dan FAQ: hanya termasuk jika tertulis pada paket/penawaran; biaya pembelian dan perpanjangan yang tidak termasuk ditanggung terpisah.
- Metadata halaman portofolio menggunakan judul dan deskripsi proyek bila detail proyek dibuka.
- Ditambahkan `robots.txt` dan `sitemap.xml` untuk URL utama `https://invitodigital.net/`. Sesuaikan domain di kedua file jika domain produksi berbeda.
- Pengaturan URL utama, gambar preview Open Graph, dan nomor WhatsApp tetap perlu diisi benar melalui konfigurasi/panel admin.
