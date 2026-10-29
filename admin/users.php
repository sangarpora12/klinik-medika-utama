<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$errors = [];
$ok     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'reset_password') {
            $id    = (int)$_POST['id'];
            $newPw = $_POST['new_password'] ?? '';
            if (strlen($newPw) < 8) $errors[] = 'Password minimal 8 karakter.';
            if (empty($errors)) {
                $hash = password_hash($newPw, PASSWORD_BCRYPT, ['cost'=>12]);
                $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $id]);
                auditLog('reset_password', "Reset password user #{$id}");
                $ok = 'Password berhasil direset.';
            }
        } elseif ($aksi === 'toggle_status') {
            $id     = (int)$_POST['id'];
            $aktif  = $_POST['aktif'] ?? 'aktif';
            $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$aktif, $id]);
            auditLog('toggle_user', "Ubah status user #{$id} menjadi {$aktif}");
            $ok = 'Status pengguna diperbarui.';
        } elseif ($aksi === 'ubah_role') {
            $id     = (int)$_POST['id'];
            $roleId = (int)$_POST['role_id'];
            $db->prepare("UPDATE users SET role_id=? WHERE id=?")->execute([$roleId, $id]);
            auditLog('ubah_role', "Ubah role user #{$id}");
            $ok = 'Role pengguna diperbarui.';
        } elseif ($aksi === 'hapus_akun') {
            $id = (int)$_POST['id'];
            if ($id === $_SESSION['user_id']) {
                $errors[] = 'Tidak dapat menghapus akun sendiri.';
            } else {
                try {
                    $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
                    auditLog('hapus_akun', "Hapus permanen user #{$id}");
                    $ok = 'Akun berhasil dihapus permanen.';
                } catch (PDOException $e) {
                    $errors[] = 'Gagal menghapus akun.';
                }
            }
        }
    }
}

$search  = sanitize($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = "(nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT u.*, r.nama_role
     FROM users u JOIN roles r ON r.id=u.role_id
     WHERE $whereStr ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$userList = $stmt->fetchAll();

$roles = $db->query("SELECT * FROM roles ORDER BY id")->fetchAll();

$csrf = generateCsrf();
$pageTitle = 'Manajemen Pengguna';
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
<?php if ($errors): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, username, email…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="users.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> pengguna ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($userList as $i => $u): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td>
            <div style="font-weight:600"><?= e($u['nama_lengkap']) ?></div>
            <div class="text-muted"><?= e($u['email']) ?></div>
          </td>
          <td><?= e($u['username']) ?></td>
          <td class="text-muted"><?= e($u['email']) ?></td>
          <td><span class="badge badge-info"><?= e($u['nama_role']) ?></span></td>
          <td>
            <span class="badge <?= $u['status']==='aktif' ? 'badge-success' : 'badge-danger' ?>">
              <?= ucfirst($u['status']) ?>
            </span>
          </td>
          <td>
            <div style="position:relative;display:inline-block">
              <button type="button" class="btn btn-sm btn-secondary btn-menu-toggle" data-menu="menu-<?= $u['id'] ?>">•••</button>
              <div id="menu-<?= $u['id'] ?>" class="dropdown-menu" style="display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid var(--border);border-radius:10px;padding:.35rem 0;min-width:180px;box-shadow:var(--shadow-md);z-index:20">
                <form method="POST" style="display:flex;align-items:center;gap:.35rem;padding:.35rem .7rem">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="aksi" value="ubah_role">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <select name="role_id" style="font-size:.78rem;padding:.3rem;flex:1" onchange="this.form.submit()">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id']==$u['role_id']?'selected':'' ?>><?= e($r['nama_role']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary" style="padding:.3rem .5rem">✓</button>
                </form>
                <form method="POST" onsubmit="return confirm('Ubah status akun ini?')">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="aksi" value="toggle_status">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="aktif" value="<?= $u['status']==='aktif' ? 'nonaktif' : 'aktif' ?>">
                  <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;padding:.4rem .7rem;font-size:.82rem;cursor:pointer;color:var(--text)">
                    <?= $u['status']==='aktif' ? '🔕 Nonaktifkan Akun' : '✅ Aktifkan Akun' ?>
                  </button>
                </form>
                <button type="button" class="dropdown-item" onclick="openResetModal(<?= $u['id'] ?>)" style="width:100%;text-align:left;background:none;border:none;padding:.4rem .7rem;font-size:.82rem;cursor:pointer;color:var(--text)">
                  🔑 Reset Password
                </button>
                <hr style="border:0;border-top:1px solid var(--border-light);margin:.3rem 0">
                <form method="POST" onsubmit="return confirm('HAPUS PERMANEN akun ini?\n\nAksi ini tidak bisa dibatalkan.')">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                  <input type="hidden" name="aksi" value="hapus_akun">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;padding:.4rem .7rem;font-size:.82rem;cursor:pointer;color:var(--danger)">
                    🗑 Hapus Akun
                  </button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$userList): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data pengguna.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<!-- Modal Reset Password -->
<div id="reset-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;padding:1.5rem;width:90%;max-width:380px;box-shadow:var(--shadow-xl)">
    <h3 style="margin-top:0">Reset Password</h3>
    <form id="reset-form" method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="aksi" value="reset_password">
      <input type="hidden" name="id" id="reset-id" value="">
      <div class="form-group">
        <label>Password Baru</label>
        <input type="password" name="new_password" id="reset-pw" required minlength="8" placeholder="Minimal 8 karakter">
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('reset-modal').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('click', function(e) {
  // toggle button
  var btn = e.target.closest('.btn-menu-toggle');
  if (btn) {
    var menuId = btn.getAttribute('data-menu');
    document.querySelectorAll('.dropdown-menu').forEach(function(m) {
      m.style.display = (m.id === menuId && m.style.display !== 'block') ? 'block' : 'none';
    });
    return;
  }
  // inside dropdown -> jangan tutup
  if (e.target.closest('.dropdown-menu')) return;
  // luar -> tutup semua
  document.querySelectorAll('.dropdown-menu').forEach(function(m) { m.style.display = 'none'; });
});

function openResetModal(id){
  document.getElementById('reset-modal').style.display='flex';
  document.getElementById('reset-id').value = id;
  document.getElementById('reset-pw').value = '';
  document.getElementById('reset-pw').focus();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>