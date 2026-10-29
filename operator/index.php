<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('operator');

$db = getDB();
$today = date('Y-m-d');

$pendaftarHariIni = $db->prepare("SELECT COUNT(*) FROM antrian WHERE operator_id=? AND tanggal=?");
$pendaftarHariIni->execute([$_SESSION['user_id'], $today]);
$totalDaftar = (int)$pendaftarHariIni->fetchColumn();

$stat = $db->prepare("SELECT status, COUNT(*) AS jml FROM antrian WHERE tanggal=? GROUP BY status");
$stat->execute([$today]);
$statMap = ['menunggu'=>0,'dipanggil'=>0,'selesai'=>0,'batal'=>0];
foreach ($stat->fetchAll() as $s) $statMap[$s['status']] = (int)$s['jml'];

$pageTitle = 'Dasbor Operator';
$quickLinks = [
  ['href' => BASE_URL.'/operator/antrian.php', 'icon' => '📋', 'label' => 'Daftar Antrian'],
  ['href' => BASE_URL.'/operator/pasien.php', 'icon' => '👥', 'label' => 'Pasien'],
  ['href' => BASE_URL.'/operator/dokter.php', 'icon' => '🩺', 'label' => 'Dokter'],
];
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
  <h1>Halo, <?= e($_SESSION['nama'] ?? 'Operator') ?></h1>
  <p>Ringkasan aktivitas pendaftaran hari ini.</p>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon-circle blue">📋</div>
    <div>
      <div class="stat-value"><?= $totalDaftar ?></div>
      <div class="stat-label">Pendaftar Hari Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle orange">⏳</div>
    <div>
      <div class="stat-value"><?= $statMap['menunggu'] ?></div>
      <div class="stat-label">Menunggu</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle purple">🔔</div>
    <div>
      <div class="stat-value"><?= $statMap['dipanggil'] ?></div>
      <div class="stat-label">Dipanggil</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle green">✅</div>
    <div>
      <div class="stat-value"><?= $statMap['selesai'] ?></div>
      <div class="stat-label">Selesai</div>
    </div>
  </div>
</div>

<div class="quick-actions">
  <?php foreach ($quickLinks as $ql): ?>
    <a href="<?= $ql['href'] ?>" class="quick-action-btn">
      <span class="quick-action-btn-icon"><?= $ql['icon'] ?></span>
      <?= e($ql['label']) ?>
    </a>
  <?php endforeach; ?>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>