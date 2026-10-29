<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db = getDB();

$stats = [
    'total_user'   => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_pasien' => $db->query("SELECT COUNT(*) FROM pasien")->fetchColumn(),
    'total_dokter' => $db->query("SELECT COUNT(*) FROM dokter WHERE aktif=1")->fetchColumn(),
    'antrian_hari' => $db->query("SELECT COUNT(*) FROM antrian WHERE tanggal=CURDATE()")->fetchColumn(),
];

$recentUsers = $db->query(
    "SELECT u.nama_lengkap, u.username, u.status, u.created_at, r.nama_role
     FROM users u JOIN roles r ON r.id=u.role_id
     ORDER BY u.created_at DESC LIMIT 8"
)->fetchAll();

$recentLogs = $db->query(
    "SELECT a.aksi, a.detail, a.created_at, a.ip_address, u.username
     FROM audit_log a LEFT JOIN users u ON u.id=a.user_id
     ORDER BY a.created_at DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Dashboard Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon-circle purple">👥</div>
    <div><div class="stat-value"><?= $stats['total_user'] ?></div><div class="stat-label">Total Pengguna</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle blue">🏥</div>
    <div><div class="stat-value"><?= $stats['total_pasien'] ?></div><div class="stat-label">Total Pasien</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle orange">🩺</div>
    <div><div class="stat-value"><?= $stats['total_dokter'] ?></div><div class="stat-label">Dokter Aktif</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-circle red">📋</div>
    <div><div class="stat-value"><?= $stats['antrian_hari'] ?></div><div class="stat-label">Antrian Hari Ini</div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

<div class="card">
  <div class="d-flex justify-between align-center mb-0" style="margin-bottom:1rem">
    <div class="section-title" style="margin:0">Pengguna Terbaru</div>
    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nama</th><th>Role</th><th>Status</th><th>Tanggal</th></tr></thead>
      <tbody>
        <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td>
            <div style="font-weight:600"><?= e($u['nama_lengkap']) ?></div>
            <div class="text-muted">@<?= e($u['username']) ?></div>
          </td>
          <td><span class="badge badge-info"><?= e($u['nama_role']) ?></span></td>
          <td>
            <?php
              $bc = ['aktif'=>'badge-success','belum_verifikasi'=>'badge-warning','nonaktif'=>'badge-danger'];
              $bl = ['aktif'=>'Aktif','belum_verifikasi'=>'Belum Verif','nonaktif'=>'Nonaktif'];
            ?>
            <span class="badge <?= $bc[$u['status']] ?? 'badge-gray' ?>">
              <?= $bl[$u['status']] ?? $u['status'] ?>
            </span>
          </td>
          <td class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recentUsers): ?>
        <tr><td colspan="4" class="empty-state"><div class="empty-state-icon">📭</div><div class="empty-state-text">Belum ada data</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="d-flex justify-between align-center" style="margin-bottom:1rem">
    <div class="section-title" style="margin:0">Aktivitas Terkini</div>
    <a href="<?= BASE_URL ?>/admin/audit.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Aksi</th><th>User</th><th>IP</th><th>Waktu</th></tr></thead>
      <tbody>
        <?php foreach ($recentLogs as $log): ?>
        <tr>
          <td>
            <div style="font-weight:600"><?= e($log['aksi']) ?></div>
            <?php if ($log['detail']): ?>
            <div class="text-muted" style="font-size:.78rem"><?= e(mb_substr($log['detail'],0,50)) ?>…</div>
            <?php endif; ?>
          </td>
          <td><?= e($log['username'] ?? '-') ?></td>
          <td class="text-muted"><?= e($log['ip_address']) ?></td>
          <td class="text-muted"><?= date('H:i', strtotime($log['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recentLogs): ?>
        <tr><td colspan="4" class="empty-state"><div class="empty-state-icon">🔇</div><div class="empty-state-text">Belum ada log</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>