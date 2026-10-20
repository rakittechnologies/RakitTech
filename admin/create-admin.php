<?php
// Gunakan sekali saja setelah mengunggah proyek. Hapus file ini segera setelah admin dibuat.
require dirname(__DIR__) . '/includes/bootstrap.php';
$count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($count > 0) { http_response_code(403); exit('Admin sudah dibuat. Hapus file admin/create-admin.php dari hosting.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_.-]{4,80}$/', $username)) $error = 'Username harus 4–80 karakter dan hanya boleh huruf, angka, titik, garis bawah, atau tanda hubung.';
    elseif (strlen($password) < 12) $error = 'Kata sandi minimal 12 karakter.';
    else {
        $stmt = $pdo->prepare('INSERT INTO admins (username,password_hash) VALUES (?,?)');
        try { $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]); header('Location: login.php'); exit; }
        catch (PDOException $e) { $error = 'Gagal membuat admin. Periksa username atau database.'; }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Buat Admin Awal — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body><main class="auth-card"><h1>Buat akun admin</h1><p>Buat akun pemilik untuk mengelola katalog. Halaman ini hanya bekerja jika belum ada admin.</p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Username<input name="username" required minlength="4" maxlength="80"></label><label>Kata sandi (minimal 12 karakter)<input type="password" name="password" required minlength="12"></label><button class="button primary" type="submit">Buat admin</button></form><div class="alert warning">Penting: setelah akun berhasil dibuat, segera hapus file <code>admin/create-admin.php</code> dari hosting.</div></main></body></html>
