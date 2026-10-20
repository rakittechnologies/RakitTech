<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$uploadDir = dirname(__DIR__) . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['site_name'] ?? ''));
    $whatsapp = preg_replace('/\D+/', '', (string)($_POST['whatsapp_number'] ?? ''));
    if ($name === '' || mb_strlen($name) > 80) $error = 'Nama website wajib diisi, maksimal 80 karakter.';
    if (!$error && ($whatsapp === '' || strlen($whatsapp) < 8 || strlen($whatsapp) > 15)) $error = 'Nomor WhatsApp wajib diisi dengan kode negara, 8–15 digit angka. Contoh: 6281234567890.';
    $saved = [];
    foreach (['logo' => 'logo', 'favicon' => 'favicon'] as $field => $key) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) continue;
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload ' . $field . ' gagal.'; break; }
        if ($file['size'] > 3 * 1024 * 1024) { $error = 'Ukuran gambar maksimal 3 MB.'; break; }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp','image/svg+xml'=>'svg','image/x-icon'=>'ico','image/vnd.microsoft.icon'=>'ico'];
        if (!isset($allowed[$mime])) { $error = 'Format gambar harus PNG, JPG, WEBP, SVG, atau ICO.'; break; }
        if ($mime === 'image/svg+xml') {
            $svg = file_get_contents($file['tmp_name']);
            if ($svg === false || preg_match('/<script|onload\s*=|javascript:|<foreignObject/i', $svg)) { $error = 'File SVG mengandung konten yang tidak diizinkan.'; break; }
        }
        $filename = $key . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) { $error = 'Gagal menyimpan gambar. Periksa izin folder uploads.'; break; }
        $saved[$key] = 'uploads/' . $filename;
    }
    $seoFields = [
        'seo_enabled' => isset($_POST['seo_enabled']) ? '1' : '0',
        'seo_title' => trim((string)($_POST['seo_title'] ?? '')),
        'seo_description' => trim((string)($_POST['seo_description'] ?? '')),
        'seo_keywords' => trim((string)($_POST['seo_keywords'] ?? '')),
        'site_url' => rtrim(normalize_http_url((string)($_POST['site_url'] ?? '')), '/'),
        'og_image_url' => normalize_http_url((string)($_POST['og_image_url'] ?? '')),
        'robots_index' => isset($_POST['robots_index']) ? '1' : '0',
        'google_site_verification' => trim((string)($_POST['google_site_verification'] ?? '')),
        'bing_site_verification' => trim((string)($_POST['bing_site_verification'] ?? '')),
        'meta_author' => trim((string)($_POST['meta_author'] ?? '')),
    ];
    $socialFields = [
        'social_instagram_enabled' => isset($_POST['social_instagram_enabled']) ? '1' : '0',
        'social_linkedin_enabled' => isset($_POST['social_linkedin_enabled']) ? '1' : '0',
        'social_x_enabled' => isset($_POST['social_x_enabled']) ? '1' : '0',
        'social_facebook_enabled' => isset($_POST['social_facebook_enabled']) ? '1' : '0',
        'social_tiktok_enabled' => isset($_POST['social_tiktok_enabled']) ? '1' : '0',
        'social_gmail_enabled' => isset($_POST['social_gmail_enabled']) ? '1' : '0',
        'social_instagram' => normalize_http_url((string)($_POST['social_instagram'] ?? '')),
        'social_linkedin' => normalize_http_url((string)($_POST['social_linkedin'] ?? '')),
        'social_x' => normalize_http_url((string)($_POST['social_x'] ?? '')),
        'social_facebook' => normalize_http_url((string)($_POST['social_facebook'] ?? '')),
        'social_tiktok' => normalize_http_url((string)($_POST['social_tiktok'] ?? '')),
        'social_gmail' => trim((string)($_POST['social_gmail'] ?? '')),
    ];
    // Validasi hanya nilai URL/email, bukan field switcher (yang nilainya memang '1' atau '0').
    foreach (['social_instagram', 'social_linkedin', 'social_x', 'social_facebook', 'social_tiktok'] as $socialKey) {
        $socialValue = $socialFields[$socialKey];
        if ($socialValue !== '' && !safe_http_url($socialValue)) {
            $error = 'URL media sosial harus menggunakan alamat http atau https yang valid.';
            break;
        }
    }
    if (!$error && $socialFields['social_gmail'] !== '' && !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $socialFields['social_gmail'])) {
        $error = 'Alamat Gmail tidak valid.';
    }
    foreach (['site_url', 'og_image_url'] as $urlField) {
        if ($seoFields[$urlField] !== '' && !safe_http_url($seoFields[$urlField])) {
            $error = ($urlField === 'site_url' ? 'URL website' : 'URL gambar Open Graph') . ' harus menggunakan alamat http atau https yang valid.';
            break;
        }
    }
    if (mb_strlen($seoFields['seo_title']) > 250 || mb_strlen($seoFields['seo_description']) > 1000 || mb_strlen($seoFields['seo_keywords']) > 2000 || mb_strlen($seoFields['google_site_verification']) > 500 || mb_strlen($seoFields['bing_site_verification']) > 500 || mb_strlen($seoFields['meta_author']) > 250) {
        $error = 'Salah satu isian SEO terlalu panjang.';
    }
    if (!$error) {
        $upsert = $pdo->prepare('INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        $upsert->execute(['site_name', $name]);
        $upsert->execute(['whatsapp_number', $whatsapp]);
        foreach ($saved as $key => $value) $upsert->execute([$key, $value]);
        foreach ($seoFields as $key => $value) $upsert->execute([$key, $value]);
        foreach ($socialFields as $key => $value) $upsert->execute([$key, $value]);
        flash('Pengaturan website berhasil disimpan.');
        header('Location: settings.php'); exit;
    }
}
$notice = take_flash();
$siteName = setting('site_name', 'RuangWeb');
$whatsappNumber = setting('whatsapp_number', $config['whatsapp_number'] ?? '');
$logo = setting('logo');
$favicon = setting('favicon');
$seoEnabled = setting('seo_enabled', '1') !== '0';
$seoTitle = setting('seo_title', '');
$seoDescription = setting('seo_description', '');
$seoKeywords = setting('seo_keywords', '');
$siteUrl = setting('site_url', '');
$ogImageUrl = setting('og_image_url', '');
$robotsIndex = setting('robots_index', '1');
$googleVerification = setting('google_site_verification', '');
$bingVerification = setting('bing_site_verification', '');
$metaAuthor = setting('meta_author', '');
$socialInstagram = setting('social_instagram', '');
$socialLinkedin = setting('social_linkedin', '');
$socialX = setting('social_x', '');
$socialFacebook = setting('social_facebook', '');
$socialTiktok = setting('social_tiktok', '');
$socialGmail = setting('social_gmail', '');
$socialInstagramEnabled = setting('social_instagram_enabled', '1') !== '0';
$socialLinkedinEnabled = setting('social_linkedin_enabled', '1') !== '0';
$socialXEnabled = setting('social_x_enabled', '1') !== '0';
$socialFacebookEnabled = setting('social_facebook_enabled', '1') !== '0';
$socialTiktokEnabled = setting('social_tiktok_enabled', '1') !== '0';
$socialGmailEnabled = setting('social_gmail_enabled', '1') !== '0';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pengaturan Website</title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body>
<header class="admin-top"><div><strong><?= e($siteName) ?> / Admin</strong><span>Pengaturan identitas website</span></div><nav><a href="index.php">Portofolio</a><a href="packages.php">Layanan & Harga</a><a href="faq.php">FAQ</a><a href="settings.php" aria-current="page">Pengaturan Website</a><a href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a><a href="logout.php">Keluar</a></nav></header>
<main class="form-main"><h1>Pengaturan Website</h1><p>Atur identitas website dan nomor WhatsApp untuk semua tombol chat tanpa mengedit kode atau file konfigurasi.</p>
<?php if ($notice): ?><div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form class="editor" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nama website *<input name="site_name" required maxlength="80" value="<?= e($siteName) ?>" placeholder="Contoh: RuangWeb"></label>
<label>Nomor WhatsApp untuk chat *<input name="whatsapp_number" type="tel" inputmode="numeric" autocomplete="tel" required minlength="8" maxlength="20" pattern="[+0-9 ()-]{8,20}" value="<?= e($whatsappNumber) ?>" placeholder="Contoh: 6281234567890"><small>Gunakan kode negara, misalnya 62 untuk Indonesia. Boleh memakai tanda +, spasi, atau tanda hubung; sistem akan menyimpan angka saja. Nomor ini dipakai untuk seluruh tombol WhatsApp di website.</small></label>
<div class="form-grid">
<label>Logo website (PNG, JPG, WEBP, SVG)<input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml"><small>Disarankan PNG transparan atau SVG. Maksimal 3 MB. Kosongkan jika tidak ingin mengganti logo.</small><?php if ($logo): ?><div class="current-image"><img src="../<?= e($logo) ?>" alt="Logo saat ini" style="max-width:220px;max-height:90px;object-fit:contain"><small>Logo saat ini</small></div><?php endif; ?></label>
<label>Ikon tab browser / favicon (PNG, ICO, JPG, WEBP)<input type="file" name="favicon" accept=".png,.jpg,.jpeg,.webp,.ico,image/png,image/jpeg,image/webp,image/x-icon"><small>Disarankan gambar persegi 32×32 atau 64×64. Maksimal 3 MB.</small><?php if ($favicon): ?><div class="current-image"><img src="../<?= e($favicon) ?>" alt="Favicon saat ini" style="width:48px;height:48px;object-fit:contain"><small>Ikon saat ini</small></div><?php endif; ?></label>
</div>
<section class="editor-section" style="margin-top:28px"><div class="seo-section-heading"><div><h2>Social Media Kami</h2><p>Aktifkan platform yang ingin ditampilkan. Isian akan muncul saat switcher platform aktif.</p></div></div><div class="social-admin-fields" id="socialAdminFields">
<label class="social-admin-item"><span class="social-admin-item-heading">Instagram<span class="seo-toggle"><input type="checkbox" name="social_instagram_enabled" value="1" <?= $socialInstagramEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="text" inputmode="url" name="social_instagram" value="<?= e($socialInstagram) ?>" placeholder="instagram.com/username"></label>
<label class="social-admin-item"><span class="social-admin-item-heading">LinkedIn<span class="seo-toggle"><input type="checkbox" name="social_linkedin_enabled" value="1" <?= $socialLinkedinEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="text" inputmode="url" name="social_linkedin" value="<?= e($socialLinkedin) ?>" placeholder="linkedin.com/company/username"></label>
<label class="social-admin-item"><span class="social-admin-item-heading">X / Twitter<span class="seo-toggle"><input type="checkbox" name="social_x_enabled" value="1" <?= $socialXEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="text" inputmode="url" name="social_x" value="<?= e($socialX) ?>" placeholder="x.com/username"></label>
<label class="social-admin-item"><span class="social-admin-item-heading">Facebook<span class="seo-toggle"><input type="checkbox" name="social_facebook_enabled" value="1" <?= $socialFacebookEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="text" inputmode="url" name="social_facebook" value="<?= e($socialFacebook) ?>" placeholder="facebook.com/username"></label>
<label class="social-admin-item"><span class="social-admin-item-heading">TikTok<span class="seo-toggle"><input type="checkbox" name="social_tiktok_enabled" value="1" <?= $socialTiktokEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="text" inputmode="url" name="social_tiktok" value="<?= e($socialTiktok) ?>" placeholder="tiktok.com/@username"></label>
<label class="social-admin-item"><span class="social-admin-item-heading">Gmail<span class="seo-toggle"><input type="checkbox" name="social_gmail_enabled" value="1" <?= $socialGmailEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></span><input type="email" name="social_gmail" value="<?= e($socialGmail) ?>" placeholder="email@domain.com"><small>Ditampilkan sebagai tautan email, bukan URL profil.</small></label>
</div></section>
<section class="editor-section" style="margin-top:28px"><div class="seo-section-heading"><div><h2>SEO &amp; Meta Tag</h2><p>Aktifkan untuk mengatur SEO, preview berbagi, dan kode verifikasi.</p></div><label class="seo-switch-label" aria-label="Aktifkan SEO dan Meta Tag"><input type="checkbox" name="seo_enabled" id="seoEnabledToggle" value="1" <?= $seoEnabled ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></label></div><div id="seoFields" <?= $seoEnabled ? '' : 'hidden' ?>>
<label>Judul SEO<input name="seo_title" maxlength="250" value="<?= e($seoTitle) ?>" placeholder="Kosongkan untuk memakai nama website"><small>Judul halaman utama di hasil pencarian dan preview tautan.</small></label>
<label>Deskripsi SEO<textarea name="seo_description" rows="3" maxlength="1000" placeholder="Jelaskan layanan website kamu secara singkat"><?= e($seoDescription) ?></textarea><small>Disarankan sekitar 150–160 karakter.</small></label>
<label>Kata kunci SEO<input name="seo_keywords" maxlength="2000" value="<?= e($seoKeywords) ?>" placeholder="jasa website, pembuatan website, landing page"><small>Opsional. Mesin pencari modern tidak banyak menggunakan meta keywords.</small></label>
<label>URL utama website<input name="site_url" type="text" inputmode="url" value="<?= e($siteUrl) ?>" placeholder="domainkamu.com"><small>Dipakai untuk canonical dan URL halaman saat dibagikan. Masukkan domain utama tanpa garis miring di akhir.</small></label>
<label>URL gambar preview sosial / Open Graph<input name="og_image_url" type="text" inputmode="url" value="<?= e($ogImageUrl) ?>" placeholder="domainkamu.com/uploads/preview.jpg"><small>Gunakan URL gambar publik yang bisa diakses tanpa login; disarankan 1200 × 630 px.</small></label>
<label>Nama penulis / brand<input name="meta_author" maxlength="250" value="<?= e($metaAuthor) ?>" placeholder="Nama brand atau pemilik website"></label>
<label class="seo-index-switch-row"><span><strong>Izinkan mesin pencari mengindeks website</strong><small>Jika dinonaktifkan, verifikasi mesin pencari disembunyikan dan website menggunakan noindex.</small></span><span class="seo-toggle"><input type="checkbox" name="robots_index" id="robotsIndexToggle" value="1" <?= $robotsIndex !== '0' ? 'checked' : '' ?>><span class="seo-switch-ui" aria-hidden="true"></span></span></label>
<div id="seoIndexFields" <?= $robotsIndex !== '0' ? '' : 'hidden' ?>><h3>Verifikasi mesin pencari</h3>
<label>Google Search Console — kode verifikasi<input name="google_site_verification" maxlength="500" value="<?= e($googleVerification) ?>" placeholder="Kode dari Google Search Console"><small>Masukkan hanya nilai content dari tag meta Google, bukan seluruh tag HTML.</small></label>
<label>Bing Webmaster Tools — kode verifikasi<input name="bing_site_verification" maxlength="500" value="<?= e($bingVerification) ?>" placeholder="Kode verifikasi Bing"><small>Masukkan hanya nilai content dari tag meta Bing.</small></label></div>
</div></div></section>
<div class="form-actions"><button class="button primary" type="submit">Simpan pengaturan</button><a class="button" href="index.php">Kembali</a></div><script>
(() => {
 const form = document.querySelector('form.editor'); if (!form) return;
 const fields = [...form.querySelectorAll('input[inputmode="url"]')];
 const normalize = input => { const value = input.value.trim(); if (!value) return; if (value.startsWith('//')) input.value = 'https:' + value; else if (!/^[a-z][a-z0-9+.-]*:\/\//i.test(value)) input.value = 'https://' + value; };
 fields.forEach(input => input.addEventListener('blur', () => normalize(input)));
 form.addEventListener('submit', () => fields.forEach(normalize));
})();
</script></form>
</main><script>
(()=>{const toggle=document.getElementById('seoEnabledToggle'),fields=document.getElementById('seoFields'),indexToggle=document.getElementById('robotsIndexToggle'),indexFields=document.getElementById('seoIndexFields');const syncSeo=()=>{if(toggle&&fields)fields.hidden=!toggle.checked};const syncIndex=()=>{if(indexToggle&&indexFields)indexFields.hidden=!indexToggle.checked};const syncSocialItems=()=>{document.querySelectorAll('.social-admin-item').forEach(item=>{const switchInput=item.querySelector('.seo-toggle input[type="checkbox"]');const fields=[...item.querySelectorAll(':scope > input, :scope > small')];fields.forEach(field=>field.hidden=!!switchInput&&!switchInput.checked)})};toggle?.addEventListener('change',syncSeo);indexToggle?.addEventListener('change',syncIndex);document.querySelectorAll('.social-admin-item .seo-toggle input').forEach(input=>input.addEventListener('change',syncSocialItems));syncSeo();syncIndex();syncSocialItems()})();
</script></body></html>