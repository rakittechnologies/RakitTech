<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Metode tidak diizinkan.'); }
verify_csrf();
$rawId = $_POST['id'] ?? null;
$id = (is_scalar($rawId) && filter_var((string)$rawId, FILTER_VALIDATE_INT) !== false) ? (int)$rawId : 0;
if ($id < 1) { flash('ID proyek tidak valid. Muat ulang daftar proyek; jika ID tetap 0, periksa migrasi kolom projects.id di database.', 'error'); header('Location: index.php'); exit; }
$stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
$stmt->execute([$id]);
flash($stmt->rowCount() ? 'Proyek berhasil dihapus.' : 'Proyek tidak ditemukan.', $stmt->rowCount() ? 'success' : 'error');
header('Location: index.php'); exit;
