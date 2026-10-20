-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 20 Okt 2026 pada 10.25
-- Versi server: 11.4.13-MariaDB-cll-lve
-- Versi PHP: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `invk9528_nexora`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$C9r92cEyTgq5Qg.HIiCZOe/5.pQncCB08nhEQBmFMYCez9FzxtQDq', '2026-10-13 19:27:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `projects`
--

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` longtext NOT NULL,
  `category` longtext DEFAULT NULL,
  `website_type` enum('Website Statis','Website Dinamis') NOT NULL DEFAULT 'Website Statis',
  `business_model_status` varchar(80) NOT NULL DEFAULT 'Contoh konsep',
  `sales_method` varchar(120) NOT NULL DEFAULT 'Konsultasi melalui WhatsApp',
  `project_status` varchar(80) NOT NULL DEFAULT 'Demo / contoh konsep',
  `description` longtext NOT NULL,
  `demo_url` longtext NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `features` longtext DEFAULT NULL,
  `theme_color` varchar(7) NOT NULL DEFAULT '#5B77EB',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `advantages` longtext DEFAULT NULL,
  `demo_access` longtext DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `system_requirements` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `projects`
--

INSERT INTO `projects` (`id`, `title`, `category`, `website_type`, `business_model_status`, `sales_method`, `project_status`, `description`, `demo_url`, `image_path`, `features`, `theme_color`, `is_published`, `sort_order`, `created_at`, `updated_at`, `advantages`, `demo_access`, `notes`, `system_requirements`) VALUES
(1, 'FlowPOS – Aplikasi Kasir Digital', 'Point Of Sale (POS)', 'Website Dinamis', 'Contoh konsep', 'Project-Based', 'Demo / contoh konsep', 'FlowPOS adalah aplikasi Point of Sale (POS) berbasis web dan Android untuk membantu UMKM kuliner mengelola operasional usaha secara praktis dan terintegrasi. Fitur utamanya meliputi kasir digital, manajemen transaksi, produk, stok, pembelian, resep dan HPP, keuangan, laporan laba-rugi, shift kasir, member dan poin loyalitas, serta pengelolaan cabang dan hak akses pengguna.', 'https://flowpos-id.vercel.app/', 'uploads/portfolio/project-623d608333ee80e0e006.jpg', 'Dashboard dan laporan penjualan, kasir digital, manajemen transaksi, dine-in dan takeaway, diskon dan pajak, cetak struk Bluetooth, manajemen produk dan kategori, pengaturan harga per cabang, varian dan modifier produk, manajemen resep dan HPP, manajemen stok, stok opname, purchase order (PO), manajemen pemasok, pencatatan pengeluaran, laporan laba-rugi, manajemen shift kasir, pengelolaan member dan poin loyalitas, manajemen pengguna dan hak akses, manajemen cabang, pembayaran tunai dan QRIS.', '#18AE70', 1, 0, '2026-10-13 19:31:34', '2026-10-14 04:47:30', 'Mendukung operasional UMKM kuliner dalam satu sistem terintegrasi.\r\nDashboard untuk memantau penjualan dan kinerja usaha.\r\nKasir digital dengan dukungan transaksi dine-in dan takeaway.\r\nManajemen produk, kategori, varian, dan harga per cabang.\r\nPengelolaan stok, pembelian, pemasok, resep, dan HPP.\r\nLaporan penjualan, pengeluaran, dan laba-rugi.\r\nManajemen shift kasir dan hak akses pengguna.\r\nFitur member dan poin loyalitas pelanggan.\r\nTersedia dalam versi web dan aplikasi Android.', 'Akun Owner\r\nEmail: mf@kopitmg.com\r\nPassword: Bismillahcuan123\r\n\r\nAkun Inventori\r\nEmail: inventori1@kopitmg.com\r\nPassword: DNKJ7eJMb7YAWt!7\r\n\r\nAkun Manager\r\nEmail: manager1@kopitmg.com\r\nPassword: UJrn59vVRvy95G!7\r\n\r\nAkun Finance\r\nEmail: finance1@kopitmg.com\r\nPassword: j7GeQQZ9bCNkh!7', '', ''),
(3, 'Website Undangan Digital', 'Undangan Digital', 'Website Dinamis', 'Contoh konsep', 'Project-Based', 'Demo / contoh konsep', 'website undangan pernikahan digital yang dirancang untuk menyampaikan informasi acara pernikahan secara online dengan tampilan menarik, interaktif, dan responsif. Website ini memuat informasi kedua mempelai, jadwal dan lokasi acara, kisah perjalanan cinta, galeri foto, serta fitur ucapan dan doa dari tamu undangan.\r\nWebsite ini menjadi alternatif undangan cetak yang praktis, mudah dibagikan melalui tautan, dan dapat diakses melalui perangkat yang terhubung ke internet.', 'https://invitodigital.net/undangan/', 'uploads/portfolio/project-daa14a8ea8992af06fe3.png', 'Informasi Mempelai\r\nJadwal Acara\r\nGoogle Calendar\r\nKisah Cinta\r\nHitung Mundur Acara\r\nInformasi Lokasi\r\nGaleri Foto\r\nLove Gift\r\nUcapan dan Doa\r\nMusik Latar', '#E15B9E', 1, 1, '2026-10-13 23:47:36', '2026-10-14 02:58:58', 'Praktis dibagikan melalui tautan tanpa perlu mencetak undangan.\r\nInformasi acara tersusun dalam satu halaman yang mudah diakses.\r\nTampilan visual menarik dengan elemen interaktif.\r\nMemudahkan tamu mengakses jadwal dan lokasi acara.\r\nMendukung penyampaian ucapan, doa, dan informasi hadiah secara digital.\r\nDapat menjadi solusi undangan yang lebih fleksibel untuk berbagai kebutuhan acara pernikahan.', '', '', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `project_images`
--

CREATE TABLE `project_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `image_path`, `sort_order`, `created_at`) VALUES
(26, 3, 'uploads/portfolio/project-47f1e3f84eeaf5e3bc62.png', 2, '2026-10-13 23:57:55'),
(27, 3, 'uploads/portfolio/project-bb4018e5f323517c0d1f.png', 3, '2026-10-13 23:57:55'),
(28, 3, 'uploads/portfolio/project-7ffa6452bf6dd31f737d.png', 4, '2026-10-13 23:57:55'),
(29, 3, 'uploads/portfolio/project-5cf2a262349509048976.png', 5, '2026-10-13 23:57:55'),
(30, 3, 'uploads/portfolio/project-fb49034d002be0f521c7.png', 6, '2026-10-13 23:57:55'),
(31, 3, 'uploads/portfolio/project-cff7ee8a6463cc8bdced.png', 7, '2026-10-13 23:57:55'),
(32, 3, 'uploads/portfolio/project-460292c69323d7413054.png', 8, '2026-10-13 23:57:55'),
(33, 3, 'uploads/portfolio/project-b02a85aa8421158a1586.png', 1, '2026-10-13 23:57:55'),
(34, 3, 'uploads/portfolio/project-daa14a8ea8992af06fe3.png', 0, '2026-10-13 23:57:55'),
(35, 1, 'uploads/portfolio/project-623d608333ee80e0e006.jpg', 0, '2026-10-14 04:47:30'),
(37, 1, 'uploads/portfolio/project-f59d9f04008ec300b78f.jpg', 1, '2026-10-14 04:48:23'),
(38, 1, 'uploads/portfolio/project-bdb66b203256bdd70887.jpg', 2, '2026-10-14 04:48:23'),
(39, 1, 'uploads/portfolio/project-a59cb6646d601f8bde91.jpg', 3, '2026-10-14 04:48:23'),
(40, 1, 'uploads/portfolio/project-cc0deaa9e5edec51a52d.jpg', 4, '2026-10-14 04:48:23'),
(41, 1, 'uploads/portfolio/project-7875ed9bb3ee612a0fc2.jpg', 5, '2026-10-14 04:48:23'),
(42, 1, 'uploads/portfolio/project-bf1a17b5158a221b395b.png', 6, '2026-10-14 04:48:23'),
(43, 1, 'uploads/portfolio/project-dd1ac069118e62bdf9d7.png', 7, '2026-10-14 04:48:23'),
(44, 1, 'uploads/portfolio/project-59e84237ba62099bfb79.jpg', 8, '2026-10-14 04:49:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `service_packages`
--

CREATE TABLE `service_packages` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` enum('Website Statis','Website Dinamis') NOT NULL DEFAULT 'Website Statis',
  `name` longtext NOT NULL,
  `description` longtext NOT NULL,
  `price` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `features` longtext NOT NULL,
  `ribbon` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `full_description` longtext DEFAULT NULL,
  `suitable_for` longtext DEFAULT NULL,
  `work_duration` varchar(255) DEFAULT NULL,
  `revision_terms` longtext DEFAULT NULL,
  `included_items` longtext DEFAULT NULL,
  `excluded_items` longtext DEFAULT NULL,
  `discount_price` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `service_packages`
--

INSERT INTO `service_packages` (`id`, `category`, `name`, `description`, `price`, `features`, `ribbon`, `is_active`, `sort_order`, `created_at`, `updated_at`, `full_description`, `suitable_for`, `work_duration`, `revision_terms`, `included_items`, `excluded_items`, `discount_price`) VALUES
(1, 'Website Statis', 'Landing Page', 'Satu halaman untuk promosi produk, jasa, atau acara dengan informasi yang terarah.', 750000, '1 halaman dengan maksimal 5–7 section\r\nDesain responsif untuk mobile dan desktop\r\nTombol WhatsApp untuk konsultasi\r\nSEO dasar dan optimasi kecepatan\r\nDomain .com atau .my.id, sesuai ketersediaan\r\n1 kali revisi', NULL, 1, 10, '2026-10-13 19:58:33', '2026-10-14 04:38:19', 'Website satu halaman yang dirancang untuk memperkenalkan bisnis, mempromosikan produk atau layanan, dan mengarahkan pengunjung melakukan tindakan tertentu seperti menghubungi WhatsApp, mengisi formulir, atau melakukan pemesanan. Tampilan responsif untuk HP, tablet, dan komputer.', 'UMKM, jasa profesional, promosi produk, kampanye pemasaran, peluncuran produk baru, event, dan bisnis yang membutuhkan halaman promosi terfokus.', '5–10 hari kerja setelah materi, konten, dan kebutuhan desain disetujui.', 'Maksimal 2 putaran revisi desain dan konten selama masih sesuai ruang lingkup paket. Perubahan konsep utama atau penambahan fitur dapat dikenakan biaya tambahan.', 'Desain satu halaman responsif; bagian informasi bisnis, produk atau layanan; tombol ajakan bertindak (CTA); tautan WhatsApp atau kontak yang disepakati; pengaturan dasar SEO on-page; pengujian tampilan di perangkat umum.', 'Biaya domain dan hosting; penulisan konten profesional atau pemotretan khusus; fitur login, dashboard admin, atau transaksi kompleks; biaya layanan pihak ketiga berbayar.', 350000),
(2, 'Website Statis', 'Company Profile', 'Website untuk memperkenalkan bisnis, layanan, portofolio, dan informasi perusahaan secara profesional.', 900000, 'Maksimal 5 halaman utama\nBeranda, Tentang, Layanan, Portofolio, dan Kontak\nDesain responsif untuk mobile dan desktop\nIntegrasi WhatsApp dan media sosial\nSEO dasar dan optimasi kecepatan\nDomain .com atau .my.id, sesuai ketersediaan', 'Pilihan populer', 1, 20, '2026-10-13 19:58:34', '2026-10-14 02:22:35', 'Website profil perusahaan untuk membangun kredibilitas bisnis secara online. Menampilkan informasi perusahaan, layanan, keunggulan, portofolio, tim, dan informasi kontak dalam tampilan profesional yang mudah diakses.', 'Perusahaan, agensi, kontraktor, konsultan, penyedia jasa, lembaga, organisasi, dan UMKM yang ingin meningkatkan kepercayaan calon pelanggan.', '7–14 hari kerja setelah materi dan struktur halaman disetujui.', 'Maksimal 2 putaran revisi desain dan konten. Perubahan struktur besar atau penambahan halaman di luar kesepakatan dapat dikenakan biaya tambahan.', 'Halaman profil perusahaan; halaman layanan atau produk; halaman kontak dan tautan WhatsApp; galeri atau portofolio sesuai kebutuhan; desain responsif; pengaturan dasar SEO on-page.', 'Biaya domain dan hosting; penulisan seluruh konten dari nol jika tidak disepakati; sistem login, transaksi online, atau dashboard khusus; biaya layanan berbayar dan pemeliharaan rutin.', NULL),
(3, 'Website Statis', 'Katalog Produk', 'Menampilkan produk atau jasa secara rapi agar calon pelanggan mudah melihat pilihan.', 1000000, 'Halaman katalog produk\nInformasi detail produk\nKategori produk sederhana\nIntegrasi WhatsApp untuk pemesanan\nTampilan responsif di semua perangkat\nSEO dasar dan optimasi kecepatan', NULL, 1, 30, '2026-10-13 19:58:34', '2026-10-14 02:22:35', 'Website katalog untuk menampilkan produk secara terstruktur berdasarkan kategori, lengkap dengan foto, nama, deskripsi, dan informasi harga. Pengunjung dapat menjelajahi produk dan menghubungi penjual untuk menanyakan ketersediaan atau melakukan pemesanan.', 'Toko offline, distributor, produsen, usaha kerajinan, supplier, bisnis fashion, makanan, perlengkapan rumah, dan usaha yang ingin memasarkan produk tanpa sistem checkout kompleks.', '7–14 hari kerja setelah foto dan data produk tersedia.', 'Maksimal 2 putaran revisi tampilan dan struktur katalog. Penambahan jumlah produk di luar kuota yang disepakati atau fitur khusus dapat dikenakan biaya tambahan.', 'Halaman katalog dan kategori produk; foto, nama, deskripsi, dan harga produk; halaman detail produk; tombol konsultasi atau pemesanan melalui WhatsApp; desain responsif; pengaturan dasar SEO on-page.', 'Keranjang belanja dan checkout otomatis; payment gateway dan integrasi pengiriman otomatis; input produk tanpa batas; biaya domain, hosting, dan layanan pihak ketiga.', NULL),
(4, 'Website Dinamis', 'Website Bisnis + Admin', 'Website bisnis dengan dashboard untuk mengelola konten dan informasi secara mandiri.', 2500000, 'Website bisnis responsif\r\nSistem login admin\r\nKelola data dan konten\r\nDashboard untuk informasi utama\r\nPengaturan tampilan dan isi\r\nDomain .com atau .my.id, sesuai ketersediaan', 'Dengan dashboard admin', 1, 10, '2026-10-13 19:58:34', '2026-10-14 04:38:45', 'Website bisnis yang dilengkapi dashboard admin untuk membantu pengelola memperbarui konten dan data tertentu secara mandiri. Fitur admin disesuaikan dengan kebutuhan proyek, dengan akses melalui akun yang telah ditentukan.', 'Bisnis yang sering memperbarui informasi, perusahaan dengan banyak layanan, penyedia jasa, organisasi, dan usaha yang membutuhkan pengelolaan konten melalui dashboard.', '14–30 hari kerja, bergantung pada jumlah modul dan kompleksitas dashboard.', 'Maksimal 2 putaran revisi untuk desain dan fungsi yang telah disepakati. Perubahan alur kerja, penambahan modul, atau integrasi baru dapat memerlukan estimasi biaya dan waktu tambahan.', 'Website bisnis responsif; dashboard admin sesuai ruang lingkup; login admin dan pengelolaan akses dasar; pengelolaan konten atau data yang disepakati; integrasi database sesuai kebutuhan; pengujian fungsi dan panduan penggunaan dasar.', 'Fitur di luar spesifikasi yang disepakati; aplikasi Android atau iOS; integrasi API berbayar atau sistem eksternal kompleks; migrasi data dalam jumlah besar tanpa kesepakatan; biaya domain, hosting, dan pemeliharaan berkelanjutan.', 1500000),
(5, 'Website Dinamis', 'Sistem Booking', 'Untuk reservasi layanan, jadwal konsultasi, penyewaan, atau pemesanan berdasarkan waktu.', 3000000, 'Form booking online\nLogin dan pengelolaan pemesanan\nData reservasi tersimpan\nPengaturan jadwal dan status\nNotifikasi sesuai kebutuhan\nDomain .com atau .my.id, sesuai ketersediaan', NULL, 1, 20, '2026-10-13 19:58:34', '2026-10-14 02:22:35', 'Sistem pemesanan jadwal online yang membantu pelanggan mengajukan reservasi melalui website. Informasi pemesanan dapat mencakup tanggal, waktu, layanan, dan data pelanggan. Alur konfirmasi dan pengelolaan reservasi disesuaikan dengan kebutuhan bisnis.', 'Barbershop, salon, studio foto, jasa konsultasi, tempat kursus, penyewaan, layanan perawatan, dan usaha lain yang menggunakan sistem janji temu.', '14–30 hari kerja setelah alur booking, aturan jadwal, dan kebutuhan pengelolaan disepakati.', 'Maksimal 2 putaran revisi pada tampilan dan alur booking sesuai kesepakatan awal. Perubahan aturan reservasi, integrasi kalender, atau penambahan sistem pembayaran dapat dikenakan biaya tambahan.', 'Formulir pemesanan online; pemilihan layanan dan jadwal sesuai spesifikasi; penyimpanan data reservasi; tampilan daftar pemesanan untuk admin jika termasuk ruang lingkup; status pemesanan sesuai alur yang disepakati; desain responsif dan pengujian dasar.', 'Integrasi pembayaran online jika tidak disepakati; WhatsApp API atau SMS berbayar; sinkronisasi kalender eksternal yang kompleks; aplikasi mobile khusus; biaya domain, hosting, dan layanan pihak ketiga.', NULL),
(6, 'Website Dinamis', 'Toko Online', 'Toko online untuk menampilkan produk, menerima pesanan, dan mengelola transaksi pelanggan.', 5000000, 'Dashboard pengelolaan produk\nKeranjang belanja dan checkout\nData pesanan dan status pembayaran\nIntegrasi pembayaran sesuai kebutuhan\nIntegrasi ongkir dan pengiriman sesuai kebutuhan\nDomain .com atau .my.id, sesuai ketersediaan', 'Fitur terlengkap', 1, 30, '2026-10-13 19:58:34', '2026-10-14 02:22:35', 'Website e-commerce untuk membantu bisnis menjual produk secara online. Pelanggan dapat melihat katalog, membuka detail produk, memasukkan produk ke keranjang, dan melakukan checkout sesuai fitur paket. Pengelola dapat mengatur produk serta pesanan melalui dashboard jika termasuk ruang lingkup proyek.', 'Toko fashion, toko perlengkapan, bisnis makanan kemasan, produsen, distributor, brand lokal, dan UMKM yang ingin memiliki saluran penjualan sendiri.', '21–45 hari kerja, bergantung pada jumlah produk, metode pembayaran, pengiriman, dan kebutuhan integrasi.', 'Maksimal 2 putaran revisi desain dan alur pembelian yang telah disepakati. Penambahan metode pembayaran, integrasi logistik, atau fitur promosi khusus dapat memerlukan biaya dan waktu tambahan.', 'Katalog dan kategori produk; halaman detail produk; keranjang belanja dan checkout sesuai spesifikasi; pengelolaan produk dan pesanan jika termasuk paket; pengaturan status pesanan dasar; desain responsif dan pengujian alur belanja.', 'Biaya transaksi payment gateway; biaya domain dan hosting; integrasi kurir atau perhitungan ongkir otomatis jika tidak disepakati; sinkronisasi marketplace, akuntansi, atau gudang eksternal; input produk dalam jumlah besar dan pemeliharaan rutin tanpa kesepakatan.', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('bing_site_verification', '', '2026-10-14 00:26:06'),
('faq_items', '[{\"question\":\"Berapa biaya pembuatan website?\",\"answer\":\"Biaya menyesuaikan jumlah halaman, desain, fitur, kebutuhan admin, integrasi, domain, dan hosting. Setelah konsultasi, kamu akan mendapat rincian penawaran terlebih dahulu.\"},{\"question\":\"Apakah website bisa dibuka lewat HP?\",\"answer\":\"Ya. Desain dibuat responsif agar tata letak menyesuaikan ukuran layar HP, tablet, maupun komputer.\"},{\"question\":\"Apa perbedaan website statis dan dinamis?\",\"answer\":\"Website statis cocok untuk informasi yang jarang berubah. Website dinamis menyediakan pengelolaan konten atau fitur interaktif seperti dashboard admin, booking, produk, dan transaksi.\"},{\"question\":\"Apakah saya bisa mengubah isi website sendiri?\",\"answer\":\"Bisa jika website dibuat dengan dashboard admin atau sistem pengelolaan konten. Untuk website statis, perubahan konten dapat dibantu sebagai layanan pembaruan.\"},{\"question\":\"Apakah demo portofolio bisa dicoba?\",\"answer\":\"Bisa jika tautan demo tersedia. Demo digunakan untuk melihat contoh tampilan dan fitur, bukan untuk mengakses panel admin atau data internal.\"},{\"question\":\"Bagaimana sistem pembayaran proyek?\",\"answer\":\"Pembayaran mengikuti penawaran yang disepakati. Untuk proyek pembuatan website, pembayaran dapat menggunakan DP dan pelunasan sesuai tahap kerja yang tertulis dalam kesepakatan.\"},{\"question\":\"Siapa pemilik website setelah selesai?\",\"answer\":\"Kepemilikan mengikuti model kerja sama dan perjanjian. Pada model Project-Based, website dan aset yang disepakati diserahkan setelah pembayaran lunas.\"},{\"question\":\"Apakah domain dan hosting sudah termasuk?\",\"answer\":\"Jangan menganggap domain atau hosting otomatis termasuk. Keduanya hanya termasuk jika tertulis jelas pada rincian paket atau penawaran. Jika tidak tercantum, biaya domain, hosting/server, email bisnis, layanan pihak ketiga, dan perpanjangan tahun berikutnya dibayar terpisah oleh klien. Nominal dan masa aktif dikonfirmasi sebelum pembayaran.\"},{\"question\":\"Apakah tersedia perawatan setelah website rilis?\",\"answer\":\"Perawatan dapat dibahas sebagai layanan berkala. Batas garansi bug, pembaruan, backup, dan pekerjaan tambahan perlu tercantum dalam penawaran.\"},{\"question\":\"Berapa lama pengerjaan website?\",\"answer\":\"Estimasi tergantung jenis website, jumlah halaman, fitur, kelengkapan materi, dan kecepatan persetujuan. Estimasi disampaikan setelah kebutuhan dibahas.\"}]', '2026-10-14 04:14:20'),
('favicon', 'uploads/favicon-9b33b8e13a753d2d.png', '2026-10-19 08:48:46'),
('google_site_verification', '-rYSpsp0t9I4hd-YT8PYFOt-hTL5g9uE77kFnG0sUu0', '2026-10-14 00:26:57'),
('logo', 'uploads/logo-9383b2a99b04e0b3.png', '2026-10-19 08:48:46'),
('meta_author', 'Rakit Technology', '2026-10-19 02:58:34'),
('og_image_url', '', '2026-10-14 00:26:06'),
('robots_index', '1', '2026-10-14 00:26:06'),
('seo_description', 'Rakit Technology membantu bisnis berkembang melalui solusi digital modern, website profesional, dan teknologi yang dirancang untuk meningkatkan efisiensi serta pertumbuhan bisnis.', '2026-10-19 02:58:34'),
('seo_enabled', '1', '2026-10-14 05:20:02'),
('seo_keywords', 'Rakit Technology, solusi digital, website bisnis, teknologi digital, website profesional', '2026-10-19 02:58:34'),
('seo_title', 'Rakit Technology — Solusi Digital untuk Bisnis Modern', '2026-10-19 02:58:34'),
('site_name', 'Rakit Technology', '2026-10-19 02:58:34'),
('site_url', 'https://invitodigital.net', '2026-10-14 00:26:06'),
('social_facebook', 'https://facebook.com/username', '2026-10-14 05:20:02'),
('social_facebook_enabled', '1', '2026-10-14 05:20:02'),
('social_gmail', 'email@domain.com', '2026-10-14 05:20:02'),
('social_gmail_enabled', '1', '2026-10-14 05:20:02'),
('social_instagram', 'https://instagram.com/username', '2026-10-14 05:20:02'),
('social_instagram_enabled', '1', '2026-10-14 05:20:02'),
('social_linkedin', 'https://linkedin.com/company/[username]', '2026-10-14 05:20:02'),
('social_linkedin_enabled', '1', '2026-10-14 05:20:02'),
('social_tiktok', 'https://tiktok.com/@username', '2026-10-14 05:20:02'),
('social_tiktok_enabled', '1', '2026-10-14 05:20:02'),
('social_x', 'https://x.com/username', '2026-10-14 05:20:02'),
('social_x_enabled', '1', '2026-10-14 05:20:02'),
('whatsapp_number', '6287734250835', '2026-10-13 21:42:06');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_published_order` (`is_published`,`sort_order`,`created_at`);

--
-- Indeks untuk tabel `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_images` (`project_id`,`sort_order`,`id`);

--
-- Indeks untuk tabel `service_packages`
--
ALTER TABLE `service_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active_order` (`is_active`,`category`,`sort_order`);

--
-- Indeks untuk tabel `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `service_packages`
--
ALTER TABLE `service_packages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
