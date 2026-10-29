<?php
// admin/backup_download.php – Download file backup secara aman (Admin only)
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

define('BACKUP_DIR', __DIR__ . '/../backups');

$file = basename($_GET['file'] ?? '');

// Hanya boleh nama file sesuai pola backup resmi — mencegah path traversal
if (!preg_match('/^backup_sistem_\d{8}_\d{6}\.sql$/', $file)) {
    auditLog('akses_ditolak', "Percobaan akses file backup tidak valid: {$file}");
    http_response_code(400);
    exit('Nama file tidak valid.');
}

$path = BACKUP_DIR . '/' . $file;
if (!is_file($path)) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

auditLog('backup_unduh', "Unduh file backup: {$file}");

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
