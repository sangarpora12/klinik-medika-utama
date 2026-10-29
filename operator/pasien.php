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
    $where[] = "(nama_lengkap LIKE ? OR no_rm LIKE ? OR telepon LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM pasien WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare("SELECT * FROM pasien WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$pasienList = $stmt->fetchAll();

$pageTitle = 'Data Pasien';
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
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, No. RM, telepon…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="pasien.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> pasien ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>No. RM</th><th>Nama</th><th>Jenis Kelamin</th><th>Telepon</th><th>Alamat</th></tr></thead>
      <tbody>
        <?php foreach ($pasienList as $i => $p): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td><span class="badge badge-info"><?= e($p['no_rm']) ?></span></td>
          <td style="font-weight:600"><?= e($p['nama_lengkap']) ?></td>
          <td><?= $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
          <td><?= e($p['telepon'] ?? '–') ?></td>
          <td><?= e($p['alamat'] ?? '–') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$pasienList): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data pasien.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>