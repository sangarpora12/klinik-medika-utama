<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('operator');

$db = getDB();
$search = sanitize($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = "(nama LIKE ? OR spesialisasi LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM dokter WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare("SELECT * FROM dokter WHERE $whereStr ORDER BY nama ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$dokterList = $stmt->fetchAll();

$pageTitle = 'Jadwal Dokter';
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
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama atau spesialisasi…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="dokter.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> dokter</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Dokter</th><th>Spesialisasi</th><th>No. SIP</th><th>Telepon</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($dokterList as $i => $d): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td style="font-weight:600"><?= e($d['nama']) ?></td>
          <td><?= e($d['spesialisasi']) ?></td>
          <td><?= e($d['no_sip'] ?? '–') ?></td>
          <td><?= e($d['telepon'] ?? '–') ?></td>
          <td><span class="badge <?= $d['aktif'] ? 'badge-success' : 'badge-danger' ?>"><?= $d['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$dokterList): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data dokter.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>