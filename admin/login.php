<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php'); exit;
    }
    $error = 'Username atau kata sandi tidak sesuai.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login Admin — <?= e(setting('site_name', 'RuangWeb')) ?></title><link rel="stylesheet" href="../assets/admin.css?v=2"></head><body>
<main class="auth-card"><a href="../index.php" class="back-link">← Kembali ke website</a><h1>Login Admin</h1><p>Masuk untuk mengelola katalog portofolio <?= e(setting('site_name', 'RuangWeb')) ?>.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" autocomplete="on"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Username<input name="username" required maxlength="80" autocomplete="username"></label><label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label><button class="button primary" type="submit">Masuk</button></form>
<div class="auth-extra"><span>Belum punya akun admin?</span><a class="button secondary" href="create-admin.php">Buat Admin</a><small>Tombol ini hanya dapat digunakan saat akun admin belum pernah dibuat.</small></div>
</main></body></html>
