<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db   = getDB();
$ok   = '';
$err  = '';
$csrf = generateCsrf();

$backupDir = __DIR__ . '/../backups/';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'buat_backup') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $err = 'Token keamanan tidak valid.';
    } else {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;
        try {
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $sql = "-- Backup Klinik Medika Utama\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            foreach ($tables as $table) {
                $sql .= "-- Table: $table\n";
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $sql .= $create['Create Table'] . ";\n\n";
                $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : $db->quote($v), array_values($row));
                    $cols = '`' . implode('`,`', array_keys($row)) . '`';
                    $sql .= "INSERT INTO `$table` ($cols) VALUES (" . implode(',', $vals) . ");\n";
                }
                $sql .= "\n";
            }
            file_put_contents($filepath, $sql);
            $ok = "Backup berhasil: $filename";
            auditLog('backup_database', "Backup created: $filename");
        } catch (Exception $e) {
            $err = 'Gagal membuat backup: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['hapus'])) {
    $hapusFile = basename($_GET['hapus']);
    $hapusPath = $backupDir . $hapusFile;
    if (file_exists($hapusPath) && is_file($hapusPath)) {
        unlink($hapusPath);
        auditLog('hapus_backup', "Deleted backup: $hapusFile");
        $ok = "Backup dihapus.";
    }
}

$backupFiles = array_filter(scandir($backupDir), function($f) use ($backupDir) {
    return is_file($backupDir . $f) && pathinfo($f, PATHINFO_EXTENSION) === 'sql';
});
rsort($backupFiles);

$pageTitle = 'Backup Database';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="page-body">

<?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<div class="card" style="margin-bottom:1.25rem">
  <h3 style="margin-bottom:1rem">💾 Buat Backup Database</h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="buat_backup">
    <button type="submit" class="btn btn-primary">📦 Backup Sekarang</button>
    <span class="text-muted" style="margin-left:1rem;font-size:.85rem">File .sql akan disimpan di folder backups/</span>
  </form>
</div>

<div class="card">
  <h3 style="margin-bottom:1rem">📁 Daftar Backup</h3>
  <?php if (empty($backupFiles)): ?>
    <p class="text-muted">Belum ada backup.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table>
      <thead><tr><th>File</th><th>Ukuran</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($backupFiles as $f): ?>
        <tr>
          <td style="font-weight:600"><?= e($f) ?></td>
          <td class="text-muted"><?= round(filesize($backupDir . $f) / 1024, 1) ?> KB</td>
          <td>
            <a href="<?= BASE_URL ?>/backups/<?= urlencode($f) ?>" class="btn btn-sm btn-secondary" download>⬇ Unduh</a>
            <a href="?hapus=<?= urlencode($f) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus backup ini?')">🗑 Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>