<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: packages.php'); exit; }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) { $stmt=$pdo->prepare('DELETE FROM service_packages WHERE id=?'); $stmt->execute([$id]); flash('Paket berhasil dihapus.'); }
header('Location: packages.php'); exit;
