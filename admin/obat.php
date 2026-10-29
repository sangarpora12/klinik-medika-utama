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

        if ($aksi === 'tambah' || $aksi === 'edit') {
            $namaObat   = sanitize($_POST['nama_obat'] ?? '');
            $kategori   = sanitize($_POST['kategori'] ?? '');
            $satuan     = sanitize($_POST['satuan'] ?? 'tablet');
            $stok       = (int)($_POST['stok'] ?? 0);
            $harga      = (float)($_POST['harga'] ?? 0);
            $keterangan = sanitize($_POST['keterangan'] ?? '');
            $id         = ($aksi === 'edit') ? (int)$_POST['id'] : 0;

            if ($namaObat === '') $errors[] = 'Nama obat wajib diisi.';

            if (empty($errors)) {
                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE obat SET nama_obat=?, kategori=?, satuan=?, stok=?, harga=?, keterangan=? WHERE id=?");
                    $stmt->execute([$namaObat, $kategori, $satuan, $stok, $harga, $keterangan, $id]);
                    $ok = 'Data obat berhasil diperbarui.';
                } else {
                    $stmt = $db->prepare("INSERT INTO obat (nama_obat, kategori, satuan, stok, harga, keterangan) VALUES (?,?,?,?,?,?)");
                    $stmt->execute([$namaObat, $kategori, $satuan, $stok, $harga, $keterangan]);
                    $ok = 'Obat baru berhasil ditambahkan.';
                }
            }
        } elseif ($aksi === 'update_stok') {
            $id   = (int)$_POST['id'];
            $op   = $_POST['op'] ?? '+';
            $jml  = (int)$_POST['stok'];
            $label = $op === '+' ? 'Tambah' : 'Kurangi';
            $db->prepare("UPDATE obat SET stok = GREATEST(0, stok {$op} ?) WHERE id=?")->execute([$jml, $id]);
            auditLog('update_stok_obat', "{$label} obat #{$id}");
            $ok = 'Stok obat berhasil diperbarui.';
        } elseif ($aksi === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            $db->prepare("DELETE FROM obat WHERE id=?")->execute([$id]);
            auditLog('hapus_obat', "Hapus obat #{$id}");
            $ok = 'Data obat berhasil dihapus.';
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
    $where[] = "(nama_obat LIKE ? OR kategori LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM obat WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT * FROM obat WHERE $whereStr ORDER BY nama_obat ASC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$obatList = $stmt->fetchAll();

$kategoriList = $db->query("SELECT DISTINCT kategori FROM obat WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori")->fetchAll();

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $es = $db->prepare("SELECT * FROM obat WHERE id=? LIMIT 1");
    $es->execute([$editId]);
    $editData = $es->fetch() ?: null;
}

$csrf = generateCsrf();
$pageTitle = 'Manajemen Obat';
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
  <h3 style="margin-bottom:1rem"><?= $editData ? 'Edit Obat' : 'Tambah Obat Baru' ?></h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
    <input type="hidden" name="id" value="<?= e((string)($editData['id'] ?? 0)) ?>">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group">
        <label>Nama Obat *</label>
        <input type="text" name="nama_obat" required value="<?= e($editData['nama_obat'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <input type="text" name="kategori" list="kategoriList" value="<?= e($editData['kategori'] ?? '') ?>">
        <datalist id="kategoriList"><?php foreach ($kategoriList as $k): ?><option value="<?= e($k['kategori']) ?>"><?php endforeach; ?></datalist>
      </div>
      <div class="form-group">
        <label>Satuan</label>
        <select name="satuan">
          <?php foreach (['tablet'=>'Tablet','kapsul'=>'Kapsul','botol'=>'Botol','tube'=>'Tube','perstrip'=>'Per Strip','sachet'=>'Sachet'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= (($editData['satuan'] ?? 'tablet')===$v)?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Stok</label>
        <input type="number" name="stok" value="<?= e($editData['stok'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label>Harga (Rp)</label>
        <input type="number" step="0.01" name="harga" value="<?= e($editData['harga'] ?? 0) ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Keterangan</label>
        <textarea name="keterangan" rows="2"><?= e($editData['keterangan'] ?? '') ?></textarea>
      </div>
    </div>
    <div style="margin-top:1rem;display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary"><?= $editData ? 'Simpan Perubahan' : 'Tambah Obat' ?></button>
      <?php if ($editData): ?><a href="obat.php" class="btn btn-secondary">Batal</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama obat atau kategori…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="obat.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> obat ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Obat</th><th>Kategori</th><th>Satuan</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($obatList as $i => $o): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td style="font-weight:600"><?= e($o['nama_obat']) ?></td>
          <td><?= e($o['kategori'] ?? '–') ?></td>
          <td><?= e($o['satuan']) ?></td>
          <td><span class="badge <?= $o['stok'] > 10 ? 'badge-success' : ($o['stok'] > 0 ? 'badge-warning' : 'badge-danger') ?>"><?= $o['stok'] ?></span></td>
          <td>Rp <?= number_format($o['harga'], 0, ',', '.') ?></td>
          <td style="display:flex;gap:.4rem">
            <a href="?edit=<?= $o['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" onsubmit="return confirm('Hapus data obat ini?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= $o['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$obatList): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data obat.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>