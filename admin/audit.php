<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$search = sanitize($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = "(a.aksi LIKE ? OR a.detail LIKE ? OR u.username LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $params ? $where : $where);

$totalStmt = $db->prepare(
    "SELECT COUNT(*) FROM audit_log a
     LEFT JOIN users u ON u.id = a.user_id
     WHERE $whereStr"
);
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT a.*, u.username, u.nama_lengkap
     FROM audit_log a
     LEFT JOIN users u ON u.id = a.user_id
     WHERE $whereStr
     ORDER BY a.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Log Audit';
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

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari aksi, detail, username…" style="width:280px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="audit.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> entri log</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Detail</th><th>IP</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $i => $l): ?>
        <tr>
          <td class="text-muted" style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
          <td>
            <?php if (!empty($l['username'])): ?>
              <strong><?= e($l['username']) ?></strong><br>
              <span class="text-muted" style="font-size:.75rem"><?= e($l['nama_lengkap'] ?? '') ?></span>
            <?php else: ?>
              <span class="badge badge-gray">System</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-info"><?= e($l['aksi']) ?></span></td>
          <td style="max-width:260px;word-break:break-word"><?= e($l['detail'] ?? '–') ?></td>
          <td class="text-muted" style="font-size:.8rem"><?= e($l['ip_address'] ?? '–') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?>
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada log.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>