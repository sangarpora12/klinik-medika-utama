<?php
// includes/header.php – Sidebar & topbar layout
// $pageTitle must be set before including this file
if (!isset($pageTitle)) $pageTitle = 'Dashboard';
$user = currentUser();
$role = $user['nama_role'] ?? 'user';

$navItems = [];
if ($role === 'admin') {
    $navItems = [
        ['label' => 'Menu Utama', 'type' => 'group'],
        ['href' => BASE_URL.'/admin/',                'icon' => '🏠', 'label' => 'Dashboard'],
        ['href' => BASE_URL.'/admin/users.php',       'icon' => '👥', 'label' => 'Manajemen User'],
        ['href' => BASE_URL.'/admin/dokter.php',      'icon' => '🩺', 'label' => 'Dokter'],
        ['href' => BASE_URL.'/admin/pasien.php',      'icon' => '🏥', 'label' => 'Pasien'],
        ['label' => 'Operasional', 'type' => 'group'],
        ['href' => BASE_URL.'/admin/antrian.php',     'icon' => '📋', 'label' => 'Antrian'],
        ['href' => BASE_URL.'/admin/rekam_medis.php', 'icon' => '📄', 'label' => 'Rekam Medis'],
        ['href' => BASE_URL.'/admin/obat.php',        'icon' => '💊', 'label' => 'Obat'],
        ['label' => 'Sistem', 'type' => 'group'],
        ['href' => BASE_URL.'/admin/audit.php',       'icon' => '🔒', 'label' => 'Audit Log'],
        ['href' => BASE_URL.'/admin/cetak_laporan.php','icon' => '🖨️', 'label' => 'Cetak Laporan'],
        ['label' => 'Database', 'type' => 'group'],
        ['href' => BASE_URL.'/admin/backup.php',       'icon' => '💾', 'label' => 'Backup'],
        ['href' => BASE_URL.'/admin/restore.php',      'icon' => '🔄', 'label' => 'Restore'],
    ];
} elseif ($role === 'operator') {
    $navItems = [
        ['label' => 'Menu Utama', 'type' => 'group'],
        ['href' => BASE_URL.'/operator/',             'icon' => '🏠', 'label' => 'Dashboard'],
        ['href' => BASE_URL.'/operator/pasien.php',   'icon' => '🏥', 'label' => 'Data Pasien'],
        ['href' => BASE_URL.'/operator/antrian.php',  'icon' => '📋', 'label' => 'Kelola Antrian'],
        ['href' => BASE_URL.'/operator/dokter.php',   'icon' => '🩺', 'label' => 'Jadwal Dokter'],
    ];
} else {
    $navItems = [
        ['label' => 'Menu Utama', 'type' => 'group'],
        ['href' => BASE_URL.'/user/',                 'icon' => '🏠', 'label' => 'Beranda'],
        ['href' => BASE_URL.'/user/antrian.php',      'icon' => '📋', 'label' => 'Daftar Antrian'],
        ['href' => BASE_URL.'/user/rekam_medis.php',  'icon' => '📄', 'label' => 'Rekam Medis'],
        ['href' => BASE_URL.'/user/profil.php',       'icon' => '👤', 'label' => 'Profil Saya'],
    ];
}

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
?>
<?php if ($role === 'user'): ?>
<header class="topbar" style="position:sticky;top:0;z-index:50;background:linear-gradient(135deg,#2563eb 0%,#4f46e5 100%);border-bottom:1px solid rgba(255,255,255,.1);padding:.5rem 1.5rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(37,99,235,.25)">
  <div style="display:flex;align-items:center;gap:.75rem">
    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.2rem;backdrop-filter:blur(4px)">🏥</div>
    <div>
      <div style="font-family:var(--font-display);font-weight:700;font-size:.95rem;color:#fff;line-height:1.2"><?= e(APP_NAME) ?></div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.7)"><?= e($user['nama_lengkap'] ?? 'Pasien') ?></div>
    </div>
  </div>
  <nav style="display:flex;align-items:center;gap:.25rem">
    <?php foreach ($navItems as $item): ?>
      <?php if (!isset($item['type']) || $item['type'] !== 'group'): ?>
        <?php $active = strpos($currentUri, parse_url($item['href'], PHP_URL_PATH)) !== false; ?>
        <a href="<?= e($item['href']) ?>" style="padding:.5rem .85rem;border-radius:8px;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.85);text-decoration:none;transition:all .2s;background:<?= $active ? 'rgba(255,255,255,.18)' : 'transparent' ?>;backdrop-filter:blur(4px)">
          <span style="margin-right:.35rem"><?= $item['icon'] ?></span><?= e($item['label']) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/auth/logout.php" style="margin-left:.5rem;padding:.45rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;color:#fff;background:rgba(239,68,68,.25);text-decoration:none;backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.15)">Keluar</a>
  </nav>
</header>
<main class="page-body" style="padding-top:1.25rem;background:linear-gradient(180deg,#f0f4ff 0%,#f8fafc 100%);min-height:calc(100vh - 60px)">
<?php else: ?>
<div class="app-layout">
<!-- Mobile overlay -->
<div class="mobile-overlay" id="mobile-overlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">🏥</div>
    <div>
      <span class="sidebar-brand-title"><?= e(APP_NAME) ?></span>
      <span class="sidebar-brand-version">v<?= e(APP_VERSION) ?></span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
      <?php if ($item['type'] ?? '' === 'group'): ?>
        <div class="nav-label"><?= e($item['label']) ?></div>
      <?php else: ?>
        <?php $active = strpos($currentUri, parse_url($item['href'], PHP_URL_PATH)) !== false; ?>
        <a href="<?= e($item['href']) ?>" class="<?= $active ? 'active' : '' ?>">
          <span class="nav-icon"><?= $item['icon'] ?></span>
          <?= e($item['label']) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user-name"><?= e($user['nama_lengkap'] ?? '') ?></div>
    <div class="sidebar-user-role"><?= e($role === 'user' ? 'Pasien' : ucfirst($role)) ?></div>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm">
      🚪 Keluar
    </a>
  </div>
</aside>

<!-- MAIN -->
<div class="main-content">
  <header class="topbar">
    <div class="topbar-left">
      <button class="mobile-toggle" id="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('mobile-overlay').classList.toggle('show')">☰</button>
      <div class="topbar-title"><?= e($pageTitle) ?></div>
    </div>
    <div class="topbar-right">
      <span class="topbar-date"><?= date('l, d F Y') ?></span>
      <span class="badge badge-<?= $role === 'user' ? 'success' : ($role === 'operator' ? 'warning' : 'info') ?>"><?= e($role === 'user' ? 'Pasien' : ucfirst($role)) ?></span>
    </div>
  </header>
  <main class="page-body">
<?php endif; ?>
