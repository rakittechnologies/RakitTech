-- RuangWeb v12: kolom harga diskon dan penyempurnaan konten enam paket bawaan.
ALTER TABLE service_packages ADD COLUMN discount_price BIGINT UNSIGNED NULL DEFAULT NULL;
-- Jika kolom discount_price sudah ada, jalankan hanya perintah UPDATE di bawah ini.
UPDATE service_packages SET description='Halaman promosi terarah untuk memperkenalkan satu produk, jasa, atau kampanye dan mendorong pengunjung menghubungi bisnis.', features='Desain satu halaman dengan struktur yang jelas
Hingga 5–7 bagian konten sesuai kebutuhan
Tampilan responsif untuk HP, tablet, dan desktop
Tombol WhatsApp atau CTA utama
Formulir kontak sederhana bila diperlukan
SEO dasar: title, description, dan struktur heading
Optimasi gambar dan performa dasar', full_description='Halaman promosi yang fokus pada satu tujuan, seperti mendapatkan prospek, memperkenalkan layanan, atau mengarahkan pengunjung ke WhatsApp.', suitable_for='UMKM, promosi produk baru, jasa profesional, event, dan kampanye iklan', work_duration='5–10 hari kerja setelah materi lengkap', revision_terms='Termasuk 2 kali revisi minor pada desain/konten sebelum final; perubahan ruang lingkup dapat dikenakan biaya tambahan.', included_items='Desain dan implementasi halaman
Pemasangan konten yang disediakan klien
Pengaturan tampilan responsif
Panduan singkat pengelolaan konten statis', excluded_items='Domain dan hosting berbayar jika tidak tercantum pada penawaran
Penulisan copywriting profesional dan produksi foto/video
Integrasi berbayar atau fitur di luar ruang lingkup' WHERE name='Landing Page';
UPDATE service_packages SET description='Website profil perusahaan yang menyajikan identitas, layanan, keunggulan, portofolio, dan kontak bisnis secara profesional.', features='Hingga 5 halaman utama
Beranda, Tentang, Layanan, Portofolio, dan Kontak
Tampilan responsif untuk HP, tablet, dan desktop
Integrasi WhatsApp, peta, dan media sosial sesuai kebutuhan
Formulir kontak sederhana
SEO dasar dan optimasi performa
Struktur konten yang mudah dipahami', full_description='Membangun kepercayaan dengan informasi perusahaan yang rapi, konsisten, dan mudah ditemukan oleh calon pelanggan.', suitable_for='Perusahaan, UMKM, konsultan, kontraktor, agensi, komunitas, dan penyedia jasa', work_duration='7–14 hari kerja setelah materi lengkap', revision_terms='Termasuk 2 kali revisi minor per tahap desain/konten yang disepakati; perubahan besar atau penambahan halaman dihitung terpisah.', included_items='Desain website sesuai identitas brand
Hingga 5 halaman sesuai paket
Pemasangan teks dan gambar dari klien
Pengaturan tautan kontak dan media sosial
SEO dasar', excluded_items='Pembuatan logo atau identitas visual baru
Penulisan konten dari nol dan sesi foto
Fitur login, dashboard, transaksi, atau sistem khusus
Biaya domain dan hosting berbayar jika tidak tercantum' WHERE name='Company Profile';
UPDATE service_packages SET description='Katalog online untuk menampilkan daftar produk atau jasa lengkap dengan foto, deskripsi, kategori, dan jalur pemesanan.', features='Halaman katalog produk atau layanan
Kartu produk dengan foto, nama, dan deskripsi
Kategori produk sederhana
Halaman atau bagian detail produk sesuai lingkup
Tombol pemesanan melalui WhatsApp
Tampilan responsif
SEO dasar dan optimasi gambar', full_description='Memudahkan calon pelanggan menjelajahi produk sebelum bertanya atau melakukan pemesanan, tanpa harus langsung membangun sistem toko online penuh.', suitable_for='Toko retail, pengrajin, kuliner, fashion, supplier, dan penyedia jasa dengan banyak pilihan', work_duration='7–14 hari kerja setelah materi produk lengkap', revision_terms='Termasuk 2 kali revisi minor; perubahan struktur katalog atau penambahan data produk di luar kesepakatan dapat dikenakan biaya.', included_items='Tata letak katalog dan kategori sederhana
Pemasangan data awal produk sesuai jumlah yang disepakati
Tautan pemesanan WhatsApp
Tampilan untuk perangkat mobile', excluded_items='Keranjang dan checkout otomatis
Manajemen stok atau pesanan melalui dashboard
Input produk massal di luar jumlah kesepakatan
Integrasi pembayaran dan ongkir otomatis' WHERE name='Katalog Produk';
UPDATE service_packages SET description='Website bisnis dengan dashboard admin agar tim dapat memperbarui konten atau data tertentu tanpa mengubah kode secara langsung.', features='Website publik responsif sesuai kebutuhan
Login admin dengan pembatasan akses dasar
Dashboard pengelolaan konten/data yang disepakati
Tambah, edit, dan hapus data sesuai modul
Validasi formulir dan notifikasi status dasar
Struktur database untuk fitur yang disepakati
SEO dasar untuk halaman publik', full_description='Menggabungkan tampilan website publik dengan panel pengelolaan agar pembaruan informasi bisnis lebih praktis dan terkontrol.', suitable_for='Bisnis yang rutin memperbarui layanan, berita, katalog sederhana, profil, atau informasi operasional', work_duration='14–25 hari kerja setelah alur dan materi disetujui', revision_terms='Termasuk 2 kali revisi minor pada alur dan tampilan; perubahan alur kerja, modul, atau hak akses setelah disetujui dapat mengubah biaya dan durasi.', included_items='Website publik dan dashboard admin sesuai spesifikasi
Modul pengelolaan yang tercantum dalam penawaran
Pengujian fungsi utama
Panduan penggunaan dasar', excluded_items='Aplikasi mobile native
Integrasi eksternal berbayar
Audit keamanan khusus atau infrastruktur skala besar
Modul yang tidak tercantum dalam ruang lingkup' WHERE name='Website Bisnis + Admin';
UPDATE service_packages SET description='Sistem pemesanan jadwal online untuk membantu pelanggan memilih layanan dan waktu, serta membantu admin mengelola reservasi.', features='Formulir booking online
Pilihan layanan dan jadwal sesuai aturan bisnis
Penyimpanan data reservasi
Status booking dan pengelolaan oleh admin
Validasi jadwal dasar untuk mengurangi benturan
Tampilan responsif untuk pelanggan dan admin
Notifikasi atau integrasi tambahan sesuai kesepakatan', full_description='Membuat proses reservasi lebih teratur, mengurangi pencatatan manual, dan memudahkan tim memantau status pemesanan.', suitable_for='Salon, klinik non-medis, studio, penyewaan, konsultasi, kursus, dan layanan berbasis jadwal', work_duration='18–30 hari kerja setelah alur booking disepakati', revision_terms='Termasuk 2 kali revisi minor pada alur yang telah disepakati; perubahan aturan jadwal atau penambahan integrasi dihitung sebagai pengembangan tambahan.', included_items='Form booking dan tampilan status
Dashboard admin untuk mengelola reservasi
Pengaturan layanan dan slot sesuai spesifikasi
Pengujian alur booking utama', excluded_items='Biaya layanan SMS/WhatsApp/email pihak ketiga
Pembayaran online otomatis kecuali disepakati
Sinkronisasi kalender eksternal atau multi-cabang kompleks
Migrasi data lama dalam jumlah besar' WHERE name='Sistem Booking';
UPDATE service_packages SET description='Toko online untuk menampilkan produk, mengelola keranjang dan pesanan, serta menyiapkan alur pembayaran dan pengiriman sesuai kebutuhan bisnis.', features='Katalog produk dan kategori
Keranjang belanja dan checkout
Data pelanggan dan pesanan
Dashboard admin untuk produk dan transaksi
Status pesanan dan pembayaran
Integrasi pembayaran sesuai penyedia yang disepakati
Integrasi ongkir/pengiriman sesuai layanan yang tersedia
Tampilan responsif dan SEO dasar', full_description='Solusi penjualan online dengan alur belanja yang lebih lengkap, dari penelusuran produk hingga pengelolaan pesanan oleh admin.', suitable_for='Retail, brand lokal, fashion, perlengkapan, produk rumahan, dan bisnis yang ingin menerima pesanan online', work_duration='25–45 hari kerja setelah data produk dan aturan toko lengkap', revision_terms='Termasuk 2 kali revisi minor pada tampilan dan alur yang disetujui; perubahan besar pada checkout, promosi, atau integrasi memerlukan penyesuaian estimasi.', included_items='Katalog dan kategori produk
Keranjang dan checkout sesuai ruang lingkup
Dashboard pengelolaan produk dan pesanan
Konfigurasi metode pembayaran/pengiriman yang disepakati
Pengujian alur pembelian utama', excluded_items='Biaya transaksi penyedia pembayaran dan ongkir
Langganan layanan pihak ketiga
Aplikasi mobile native
ERP, akuntansi, atau integrasi khusus yang tidak tercantum' WHERE name='Toko Online';
