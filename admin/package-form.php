<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$package = ['category'=>'Website Statis','name'=>'','description'=>'','price'=>'','discount_price'=>'','features'=>'','ribbon'=>'','full_description'=>'','suitable_for'=>'','work_duration'=>'','revision_terms'=>'','included_items'=>'','excluded_items'=>'','is_active'=>1,'sort_order'=>0];
if ($id) { $stmt=$pdo->prepare('SELECT * FROM service_packages WHERE id=?'); $stmt->execute([$id]); $found=$stmt->fetch(); if (!$found) { http_response_code(404); exit('Paket tidak ditemukan.'); } $package=$found; }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $package['category'] = in_array($_POST['category'] ?? '', ['Website Statis','Website Dinamis'], true) ? $_POST['category'] : 'Website Statis';
    $package['name'] = trim((string)($_POST['name'] ?? ''));
    $package['description'] = trim((string)($_POST['description'] ?? ''));
    $priceRaw = preg_replace('/[^0-9]/', '', (string)($_POST['price'] ?? ''));
    $package['price'] = $priceRaw === '' ? '' : (int)$priceRaw;
    $discountRaw = preg_replace('/[^0-9]/', '', (string)($_POST['discount_price'] ?? ''));
    $package['discount_price'] = $discountRaw === '' ? '' : (int)$discountRaw;
    $package['features'] = trim((string)($_POST['features'] ?? ''));
    $package['ribbon'] = trim((string)($_POST['ribbon'] ?? ''));
    foreach (['full_description','suitable_for','work_duration','revision_terms','included_items','excluded_items'] as $detailField) { $package[$detailField] = trim((string)($_POST[$detailField] ?? '')); }
    $package['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $package['sort_order'] = (int)($_POST['sort_order'] ?? 0);
    if ($package['name']==='') $errors[]='Nama paket wajib diisi.';
    if ($package['description']==='') $errors[]='Deskripsi wajib diisi.';
    if ($package['price']==='' || $package['price']<0) $errors[]='Harga wajib diisi dengan angka.';
    if ($package['features']==='') $errors[]='Isi setidaknya satu fitur, satu fitur per baris.';
    if ($package['discount_price'] !== '' && (int)$package['discount_price'] >= (int)$package['price']) $errors[]='Harga diskon harus lebih rendah daripada harga awal. Kosongkan jika tidak ada diskon.';
    if (!$errors) {
        $nullable = static fn(string $value) => $value !== '' ? $value : null;
        $data=[$package['category'],$package['name'],$package['description'],(int)$package['price'],($package['discount_price'] === '' ? null : (int)$package['discount_price']),$package['features'],$nullable($package['ribbon']),$nullable($package['full_description']),$nullable($package['suitable_for']),$nullable($package['work_duration']),$nullable($package['revision_terms']),$nullable($package['included_items']),$nullable($package['excluded_items']),$package['is_active'],$package['sort_order']];
        if ($id) { $data[]=$id; $stmt=$pdo->prepare('UPDATE service_packages SET category=?,name=?,description=?,price=?,discount_price=?,features=?,ribbon=?,full_description=?,suitable_for=?,work_duration=?,revision_terms=?,included_items=?,excluded_items=?,is_active=?,sort_order=? WHERE id=?'); $stmt->execute($data); flash('Paket berhasil diperbarui.'); }
        else { $stmt=$pdo->prepare('INSERT INTO service_packages (category,name,description,price,discount_price,features,ribbon,full_description,suitable_for,work_duration,revision_terms,included_items,excluded_items,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'); $stmt->execute($data); flash('Paket berhasil ditambahkan.'); }
        header('Location: packages.php'); exit;
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $id?'Edit':'Tambah' ?> Paket — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body>
<header class="admin-top"><div><strong><?= e(setting('site_name', 'RuangWeb')) ?> / Admin</strong><span>Kelola layanan dan harga</span></div><nav><a href="index.php">Portofolio</a><a href="packages.php">Layanan & Harga</a><a href="faq.php">FAQ</a><a href="settings.php">Pengaturan Website</a><a href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a><a href="logout.php">Keluar</a></nav></header>
<main class="form-main"><a class="back-link" href="packages.php">← Kembali ke daftar paket</a><h1><?= $id?'Edit paket':'Tambah paket' ?></h1><p>Data ini akan digunakan pada kartu layanan dan pilihan paket di formulir konsultasi.</p>
<?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="editor" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="form-grid"><label>Kategori<select name="category"><option <?= $package['category']==='Website Statis'?'selected':'' ?>>Website Statis</option><option <?= $package['category']==='Website Dinamis'?'selected':'' ?>>Website Dinamis</option></select></label>
<label>Nama paket<input name="name" required value="<?= e((string)$package['name']) ?>" placeholder="Contoh: Landing Page"></label></div>
<label>Deskripsi<textarea name="description" rows="3" required><?= e((string)$package['description']) ?></textarea></label>
<div class="form-grid"><label>Harga awal (Rupiah)<input name="price" inputmode="numeric" required value="<?= e((string)$package['price']) ?>" placeholder="750000"><small>Harga normal sebelum diskon. Masukkan angka saja.</small></label><label>Harga diskon (opsional)<input name="discount_price" inputmode="numeric" value="<?= e((string)($package['discount_price'] ?? '')) ?>" placeholder="Contoh: 650000"><small>Kosongkan jika tidak ada diskon. Harus lebih rendah dari harga awal.</small></label></div><div class="form-grid"><label>Label khusus (opsional)<input name="ribbon" value="<?= e((string)($package['ribbon'] ?? '')) ?>" placeholder="Contoh: Pilihan populer"></label></div>
<label>Fitur paket <textarea name="features" rows="8" required placeholder="Satu fitur per baris"><?= e((string)$package['features']) ?></textarea><small>Tulis satu fitur di setiap baris.</small></label>
<div class="info"><strong>Detail popup (opsional)</strong><br>Informasi di bawah ini tampil pada popup detail layanan. Kosongkan jika tidak dibutuhkan.</div>
<label>Deskripsi lengkap <textarea name="full_description" rows="4" placeholder="Penjelasan lebih lengkap tentang layanan"><?= e((string)($package['full_description'] ?? '')) ?></textarea></label>
<label>Cocok untuk <textarea name="suitable_for" rows="3" placeholder="Contoh: UMKM, bisnis jasa, atau promosi produk"><?= e((string)($package['suitable_for'] ?? '')) ?></textarea></label>
<label>Durasi pengerjaan <input name="work_duration" value="<?= e((string)($package['work_duration'] ?? '')) ?>" placeholder="Contoh: 7–14 hari kerja"></label>
<label>Ketentuan revisi <textarea name="revision_terms" rows="3" placeholder="Contoh: 2 kali revisi desain sesuai ruang lingkup paket"><?= e((string)($package['revision_terms'] ?? '')) ?></textarea></label>
<label>Yang termasuk <textarea name="included_items" rows="4" placeholder="Satu item per baris"><?= e((string)($package['included_items'] ?? '')) ?></textarea><small>Satu item per baris.</small></label>
<label>Yang tidak termasuk <textarea name="excluded_items" rows="4" placeholder="Satu item per baris"><?= e((string)($package['excluded_items'] ?? '')) ?></textarea><small>Satu item per baris. Kolom kosong tidak akan ditampilkan.</small></label>
<div class="form-grid"><label>Urutan tampil<input type="number" name="sort_order" value="<?= (int)$package['sort_order'] ?>"><small>Angka lebih kecil tampil lebih dahulu dalam kategori.</small></label><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" <?= $package['is_active']?'checked':'' ?>> Aktif — tampil di website</label></div>
<div class="form-actions"><button class="button primary" type="submit">Simpan paket</button><a class="button" href="packages.php">Batal</a></div></form></main></body></html>
