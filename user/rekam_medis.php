<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('user');

$db     = getDB();
$userId = $_SESSION['user_id'];

$pasienStmt = $db->prepare("SELECT * FROM pasien WHERE user_id=? LIMIT 1");
$pasienStmt->execute([$userId]);
$pasien = $pasienStmt->fetch();

if (!$pasien) {
    header('Location: ' . BASE_URL . '/user/profil.php?tab=data_diri');
    exit;
}
$pasienId = $pasien['id'];

$search  = sanitize($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = ['r.pasien_id = ?'];
$params = [$pasienId];
if ($search !== '') {
    $where[] = "(d.nama LIKE ? OR r.diagnosa LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare(
    "SELECT COUNT(*) FROM rekam_medis r
     JOIN dokter d ON d.id = r.dokter_id
     WHERE $whereStr"
);
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT r.*, d.nama AS nama_dokter
     FROM rekam_medis r
     JOIN dokter d ON d.id = r.dokter_id
     WHERE $whereStr
     ORDER BY r.tanggal DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$rekamList = $stmt->fetchAll();

$pageTitle = 'Rekam Medis Saya';
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

<div class="section-header">
  <h1>Rekam Medis Saya</h1>
  <p>Lihat riwayat pemeriksaan Anda di klinik.</p>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari dokter atau diagnosa…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="rekam_medis.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> catatan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Dokter</th><th>Diagnosa</th><th>Tensi</th><th>Suhu</th></tr></thead>
      <tbody>
        <?php foreach ($rekamList as $i => $r): ?>
        <tr>
          <td class="text-muted"><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
          <td style="font-weight:600"><?= e($r['nama_dokter']) ?></td>
          <td><?= nl2br(e($r['diagnosa'] ?? '–')) ?></td>
          <td class="text-muted"><?= e($r['tekanan_darah'] ?? '–') ?></td>
          <td class="text-muted"><?= $r['suhu'] ? $r['suhu'].' °C' : '–' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rekamList): ?>
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--gray-500)">Belum ada rekam medis.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>