<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$total = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$stmt = $pdo->prepare('SELECT * FROM projects ORDER BY created_at DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT); $stmt->bindValue(':off', ($page-1)*$perPage, PDO::PARAM_INT); $stmt->execute();
$projects = $stmt->fetchAll();
$notice = take_flash();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kelola Portofolio — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body>
<header class="admin-top"><div><strong><?= e(setting('site_name', 'RuangWeb')) ?> / Admin</strong><span>Masuk sebagai <?= e($_SESSION['admin_username'] ?? 'admin') ?></span></div><nav><a href="index.php">Portofolio</a><a href="packages.php">Layanan & Harga</a><a href="faq.php">FAQ</a><a href="settings.php">Pengaturan Website</a><a href="../index.php" target="_blank" rel="noopener">Lihat website ↗</a><a href="logout.php">Keluar</a></nav></header>
<main class="admin-main"><div class="page-heading"><div><h1>Portofolio</h1><p>Kelola proyek yang tampil di katalog publik.</p></div><div style="display:flex;gap:10px;flex-wrap:wrap"><a class="button" href="packages.php">Kelola Layanan & Harga</a><a class="button primary" href="project-form.php">+ Tambah proyek</a></div></div>
<?php if ($notice): ?><div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>
<div class="info">Proyek berstatus <b>Publik</b> terlihat oleh pengunjung. Proyek <b>Draft</b> hanya tampil di panel admin. Tautan demo dibuka di tab baru; jangan berikan kredensial admin proyek kepada calon klien.</div>
<div class="table-wrap"><table><thead><tr><th>Proyek</th><th>Gambar</th><th>Jenis</th><th>Status</th><th>Demo</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$projects): ?><tr><td colspan="6">Belum ada proyek. Tambahkan portofolio pertama kamu.</td></tr><?php else: foreach ($projects as $p): ?><tr><td><strong><?= e($p['title']) ?></strong><small><?= e($p['category'] ?? '') ?></small></td><td><?php if (!empty($p['image_path'])): ?><img src="../<?= e($p['image_path']) ?>" alt="" style="width:76px;height:48px;object-fit:cover;border-radius:6px"><?php else: ?>—<?php endif; ?></td><td><?= e($p['website_type']) ?></td><td><span class="status <?= $p['is_published'] ? 'published' : 'draft' ?>"><?= $p['is_published'] ? 'Publik' : 'Draft' ?></span></td><td><a href="<?= e($p['demo_url']) ?>" target="_blank" rel="noopener noreferrer">Buka ↗</a></td><td class="actions-cell"><a class="button small" href="project-form.php?id=<?= (int)$p['id'] ?>">Edit</a><form method="post" action="delete-project.php" onsubmit="return confirm('Yakin ingin menghapus proyek ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="button small danger" type="submit">Hapus</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div>
<?php if ($total > $perPage): ?><div class="pagination"><?php if ($page>1): ?><a href="?page=<?= $page-1 ?>">← Sebelumnya</a><?php endif; ?><span>Halaman <?= $page ?> dari <?= (int)ceil($total/$perPage) ?></span><?php if ($page < ceil($total/$perPage)): ?><a href="?page=<?= $page+1 ?>">Berikutnya →</a><?php endif; ?></div><?php endif; ?>
</main></body></html>
