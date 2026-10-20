<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
// Upgrade legacy ENUM to HEX-capable color storage when database permissions allow it.
try {
    $colorColumnType = $pdo->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'theme_color'")->fetchColumn();
    if (strtolower((string)$colorColumnType) === 'enum') {
        $pdo->exec("ALTER TABLE projects MODIFY theme_color VARCHAR(7) NOT NULL DEFAULT '#5B77EB'");
        $pdo->exec("UPDATE projects SET theme_color = CASE theme_color WHEN 'blue' THEN '#5B77EB' WHEN 'green' THEN '#18AE70' WHEN 'orange' THEN '#F58B2C' WHEN 'pink' THEN '#E15B9E' WHEN 'violet' THEN '#8B5CF6' ELSE theme_color END WHERE theme_color IN ('blue','green','orange','pink','violet')");
    }
} catch (Throwable $migrationError) { /* Hosting can apply the ALTER statement from database.sql if needed. */ }
$rawId = $_GET['id'] ?? null;
$id = (is_scalar($rawId) && filter_var((string)$rawId, FILTER_VALIDATE_INT) !== false) ? (int)$rawId : 0;
if ($id < 0) $id = 0;
$project = ['title'=>'','category'=>'','website_type'=>'Website Statis','sales_method'=>'Project-Based','project_status'=>'Demo / contoh konsep','description'=>'','demo_url'=>'','image_path'=>null,'features'=>'','advantages'=>'','demo_access'=>'','notes'=>'','system_requirements'=>'','theme_color'=>'#5B77EB','is_published'=>1,'sort_order'=>0];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]); $existing = $stmt->fetch();
    if (!$existing) { flash('Proyek tidak ditemukan.', 'error'); header('Location: index.php'); exit; }
    $project = $existing;
}
function get_project_images(PDO $pdo, int $projectId): array {
    $stmt = $pdo->prepare('SELECT id, image_path, sort_order FROM project_images WHERE project_id=? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$projectId]);
    return $stmt->fetchAll();
}
$galleryImages = $id ? get_project_images($pdo, $id) : [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $type = (string)($_POST['website_type'] ?? 'Website Statis');
    $salesMethod = trim((string)($_POST['sales_method'] ?? 'Project-Based'));
    $projectStatus = trim((string)($_POST['project_status'] ?? 'Demo / contoh konsep'));
    $allowedProjectStatuses = ['Demo / contoh konsep','Proyek internal','Digunakan klien'];
    if (!in_array($projectStatus, $allowedProjectStatuses, true)) $projectStatus = 'Demo / contoh konsep';
    $description = trim((string)($_POST['description'] ?? ''));
    $demoUrl = normalize_http_url((string)($_POST['demo_url'] ?? ''));
    $features = trim((string)($_POST['features'] ?? ''));
    $advantages = trim((string)($_POST['advantages'] ?? ''));
    $demoAccess = trim((string)($_POST['demo_access'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));
    $systemRequirements = trim((string)($_POST['system_requirements'] ?? ''));
    $color = strtoupper(trim((string)($_POST['theme_color'] ?? '#5B77EB')));
    $published = isset($_POST['is_published']) ? 1 : 0;
    $sortOrder = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
    $allowedTypes = ['Website Statis','Website Dinamis'];
    $allowedSalesMethods = ['Project-Based','Website Retainer','Website as a Service (WaaS)','SaaS'];
    $allowedColors = ['#5B77EB','#18AE70','#F58B2C','#E15B9E','#8B5CF6','#0F172A','#0D9488','#F59E0B','#EF4444','#F3F4F6'];
    $removeIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['remove_images'] ?? [])), fn($v) => $v > 0)));
    $currentImages = $galleryImages;
    if ($removeIds && $id) $currentImages = array_values(array_filter($currentImages, fn($img) => !in_array((int)$img['id'], $removeIds, true)));
    $newFiles = [];
    if (isset($_FILES['project_images']) && is_array($_FILES['project_images']['name'] ?? null)) {
        foreach ($_FILES['project_images']['name'] as $i => $name) {
            $err = $_FILES['project_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($err === UPLOAD_ERR_NO_FILE) continue;
            if ($err !== UPLOAD_ERR_OK) { $error = 'Salah satu gambar gagal diunggah. Coba lagi.'; break; }
            if (($_FILES['project_images']['size'][$i] ?? 0) > 5 * 1024 * 1024) { $error = 'Setiap gambar maksimal 5 MB.'; break; }
            $tmp = $_FILES['project_images']['tmp_name'][$i] ?? '';
            $mime = $tmp && is_uploaded_file($tmp) ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : '';
            $types = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($types[$mime])) { $error = 'Semua gambar harus berformat JPG, PNG, atau WEBP.'; break; }
            $newFiles[] = ['tmp'=>$tmp, 'ext'=>$types[$mime]];
        }
    }
    if (!$error && count($currentImages) + count($newFiles) > 9) $error = 'Maksimal 9 gambar per proyek. Hapus beberapa gambar lama atau kurangi gambar baru.';
    if (!$error && $title === '') $error = 'Nama proyek wajib diisi.';
    elseif (!$error && !in_array($type, $allowedTypes, true)) $error = 'Jenis website tidak valid.';
    elseif (!$error && !in_array($salesMethod, $allowedSalesMethods, true)) $error = 'Metode penjualan tidak valid.';
    elseif (!$error && $description === '') $error = 'Deskripsi wajib diisi.';
    elseif (!$error && !safe_http_url($demoUrl)) $error = 'URL demo harus berupa URL HTTP atau HTTPS yang valid.';
    
    
    elseif (!$error && !preg_match('/^#[0-9A-F]{6}$/', $color)) $error = 'Warna harus berupa kode HEX 6 digit, misalnya #5B77EB.';
    elseif (!$error && $sortOrder === false) $error = 'Urutan harus berupa angka bulat.';

    if (!$error) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE projects SET title=?,category=?,website_type=?,sales_method=?,project_status=?,description=?,demo_url=?,features=?,advantages=?,demo_access=?,notes=?,system_requirements=?,theme_color=?,is_published=?,sort_order=? WHERE id=?');
            $stmt->execute([$title,$category,$type,$salesMethod,$projectStatus,$description,$demoUrl,$features,$advantages,$demoAccess,$notes,$systemRequirements,$color,$published,$sortOrder,$id]);
            if ($removeIds) {
                $marks = implode(',', array_fill(0, count($removeIds), '?'));
                $del = $pdo->prepare("DELETE FROM project_images WHERE project_id=? AND id IN ($marks)");
                $del->execute(array_merge([$id], $removeIds));
                foreach ($galleryImages as $img) if (in_array((int)$img['id'], $removeIds, true)) {
                    $path = dirname(__DIR__) . '/' . ltrim($img['image_path'], '/');
                    if (is_file($path)) @unlink($path);
                }
            }
            $projectId = $id;
        } else {
            $stmt = $pdo->prepare('INSERT INTO projects (title,category,website_type,sales_method,project_status,description,demo_url,image_path,features,advantages,demo_access,notes,system_requirements,theme_color,is_published,sort_order) VALUES (?,?,?,?,?,?,?,NULL,?,?,?,?,?,?,?,?)');
            $stmt->execute([$title,$category,$type,$salesMethod,$projectStatus,$description,$demoUrl,$features,$advantages,$demoAccess,$notes,$systemRequirements,$color,$published,$sortOrder]);
            $projectId = (int)$pdo->lastInsertId();
            if ($projectId < 1) {
                $error = 'Proyek tersimpan tanpa ID yang valid. Periksa kolom projects.id di database; pastikan bertipe AUTO_INCREMENT, lalu muat ulang halaman admin.';
            }
        }
        if (!$error && $projectId > 0) {
        $newImageIds = [];
        $uploadDir = dirname(__DIR__) . '/uploads/portfolio';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $nextOrder = count($currentImages);
        foreach ($newFiles as $file) {
            $filename = 'project-' . bin2hex(random_bytes(10)) . '.' . $file['ext'];
            if (!move_uploaded_file($file['tmp'], $uploadDir . '/' . $filename)) {
                $error = 'Gagal menyimpan salah satu gambar. Periksa izin folder uploads/portfolio.';
                break;
            }
            $path = 'uploads/portfolio/' . $filename;
            $ins = $pdo->prepare('INSERT INTO project_images (project_id,image_path,sort_order) VALUES (?,?,?)');
            $ins->execute([$projectId,$path,$nextOrder++]);
            $newImageIds[] = (int)$pdo->lastInsertId();
        }
        if (!$error) {
            $postedOrder = json_decode((string)($_POST['image_order_json'] ?? '[]'), true);
            if (is_array($postedOrder) && $postedOrder) {
                $validOrder = [];
                foreach ($postedOrder as $key) {
                    if (!is_string($key)) continue;
                    if (preg_match('/^existing:(\d+)$/', $key, $m)) {
                        $imageId = (int)$m[1];
                        foreach ($currentImages as $currentImage) if ((int)$currentImage['id'] === $imageId) { $validOrder[] = $imageId; break; }
                    } elseif (preg_match('/^new:(\d+)$/', $key, $m)) {
                        $idx = (int)$m[1];
                        if (isset($newImageIds[$idx])) $validOrder[] = $newImageIds[$idx];
                    }
                }
                $validOrder = array_values(array_unique($validOrder));
                $allImageIds = array_merge(array_map(fn($img) => (int)$img['id'], $currentImages), $newImageIds);
                foreach ($allImageIds as $imageId) if (!in_array($imageId, $validOrder, true)) $validOrder[] = $imageId;
                $sortStmt = $pdo->prepare('UPDATE project_images SET sort_order=? WHERE id=? AND project_id=?');
                foreach ($validOrder as $position => $imageId) $sortStmt->execute([$position, $imageId, $projectId]);
            }
        }
        }
        if (!$error) {
            $first = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id=? ORDER BY sort_order ASC,id ASC LIMIT 1');
            $first->execute([$projectId]);
            $coverPath = $first->fetchColumn() ?: null;
            $upd = $pdo->prepare('UPDATE projects SET image_path=? WHERE id=?');
            $upd->execute([$coverPath,$projectId]);
            flash($id ? 'Proyek berhasil diperbarui.' : 'Proyek berhasil ditambahkan.');
            header('Location: index.php'); exit;
        }
        $id = $projectId;
        $project = array_merge($project, ['title'=>$title,'category'=>$category,'website_type'=>$type,'sales_method'=>$salesMethod,'project_status'=>$projectStatus,'description'=>$description,'demo_url'=>$demoUrl,'features'=>$features,'advantages'=>$advantages,'demo_access'=>$demoAccess,'notes'=>$notes,'system_requirements'=>$systemRequirements,'theme_color'=>$color,'is_published'=>$published,'sort_order'=>$sortOrder ?: 0]);
        $galleryImages = get_project_images($pdo, $id);
    } else {
        $project = array_merge($project, ['title'=>$title,'category'=>$category,'website_type'=>$type,'sales_method'=>$salesMethod,'project_status'=>$projectStatus,'description'=>$description,'demo_url'=>$demoUrl,'features'=>$features,'advantages'=>$advantages,'demo_access'=>$demoAccess,'notes'=>$notes,'system_requirements'=>$systemRequirements,'theme_color'=>$color,'is_published'=>$published,'sort_order'=>$sortOrder ?: 0]);
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $id ? 'Edit' : 'Tambah' ?> Proyek — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"><style>
.form-grid>.website-type-field{align-self:start;align-content:start;display:flex;flex-direction:column;gap:7px;height:auto;min-height:0}.form-grid>.website-type-field>.website-type-select{display:block;width:100%;height:44px;min-height:44px;max-height:44px;flex:0 0 44px;align-self:stretch;padding:8px 12px;line-height:1.4;box-sizing:border-box}.form-grid>.theme-color-editor{align-self:start}
.gallery-manage{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:14px;margin-top:14px}.gallery-item{position:relative;cursor:grab;overflow:hidden;border:1px solid #dbe1ec;border-radius:14px;background:#fff;box-shadow:0 5px 16px rgba(15,23,42,.06)}.gallery-image-wrap{position:relative;height:132px;background:#eef2f7}.gallery-item img{display:block;width:100%;height:100%;object-fit:cover}.gallery-item-actions{position:absolute;z-index:3;inset:0;display:flex;align-items:flex-start;justify-content:flex-end;gap:5px;padding:7px;background:linear-gradient(180deg,rgba(15,23,42,.56),transparent 52%);opacity:0;transition:opacity .18s}.gallery-item:hover .gallery-item-actions,.gallery-item:focus-within .gallery-item-actions{opacity:1}.gallery-item-actions button{border:0;border-radius:7px;padding:6px 8px;background:#fff;color:#172033;font-size:.75rem;font-weight:700;cursor:pointer;box-shadow:0 2px 7px #0002}.gallery-item-actions button.danger{background:#fee2e2;color:#991b1b}.gallery-item-actions .icon-action{width:34px;height:34px;display:grid;place-items:center;padding:0;font-size:1.05rem}.gallery-item-actions .icon-action svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.gallery-item-actions .icon-action:focus-visible{outline:3px solid #818cf8;outline-offset:2px}.gallery-item-name{padding:8px 10px;font-size:.78rem;color:#475569;overflow-wrap:anywhere}.gallery-item.is-dragging{opacity:.35;outline:2px dashed #4f46e5}.gallery-item.is-drop-target{outline:2px solid #6366f1;transform:scale(.98)}.thumbnail-badge{position:absolute;left:8px;top:8px;z-index:1;pointer-events:none;background:#4f46e5;color:#fff;border-radius:6px;padding:5px 7px;font-size:.68rem;font-weight:800;letter-spacing:.04em;box-shadow:0 2px 8px #0003}.thumbnail-badge[hidden]{display:none}.drag-handle{position:absolute;left:8px;bottom:8px;z-index:2;background:#fff;color:#334155;border-radius:6px;padding:2px 7px;font-size:1.1rem;line-height:1.2;box-shadow:0 2px 8px #0003;cursor:grab}.gallery-item.is-removed{opacity:.45}.gallery-item.is-removed .gallery-image-wrap:after{content:'AKAN DIHAPUS';position:absolute;inset:0;display:grid;place-items:center;background:#7f1d1dcc;color:white;font-size:.75rem;font-weight:800;letter-spacing:.04em}.gallery-upload{border:1px dashed #9ca9bf;border-radius:16px;padding:18px;background:rgba(100,116,139,.06)}.gallery-upload input{width:100%;margin-top:8px}.gallery-help{font-size:.86rem;color:#64748b;line-height:1.6}.gallery-counter{font-weight:700;color:#4f46e5}.gallery-item input.remove-gallery-image{display:none}.gallery-drop{display:block;margin-top:15px;padding:22px;border:1px dashed #a5b4fc;border-radius:14px;background:#eef2ff;text-align:center;cursor:pointer;color:#3730a3}.gallery-drop strong{display:block}.gallery-drop small{display:block;color:#64748b;margin-top:5px}.gallery-drop input{display:none}.crop-modal,.preview-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.76)}.crop-modal.open,.preview-modal.open{display:flex}.crop-dialog,.preview-dialog{width:min(850px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:18px;padding:18px;box-shadow:0 24px 80px #0005}.crop-dialog-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}.crop-dialog-head h3{margin:0}.crop-image-area{height:min(58vh,540px);background:#e2e8f0;overflow:hidden}.crop-image-area img{display:block;max-width:100%;max-height:100%}.crop-controls,.crop-dialog-actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:13px}.crop-controls button,.crop-dialog-actions button,.preview-dialog button{border:1px solid #cbd5e1;background:#fff;color:#1e293b;padding:9px 13px;border-radius:9px;cursor:pointer;font-weight:700}.crop-dialog-actions .primary{background:#4f46e5;color:#fff;border-color:#4f46e5}.preview-dialog{width:min(1000px,100%);position:relative;text-align:center}.preview-dialog img{max-width:100%;max-height:78vh;object-fit:contain}.preview-close{position:absolute;right:12px;top:12px}.gallery-empty{font-size:.86rem;color:#64748b}
.theme-color-editor{display:flex;flex-direction:column;gap:9px;margin:12px 0 18px}.theme-color-controls{display:flex;gap:10px;align-items:center}.theme-color-controls input[type=color]{width:54px;height:44px;padding:4px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;cursor:pointer}.theme-color-controls input[type=text]{max-width:150px;font-family:ui-monospace,monospace;text-transform:uppercase}.theme-swatches{display:flex;flex-wrap:wrap;gap:9px}.theme-swatch{width:30px;height:30px;border:3px solid #fff;border-radius:50%;background:var(--swatch);box-shadow:0 0 0 1px #cbd5e1;cursor:pointer}.theme-swatch:focus-visible,.theme-swatch:hover{outline:3px solid #6366f1;outline-offset:2px}.theme-card-preview{width:min(340px,100%);overflow:hidden;border:1px solid color-mix(in srgb,var(--preview-accent) 65%,#ffffff);border-radius:14px;background:color-mix(in srgb,var(--preview-accent) 9%,#111a2e);box-shadow:0 6px 20px #0f172a16}.theme-card-preview-cover{min-height:100px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;color:#fff;background:linear-gradient(135deg,color-mix(in srgb,var(--preview-accent) 82%,#111827),color-mix(in srgb,var(--preview-accent) 48%,#111827))}.theme-card-preview-cover span{font-size:.65rem;font-weight:800;letter-spacing:.08em}.theme-card-preview-cover strong{font-size:1.2rem;overflow-wrap:anywhere}.theme-card-preview-body{padding:13px;border-top:3px solid var(--preview-accent)}.theme-card-preview-body strong{color:#fff}.theme-card-preview-body p{margin:5px 0 8px;color:#cbd5e1;font-size:.82rem}.theme-card-preview-body span{color:color-mix(in srgb,var(--preview-accent) 70%,#ffffff);font-size:.78rem;font-weight:800}
</style></head><body>
<header class="admin-top"><div><strong><?= e(setting('site_name', 'RuangWeb')) ?> / Admin</strong><span>Pengelolaan portofolio</span></div><nav><a href="index.php">Portofolio</a><a href="packages.php">Layanan & Harga</a><a href="faq.php">FAQ</a><a href="settings.php">Pengaturan Website</a><a href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a><a href="logout.php">Keluar</a></nav></header>
<main class="form-main"><h1><?= $id ? 'Edit proyek' : 'Tambah proyek' ?></h1><p>Isi informasi proyek dan tambahkan hingga 9 gambar untuk menampilkan detail hasil website dengan lebih menarik.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form class="editor" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="form-grid"><label>Nama proyek *<input name="title" required value="<?= e($project['title']) ?>" placeholder="Contoh: Website Kopi Senja"></label><label>Kategori bisnis<input name="category" value="<?= e($project['category']) ?>" placeholder="Kuliner, UMKM, Booking"></label>
<label class="website-type-field">Jenis website *<select class="website-type-select" name="website_type"><option <?= $project['website_type']==='Website Statis'?'selected':'' ?>>Website Statis</option><option <?= $project['website_type']==='Website Dinamis'?'selected':'' ?>>Website Dinamis</option></select></label>
<label>Metode penjualan *<select name="sales_method" id="salesMethodSelect" required><?php foreach (['Project-Based'=>'Jual lepas; bayar sekali atau DP, website menjadi milik klien setelah lunas.','Website Retainer'=>'Jasa perawatan berkala; website tetap milik klien.','Website as a Service (WaaS)'=>'Sewa website berlangganan; website dan sistem tetap milik agensi.','SaaS'=>'Platform pembuat website mandiri dengan paket langganan.'] as $value=>$label): ?><option value="<?= e($value) ?>" <?= (($project['sales_method'] ?? 'Project-Based')===$value || ($value==='Website Retainer' && ($project['sales_method'] ?? '')==='Website Retainer'))?'selected':'' ?>><?= e($value) ?></option><?php endforeach; ?></select><small id="salesMethodHelp">Pilih model kerja sama yang digunakan untuk proyek ini. Penjelasan lengkap tersedia di halaman utama website.</small></label>
<label>Status proyek *<select name="project_status" required><?php foreach (['Demo / contoh konsep','Proyek internal','Digunakan klien'] as $statusOption): ?><option value="<?= e($statusOption) ?>" <?= (($project['project_status'] ?? 'Demo / contoh konsep')===$statusOption)?'selected':'' ?>><?= e($statusOption) ?></option><?php endforeach; ?></select><small>Pilih sesuai kondisi sebenarnya. Jangan menandai proyek sebagai digunakan klien jika belum benar-benar dipakai klien.</small></label>
<div class="theme-color-editor"><label for="themeColorPicker">Warna kartu</label><div class="theme-color-controls"><input id="themeColorPicker" type="color" value="<?= preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$project['theme_color']) ? e($project['theme_color']) : '#5B77EB' ?>" aria-label="Pilih warna kartu"><input id="themeColorHex" name="theme_color" type="text" maxlength="7" pattern="#[0-9A-Fa-f]{6}" value="<?= preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$project['theme_color']) ? e($project['theme_color']) : '#5B77EB' ?>" aria-label="Kode warna HEX" placeholder="#5B77EB" required></div><div class="theme-swatches" aria-label="Palet warna rekomendasi"><?php foreach (['#5B77EB','#18AE70','#F58B2C','#E15B9E','#8B5CF6','#0F172A','#0D9488','#F59E0B','#EF4444','#F3F4F6'] as $swatch): ?><button type="button" class="theme-swatch" data-color="<?= e($swatch) ?>" style="--swatch:<?= e($swatch) ?>" aria-label="Gunakan warna <?= e($swatch) ?>" title="<?= e($swatch) ?>"></button><?php endforeach; ?></div><small>Pilih warna sendiri atau gunakan palet rekomendasi. Warna diterapkan pada sampul dan aksen seluruh kartu.</small><div class="theme-card-preview" id="themeCardPreview"><div class="theme-card-preview-cover"><span>PORTOFOLIO</span><strong id="themePreviewTitle"><?= e($project['title'] ?: 'Contoh Proyek') ?></strong></div><div class="theme-card-preview-body"><strong>Contoh kartu proyek</strong><p>Pratinjau warna kartu sebelum disimpan.</p><span>Detail proyek →</span></div></div></div></div>
<label>Deskripsi proyek *<textarea name="description" required rows="4" placeholder="Tujuan proyek, masalah/kebutuhan yang ingin dibantu, target pengguna, solusi yang dibuat, dan hasil pengerjaan. Hindari klaim hasil bisnis yang belum terukur."><?= e($project['description']) ?></textarea></label>
<section class="gallery-upload"><strong>Galeri gambar proyek <span class="gallery-counter" id="galleryCounter"><?= count($galleryImages) ?>/9 gambar</span></strong><p class="gallery-help">Unggah maksimal 9 gambar untuk satu proyek. Setiap gambar maksimal 5 MB. Gunakan screenshot halaman beranda, katalog, dashboard, atau tampilan mobile agar portofolio terlihat profesional.</p>
<div class="gallery-manage" id="existingGallery"><?php foreach ($galleryImages as $position => $img): ?><div class="gallery-item" data-existing-card="<?= (int)$img['id'] ?>" data-sort-key="existing:<?= (int)$img['id'] ?>" draggable="true"><div class="gallery-image-wrap"><img src="../<?= e($img['image_path']) ?>" alt="Gambar portofolio"><span class="thumbnail-badge" <?= $position === 0 ? '' : 'hidden' ?>>THUMBNAIL</span><span class="drag-handle" title="Seret untuk mengatur posisi" aria-hidden="true">⠿</span><div class="gallery-item-actions"><button type="button" class="icon-action" data-preview-src="../<?= e($img['image_path']) ?>" aria-label="Pratinjau gambar" title="Pratinjau gambar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button><button type="button" class="icon-action" data-crop-existing="<?= (int)$img['id'] ?>" data-src="../<?= e($img['image_path']) ?>" aria-label="Crop gambar" title="Crop gambar">✂</button><button type="button" class="icon-action" data-replace-existing="<?= (int)$img['id'] ?>" aria-label="Ganti gambar" title="Ganti gambar">↻</button><button type="button" class="icon-action danger" data-remove-existing="<?= (int)$img['id'] ?>" aria-label="Hapus gambar" title="Hapus gambar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2m4 0-1 14H6L5 6m4 4v6m6-6v6"/></svg></button></div></div><div class="gallery-item-name"><?= e(basename($img['image_path'])) ?></div><input type="checkbox" name="remove_images[]" value="<?= (int)$img['id'] ?>" class="remove-gallery-image"></div><?php endforeach; ?></div><?php if (!$galleryImages): ?><p class="gallery-help" id="emptyGalleryNote">Belum ada gambar. Gambar pertama akan menjadi sampul kartu portofolio.</p><?php endif; ?>
<label class="gallery-drop" for="projectImages"><strong>＋ Tambah gambar portofolio</strong><small>Seret gambar untuk mengatur urutan. Gambar pertama otomatis menjadi thumbnail/sampul portofolio.</small><input id="projectImages" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple></label><input type="hidden" name="image_order_json" id="imageOrderJson"><div class="gallery-manage" id="newImagePreview" hidden></div><input type="file" id="projectImagesPayload" name="project_images[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
</section>
<label>Akses demo (URL) *<input name="demo_url" type="text" inputmode="url" required value="<?= e($project['demo_url']) ?>" placeholder="demo-domain-anda.com"><small>URL untuk mencoba proyek. Jangan gunakan URL dashboard/admin jika tidak boleh diakses publik.</small></label>
<label>Fitur utama (satu fitur per baris atau pisahkan dengan koma)<textarea name="features" rows="4" placeholder="Login dan registrasi&#10;Dashboard admin&#10;Pencarian data&#10;Contoh: membantu staf mengelola data lebih rapi"><?= e($project['features']) ?></textarea></label>
<label>Keunggulan (satu poin per baris)<textarea name="advantages" rows="4" placeholder="Tampilan responsif&#10;Mudah digunakan&#10;Dapat dikembangkan lebih lanjut"><?= e($project['advantages'] ?? '') ?></textarea></label>
<label>Informasi akses demo<textarea name="demo_access" rows="3" placeholder="Cara masuk demo, akun demo jika memang aman dibagikan, atau petunjuk akses."><?= e($project['demo_access'] ?? '') ?></textarea><small>Jangan cantumkan kata sandi akun pribadi atau kredensial produksi.</small></label>
<label>Catatan<textarea name="notes" rows="3" placeholder="Batasan demo, teknologi yang digunakan, dukungan setelah rilis, garansi bug, dan informasi tambahan yang perlu diketahui pengunjung."><?= e($project['notes'] ?? '') ?></textarea></label>
<label>System Requirements (satu kebutuhan per baris)<textarea name="system_requirements" rows="4" placeholder="PHP 8.1 atau lebih baru&#10;MySQL 8&#10;CodeIgniter 4"><?= e($project['system_requirements'] ?? '') ?></textarea></label>
<div class="form-grid"><label>Urutan tampil<input name="sort_order" type="number" step="1" value="<?= (int)$project['sort_order'] ?>"><small>Angka lebih kecil tampil lebih awal.</small></label><label class="checkbox-label"><input type="checkbox" name="is_published" value="1" <?= $project['is_published']?'checked':'' ?>> Tampilkan di katalog publik</label></div>
<div class="form-actions"><button class="button primary" type="submit">Simpan proyek</button><a class="button" href="index.php">Batal</a></div></form></main>
<div class="crop-modal" id="cropModal" role="dialog" aria-modal="true" aria-label="Potong gambar"><div class="crop-dialog"><div class="crop-dialog-head"><h3>Potong gambar</h3><button type="button" id="cropCancelTop">Tutup</button></div><div class="crop-image-area"><img id="cropTarget" alt="Gambar yang akan dipotong"></div><div class="crop-controls"><button type="button" id="cropRotateLeft">↶ Putar kiri</button><button type="button" id="cropRotateRight">↷ Putar kanan</button><button type="button" id="cropReset">Reset</button><label>Rasio <select id="cropRatio"><option value="free">Bebas</option><option value="1.7777778">16:9</option><option value="1.3333333">4:3</option><option value="1">1:1</option></select></label></div><div class="crop-dialog-actions"><button type="button" id="cropCancel">Batal</button><button type="button" class="primary" id="cropSave">Gunakan hasil crop</button></div></div></div>
<div class="preview-modal" id="imagePreviewModal" role="dialog" aria-modal="true"><div class="preview-dialog"><button type="button" class="preview-close" id="previewClose">✕ Tutup</button><img id="imagePreviewLarge" alt="Pratinjau gambar"></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(()=>{
const pickerColor=document.getElementById('themeColorPicker'),hexColor=document.getElementById('themeColorHex'),colorPreview=document.getElementById('themeCardPreview');
if(pickerColor&&hexColor&&colorPreview){const valid=v=>/^#[0-9a-fA-F]{6}$/.test(v);const apply=v=>{if(valid(v)){const normalized=v.toUpperCase();colorPreview.style.setProperty('--preview-accent',normalized);document.querySelectorAll('.theme-swatch').forEach(b=>b.classList.toggle('is-selected',b.dataset.color===normalized));}};pickerColor.addEventListener('input',()=>{hexColor.value=pickerColor.value.toUpperCase();apply(hexColor.value);});hexColor.addEventListener('input',()=>{if(valid(hexColor.value)){pickerColor.value=hexColor.value;apply(hexColor.value);}});document.querySelectorAll('.theme-swatch').forEach(b=>b.addEventListener('click',()=>{hexColor.value=b.dataset.color;pickerColor.value=b.dataset.color;apply(b.dataset.color);}));apply(hexColor.value);}
})();
</script>
<script>
(()=>{
const picker=document.getElementById('projectImages'),payload=document.getElementById('projectImagesPayload'),preview=document.getElementById('existingGallery'),newPreview=document.getElementById('newImagePreview'),counter=document.getElementById('galleryCounter'),orderInput=document.getElementById('imageOrderJson');
const cropModal=document.getElementById('cropModal'),cropTarget=document.getElementById('cropTarget'),previewModal=document.getElementById('imagePreviewModal'),previewLarge=document.getElementById('imagePreviewLarge');
const oldCount=<?= count($galleryImages) ?>;let files=[],cropper=null,cropTargetInfo=null,pendingReplaceId=null;
const removedCount=()=>document.querySelectorAll('.remove-gallery-image:checked').length;
const remainingOld=()=>oldCount-removedCount();
const refreshPayload=()=>{const dt=new DataTransfer();files.forEach(f=>dt.items.add(f));payload.files=dt.files;};
const refreshCounter=()=>{const n=remainingOld()+files.length;counter.textContent=n+'/9 gambar';counter.style.color=n>9?'#b91c1c':'#4f46e5';};
const addFile=(file,replaceIndex=null)=>{if(!file)return;if(!/^image\/(jpeg|png|webp)$/.test(file.type)){alert('Gunakan gambar JPG, PNG, atau WEBP.');return;}if(file.size>5*1024*1024){alert('Setiap gambar maksimal 5 MB.');return;}if(replaceIndex!==null)files[replaceIndex]=file;else files.push(file);if(remainingOld()+files.length>9){if(replaceIndex!==null)files[replaceIndex]=cropTargetInfo?.originalFile;else files.pop();alert('Maksimal 9 gambar per proyek.');return;}refreshPayload();renderNew();};
const renderNew=()=>{preview.querySelectorAll('.new-image-card').forEach(el=>el.remove());files.forEach((file,index)=>{const card=document.createElement('div');card.className='gallery-item new-image-card';card.dataset.sortKey='new:'+index;card.draggable=true;const wrap=document.createElement('div');wrap.className='gallery-image-wrap';const img=document.createElement('img');img.src=URL.createObjectURL(file);img.alt=file.name;const badge=document.createElement('span');badge.className='thumbnail-badge';badge.textContent='THUMBNAIL';const handle=document.createElement('span');handle.className='drag-handle';handle.textContent='⠿';handle.title='Seret untuk mengatur posisi';const actions=document.createElement('div');actions.className='gallery-item-actions';[['Preview',()=>openPreview(img.src)],['Crop',()=>openCrop(img.src,{kind:'new',index,originalFile:file})],['Hapus',()=>{files.splice(index,1);refreshPayload();renderNew()}]].forEach(([label,fn])=>{const b=document.createElement('button');b.type='button';b.className='icon-action'+(label==='Hapus'?' danger':'');b.title=label;b.setAttribute('aria-label',label);b.innerHTML=label==='Preview'?'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>':label==='Crop'?'✂':'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2m4 0-1 14H6L5 6m4 4v6m6-6v6"/></svg>';b.addEventListener('click',fn);actions.append(b)});wrap.append(img,badge,handle,actions);const name=document.createElement('div');name.className='gallery-item-name';name.textContent=file.name;card.append(wrap,name);preview.append(card)});refreshCounter();refreshThumbnailBadge();};
const gallery=preview;let draggedCard=null;const refreshThumbnailBadge=()=>{gallery.querySelectorAll('.thumbnail-badge').forEach(b=>b.hidden=true);const first=Array.from(gallery.querySelectorAll('.gallery-item')).find(card=>!card.classList.contains('is-removed'));if(first){const badge=first.querySelector('.thumbnail-badge');if(badge)badge.hidden=false;}};const syncImageOrder=()=>{orderInput.value=JSON.stringify(Array.from(gallery.querySelectorAll('.gallery-item')).filter(card=>!card.classList.contains('is-removed')).map(card=>card.dataset.sortKey).filter(Boolean));refreshThumbnailBadge();};gallery.addEventListener('dragstart',e=>{const card=e.target.closest('.gallery-item');if(!card)return;draggedCard=card;card.classList.add('is-dragging');e.dataTransfer.effectAllowed='move';try{e.dataTransfer.setData('text/plain',card.dataset.sortKey||'')}catch(_){}});gallery.addEventListener('dragover',e=>{if(!draggedCard)return;e.preventDefault();const target=e.target.closest('.gallery-item');if(!target||target===draggedCard)return;const r=target.getBoundingClientRect();const after=(e.clientY-r.top)>r.height/2;gallery.insertBefore(draggedCard,after?target.nextSibling:target);gallery.querySelectorAll('.is-drop-target').forEach(c=>c.classList.remove('is-drop-target'));target.classList.add('is-drop-target')});gallery.addEventListener('drop',e=>{if(draggedCard)e.preventDefault();gallery.querySelectorAll('.is-drop-target').forEach(c=>c.classList.remove('is-drop-target'));syncImageOrder()});gallery.addEventListener('dragend',()=>{if(draggedCard)draggedCard.classList.remove('is-dragging');draggedCard=null;gallery.querySelectorAll('.is-drop-target').forEach(c=>c.classList.remove('is-drop-target'));syncImageOrder()});
const openPreview=src=>{previewLarge.src=src;previewModal.classList.add('open')};document.getElementById('previewClose').addEventListener('click',()=>{previewModal.classList.remove('open');previewLarge.src=''});previewModal.addEventListener('click',e=>{if(e.target===previewModal)document.getElementById('previewClose').click()});
const openCrop=(src,info)=>{if(typeof Cropper==='undefined'){alert('Fitur crop memerlukan koneksi internet untuk memuat editor gambar.');return;}cropTargetInfo=info;cropTarget.onload=()=>{cropModal.classList.add('open');if(cropper)cropper.destroy();cropper=new Cropper(cropTarget,{viewMode:1,dragMode:'move',autoCropArea:.9, responsive:true,background:false,zoomable:true, movable:true,rotatable:true, scalable:true});};cropTarget.src=src;};
const closeCrop=()=>{cropModal.classList.remove('open');if(cropper){cropper.destroy();cropper=null}cropTarget.src='';cropTargetInfo=null;pendingReplaceId=null};document.getElementById('cropCancel').addEventListener('click',closeCrop);document.getElementById('cropCancelTop').addEventListener('click',closeCrop);document.getElementById('cropRotateLeft').addEventListener('click',()=>cropper?.rotate(-90));document.getElementById('cropRotateRight').addEventListener('click',()=>cropper?.rotate(90));document.getElementById('cropReset').addEventListener('click',()=>cropper?.reset());document.getElementById('cropRatio').addEventListener('change',e=>cropper?.setAspectRatio(e.target.value==='free'?NaN:Number(e.target.value)));
document.getElementById('cropSave').addEventListener('click',()=>{if(!cropper||!cropTargetInfo)return;cropper.getCroppedCanvas({maxWidth:2200,maxHeight:1600,imageSmoothingQuality:'high'}).toBlob(blob=>{if(!blob){alert('Gagal memproses crop gambar.');return;}const name=(cropTargetInfo.originalFile?.name||'portfolio-image.jpg').replace(/\.(png|jpe?g|webp)$/i,'')+'-crop.jpg';const cropped=new File([blob],name,{type:'image/jpeg',lastModified:Date.now()});if(cropTargetInfo.kind==='new'){files[cropTargetInfo.index]=cropped;refreshPayload();renderNew()}else{const id=cropTargetInfo.id;const old=document.querySelector('[data-existing-card="'+id+'"] .remove-gallery-image');if(old)old.checked=true;addFile(cropped);refreshExisting();}closeCrop()},'image/jpeg',.9)});
const refreshExisting=()=>{document.querySelectorAll('[data-existing-card]').forEach(card=>{const cb=card.querySelector('.remove-gallery-image');card.classList.toggle('is-removed',cb.checked);const del=card.querySelector('[data-remove-existing]');if(del){del.innerHTML=cb.checked?'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 2.64-6.36L3 8m0-5v5h5m4-1v5l3 2"/></svg>':'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2m4 0-1 14H6L5 6m4 4v6m6-6v6"/></svg>';del.title=cb.checked?'Pulihkan gambar':'Hapus gambar';del.setAttribute('aria-label',del.title)}});refreshCounter();refreshThumbnailBadge();syncImageOrder()};
document.querySelectorAll('[data-preview-src]').forEach(b=>b.addEventListener('click',()=>openPreview(b.dataset.previewSrc)));
document.querySelectorAll('[data-remove-existing]').forEach(b=>b.addEventListener('click',()=>{const cb=document.querySelector('[data-existing-card="'+b.dataset.removeExisting+'"] .remove-gallery-image');cb.checked=!cb.checked;refreshExisting()}));
const replacementPicker=document.createElement('input');replacementPicker.type='file';replacementPicker.accept='image/jpeg,image/png,image/webp';replacementPicker.hidden=true;document.body.append(replacementPicker);let replacingId=null;replacementPicker.addEventListener('change',()=>{const f=replacementPicker.files?.[0];if(f&&replacingId){openCrop(URL.createObjectURL(f),{kind:'existing',id:replacingId,originalFile:f});}replacementPicker.value=''});
document.querySelectorAll('[data-replace-existing]').forEach(b=>b.addEventListener('click',()=>{replacingId=b.dataset.replaceExisting;replacementPicker.click()}));
document.querySelectorAll('[data-crop-existing]').forEach(b=>b.addEventListener('click',()=>{openCrop(b.dataset.src,{kind:'existing',id:b.dataset.cropExisting})}));
picker.addEventListener('change',()=>{const incoming=Array.from(picker.files||[]);picker.value='';if(remainingOld()+files.length+incoming.length>9){alert('Maksimal 9 gambar. Hapus gambar lama atau kurangi jumlah gambar baru.');return;}for(const file of incoming){if(file.size>5*1024*1024){alert('Setiap gambar maksimal 5 MB. File '+file.name+' dilewati.');continue;}addFile(file)} });
document.querySelectorAll('.remove-gallery-image').forEach(cb=>cb.addEventListener('change',refreshExisting));document.querySelector('form.editor').addEventListener('submit',e=>{refreshPayload();syncImageOrder();if(remainingOld()+files.length>9){e.preventDefault();alert('Maksimal 9 gambar per proyek.');}});refreshExisting();renderNew();
})();
</script><script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('salesMethodSelect'),h=document.getElementById('salesMethodHelp');if(!s||!h)return;var t={'Project-Based':'Bayar sekali atau DP; website menjadi milik klien setelah lunas.','Website Retainer':'Bayar biaya perawatan bulanan/tahunan; website tetap milik klien.','Website as a Service (WaaS)':'Bayar biaya sewa bulanan; website dan sistem tetap milik agensi.'};function u(){h.textContent=t[s.value]||t['Project-Based'];}s.addEventListener('change',u);u();});</script></body></html>
