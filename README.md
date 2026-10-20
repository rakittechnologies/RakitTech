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
2. Buka phpMyAdmin, pilih database yang dibuat, lalu import 
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
