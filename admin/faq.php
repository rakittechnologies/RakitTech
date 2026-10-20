<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$error = '';
$defaults = [
    ['question' => 'Berapa biaya pembuatan website?', 'answer' => 'Biaya menyesuaikan jumlah halaman, desain, fitur, kebutuhan admin, integrasi, domain, dan hosting. Setelah konsultasi, kamu akan mendapat rincian penawaran terlebih dahulu.'],
    ['question' => 'Apakah website bisa dibuka lewat HP?', 'answer' => 'Ya. Desain dibuat responsif agar tata letak menyesuaikan ukuran layar HP, tablet, maupun komputer.'],
    ['question' => 'Apa perbedaan website statis dan dinamis?', 'answer' => 'Website statis cocok untuk informasi yang jarang berubah. Website dinamis menyediakan pengelolaan konten atau fitur interaktif seperti dashboard admin, booking, produk, dan transaksi.'],
    ['question' => 'Apakah saya bisa mengubah isi website sendiri?', 'answer' => 'Bisa jika website dibuat dengan dashboard admin atau sistem pengelolaan konten. Untuk website statis, perubahan konten dapat dibantu sebagai layanan pembaruan.'],
    ['question' => 'Apakah demo portofolio bisa dicoba?', 'answer' => 'Bisa jika tautan demo tersedia. Demo digunakan untuk melihat contoh tampilan dan fitur, bukan untuk mengakses panel admin atau data internal.'],
    ['question' => 'Bagaimana sistem pembayaran proyek?', 'answer' => 'Pembayaran mengikuti penawaran yang disepakati. Untuk proyek pembuatan website, pembayaran dapat menggunakan DP dan pelunasan sesuai tahap kerja yang tertulis dalam kesepakatan.'],
    ['question' => 'Siapa pemilik website setelah selesai?', 'answer' => 'Kepemilikan mengikuti model kerja sama dan perjanjian. Pada model Project-Based, website dan aset yang disepakati diserahkan setelah pembayaran lunas.'],
    ['question' => 'Apakah domain dan hosting sudah termasuk?', 'answer' => 'Jangan menganggap domain atau hosting otomatis termasuk. Keduanya hanya termasuk jika tertulis jelas pada rincian paket atau penawaran. Jika tidak tercantum, biaya domain, hosting/server, email bisnis, layanan pihak ketiga, dan perpanjangan tahun berikutnya dibayar terpisah oleh klien. Nominal dan masa aktif dikonfirmasi sebelum pembayaran.'],
    ['question' => 'Apakah tersedia perawatan setelah website rilis?', 'answer' => 'Perawatan dapat dibahas sebagai layanan berkala. Batas garansi bug, pembaruan, backup, dan pekerjaan tambahan perlu tercantum dalam penawaran.'],
    ['question' => 'Berapa lama pengerjaan website?', 'answer' => 'Estimasi tergantung jenis website, jumlah halaman, fitur, kelengkapan materi, dan kecepatan persetujuan. Estimasi disampaikan setelah kebutuhan dibahas.'],
];
$stored = json_decode((string)setting('faq_items', ''), true);
$faqItems = is_array($stored) ? $stored : $defaults;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 verify_csrf();
 $questions = $_POST['faq_question'] ?? [];
 $answers = $_POST['faq_answer'] ?? [];
 $items = [];
 if (is_array($questions) && is_array($answers)) {
  foreach ($questions as $i => $question) {
   $question = trim((string)$question); $answer = trim((string)($answers[$i] ?? ''));
   if ($question === '' && $answer === '') continue;
   if ($question === '' || $answer === '') { $error = 'Setiap FAQ harus memiliki pertanyaan dan jawaban, atau kosongkan keduanya.'; break; }
   if (mb_strlen($question) > 250 || mb_strlen($answer) > 3000) { $error = 'Pertanyaan maksimal 250 karakter dan jawaban maksimal 3.000 karakter.'; break; }
   if (count($items) >= 20) { $error = 'Maksimal 20 FAQ.'; break; }
   $items[] = ['question' => $question, 'answer' => $answer];
  }
 }
 if (!$error) {
  $upsert = $pdo->prepare('INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
  $upsert->execute(['faq_items', json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
  flash('FAQ berhasil disimpan.'); header('Location: faq.php'); exit;
 }
 $faqItems = [];
 foreach (($questions ?? []) as $i => $question) $faqItems[] = ['question' => (string)$question, 'answer' => (string)($answers[$i] ?? '')];
}
$notice = take_flash();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kelola FAQ — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body>
<header class="admin-top"><div><strong><?= e(setting('site_name', 'RuangWeb')) ?> / Admin</strong><span>Kelola pertanyaan yang tampil di halaman Home</span></div><nav><a href="index.php">Portofolio</a><a href="packages.php">Layanan & Harga</a><a href="faq.php" aria-current="page">FAQ</a><a href="settings.php">Pengaturan Website</a><a href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a><a href="logout.php">Keluar</a></nav></header>
<main class="form-main"><div class="page-heading"><div><h1>Kelola FAQ</h1><p>Atur pertanyaan dan jawaban yang tampil pada bagian FAQ di Home. Maksimal 20 item.</p></div></div>
<?php if ($notice): ?><div class="alert success"><?= e($notice['message']) ?></div><?php endif; ?><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form class="editor" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="seo-section-heading"><div><h2>Daftar pertanyaan</h2><p>FAQ yang kosong tidak akan ditampilkan di website.</p></div><button class="button" type="button" id="addFaqRow">+ Tambah FAQ</button></div>
<div id="faqAdminList" class="faq-admin-list"><?php foreach ($faqItems as $i => $faq): ?><div class="faq-admin-row"><div class="faq-admin-row-top"><strong>FAQ <span class="faq-admin-number"><?= $i + 1 ?></span></strong><button type="button" class="button faq-remove-button">Hapus</button></div><label>Pertanyaan<input name="faq_question[]" maxlength="250" value="<?= e((string)($faq['question'] ?? '')) ?>" placeholder="Contoh: Berapa lama proses pembuatan website?"></label><label>Jawaban<textarea name="faq_answer[]" rows="3" maxlength="3000" placeholder="Tuliskan jawaban yang jelas dan ringkas."><?= e((string)($faq['answer'] ?? '')) ?></textarea></label></div><?php endforeach; ?></div>
<template id="faqRowTemplate"><div class="faq-admin-row"><div class="faq-admin-row-top"><strong>FAQ <span class="faq-admin-number"></span></strong><button type="button" class="button faq-remove-button">Hapus</button></div><label>Pertanyaan<input name="faq_question[]" maxlength="250" placeholder="Contoh: Berapa lama proses pembuatan website?"></label><label>Jawaban<textarea name="faq_answer[]" rows="3" maxlength="3000" placeholder="Tuliskan jawaban yang jelas dan ringkas."></textarea></label></div></template>
<div class="form-actions"><button class="button primary" type="submit">Simpan FAQ</button><a class="button" href="index.php">Kembali</a></div></form></main>
<script>(()=>{const list=document.getElementById('faqAdminList'),tpl=document.getElementById('faqRowTemplate'),add=document.getElementById('addFaqRow');const sync=()=>{[...list.querySelectorAll('.faq-admin-row')].forEach((r,i)=>{r.querySelector('.faq-admin-number').textContent=i+1});add.disabled=list.querySelectorAll('.faq-admin-row').length>=20};add.addEventListener('click',()=>{if(list.querySelectorAll('.faq-admin-row').length>=20)return;list.appendChild(tpl.content.cloneNode(true));sync()});list.addEventListener('click',e=>{const b=e.target.closest('.faq-remove-button');if(b){b.closest('.faq-admin-row').remove();sync()}});sync()})();</script></body></html>
