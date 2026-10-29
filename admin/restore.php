<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db   = getDB();
$ok   = '';
$err  = '';
$csrf = generateCsrf();

$backupDir = __DIR__ . '/../backups/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $err = 'Token keamanan tidak valid.';
    } elseif (($_POST['aksi'] ?? '') === 'restore') {
        $file = basename($_POST['file'] ?? '');
        $path = $backupDir . $file;
        if (!file_exists($path) || !is_file($path)) {
            $err = 'File backup tidak ditemukan.';
        } else {
            try {
                $sql = file_get_contents($path);
                $db->exec("SET FOREIGN_KEY_CHECKS=0");
                $statements = array_filter(array_map('trim', explode(";\n", $sql)), fn($s) => $s !== '');
                foreach ($statements as $st) {
                    if (stripos($st, 'DROP TABLE IF EXISTS') === 0) continue;
                    $db->exec($st);
                }
                $db->exec("SET FOREIGN_KEY_CHECKS=1");
                $ok = "Database berhasil di-restore dari: $file";
                auditLog('restore_database', "Restore from $file");
            } catch (Exception $e) {
                $err = 'Gagal restore: ' . $e->getMessage();
            }
        }
    } elseif (($_POST['aksi'] ?? '') === 'upload' && isset($_FILES['sql_file'])) {
        $uploaded = $_FILES['sql_file'];
        if ($uploaded['error'] !== UPLOAD_ERR_OK) {
            $err = 'Gagal mengupload file. Error code: ' . $uploaded['error'];
        } elseif (pathinfo($uploaded['name'], PATHINFO_EXTENSION) !== 'sql') {
            $err = 'Format file harus .sql';
        } elseif ($uploaded['size'] > 50 * 1024 * 1024) {
            $err = 'Ukuran file maksimal 50MB';
        } else {
            $tmpPath = $uploaded['tmp_name'];
            if (!is_uploaded_file($tmpPath)) {
                $err = 'File yang diupload tidak valid.';
            } else {
                try {
                    $sql = file_get_contents($tmpPath);
                    $db->exec("SET FOREIGN_KEY_CHECKS=0");
                    $statements = array_filter(array_map('trim', explode(";\n", $sql)), fn($s) => $s !== '');
                    foreach ($statements as $st) {
                        if (stripos($st, 'DROP TABLE IF EXISTS') === 0) continue;
                        $db->exec($st);
                    }
                    $db->exec("SET FOREIGN_KEY_CHECKS=1");
                    $ok = "Database berhasil di-restore dari file: " . e($uploaded['name']);
                    auditLog('restore_database', "Restore from uploaded file: " . $uploaded['name']);
                } catch (Exception $e) {
                    $err = 'Gagal restore: ' . $e->getMessage();
                }
            }
        }
    }
}

$backupFiles = array_filter(scandir($backupDir), function($f) use ($backupDir) {
    return is_file($backupDir . $f) && pathinfo($f, PATHINFO_EXTENSION) === 'sql';
});
rsort($backupFiles);

$pageTitle = 'Restore Database';
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
  <h3 style="margin-bottom:1rem">⚠️ Restore Database dari Backup</h3>

  <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN: Semua data saat ini akan diganti dengan data backup. Lanjutkan?')" style="margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid #e5e7eb">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="upload">
    <div class="form-group">
      <label>Upload File SQL dari Komputer</label>
      <input type="file" name="sql_file" accept=".sql" required style="padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:8px;width:100%;max-width:400px">
      <small class="text-muted">Format: .sql | Maksimal: 50MB</small>
    </div>
    <button type="submit" class="btn btn-primary">📤 Upload & Restore</button>
  </form>

  <form method="POST" onsubmit="return confirm('PERINGATAN: Semua data saat ini akan diganti dengan data backup. Lanjutkan?')">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="restore">
    <div class="form-group">
      <label>Pilih File Backup yang Sudah Ada di Server</label>
      <select name="file" required>
        <option value="">-- Pilih Backup --</option>
        <?php foreach ($backupFiles as $f): ?>
        <option value="<?= e($f) ?>"><?= e($f) ?> (<?= round(filesize($backupDir . $f) / 1024, 1) ?> KB)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-danger">🔄 Restore dari Server</button>
  </form>
  <span class="text-muted" style="display:block;margin-top:.75rem;font-size:.85rem">Data saat ini akan diganti total.</span>
</div>

<div class="card">
  <h3 style="margin-bottom:1rem">📋 Daftar Backup Tersedia</h3>
  <?php if (empty($backupFiles)): ?>
    <p class="text-muted">Belum ada backup untuk direstore.</p>
  <?php else: ?>
    <div class="table-wrap">
    <table>
      <thead><tr><th>File</th><th>Ukuran</th></tr></thead>
      <tbody>
        <?php foreach ($backupFiles as $f): ?>
        <tr>
          <td style="font-weight:600"><?= e($f) ?></td>
          <td class="text-muted"><?= round(filesize($backupDir . $f) / 1024, 1) ?> KB</td>
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