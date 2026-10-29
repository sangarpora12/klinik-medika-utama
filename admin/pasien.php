<?php
// admin/pasien.php – CRUD Data Pasien (Admin only)
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$errors = [];
$ok     = '';

function generateNoRM(PDO $db): string {
    $tahun = date('Y');
    $last  = $db->query("SELECT no_rm FROM pasien WHERE no_rm LIKE 'RM-$tahun-%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    $next  = $last ? ((int)substr($last, -4) + 1) : 1;
    return sprintf('RM-%s-%04d', $tahun, $next);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                $db->prepare("DELETE FROM pasien WHERE id=?")->execute([$id]);
                auditLog('admin_hapus_pasien', "Hapus pasien #{$id}");
                $ok = 'Data pasien berhasil dihapus.';
            } catch (PDOException $e) {
                $errors[] = 'Tidak dapat menghapus: pasien masih memiliki data antrian/rekam medis terkait.';
            }
        } else {
            $id           = (int)($_POST['id'] ?? 0);
            $namaLengkap  = sanitize($_POST['nama_lengkap'] ?? '');
            $nik          = sanitize($_POST['nik'] ?? '');
            $tglLahir     = sanitize($_POST['tanggal_lahir'] ?? '');
            $jk           = sanitize($_POST['jenis_kelamin'] ?? '');
            $golDarah     = sanitize($_POST['gol_darah'] ?? 'tidak_diketahui');
            $alamat       = sanitize($_POST['alamat'] ?? '');
            $telepon      = sanitize($_POST['telepon'] ?? '');
            $email        = sanitize($_POST['email'] ?? '');
            $alergi       = sanitize($_POST['alergi'] ?? '');

            if ($namaLengkap === '' || mb_strlen($namaLengkap) < 3) {
                $errors[] = 'Nama lengkap wajib diisi (minimal 3 karakter).';
            }
            if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
                $errors[] = 'NIK harus berupa 16 digit angka.';
            }
            if (!in_array($jk, ['L', 'P'], true)) {
                $errors[] = 'Jenis kelamin wajib dipilih.';
            }
            if (!in_array($golDarah, ['A','B','AB','O','tidak_diketahui'], true)) {
                $golDarah = 'tidak_diketahui';
            }
            if ($tglLahir !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglLahir)) {
                $errors[] = 'Format tanggal lahir tidak valid.';
            }
            if ($telepon !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $telepon)) {
                $errors[] = 'Format nomor telepon tidak valid.';
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format email tidak valid.';
            }

            if (empty($errors) && $nik !== '') {
                $cek = $db->prepare("SELECT id FROM pasien WHERE nik=? AND id<>?");
                $cek->execute([$nik, $id]);
                if ($cek->fetch()) {
                    $errors[] = 'NIK sudah terdaftar untuk pasien lain.';
                }
            }

            if (empty($errors)) {
                if ($id > 0) {
                    $stmt = $db->prepare(
                        "UPDATE pasien SET nama_lengkap=?, nik=?, tanggal_lahir=?, jenis_kelamin=?,
                         gol_darah=?, alamat=?, telepon=?, email=?, alergi=? WHERE id=?"
                    );
                    $stmt->execute([
                        $namaLengkap, $nik ?: null, $tglLahir ?: null, $jk,
                        $golDarah, $alamat ?: null, $telepon ?: null, $email ?: null, $alergi ?: null, $id
                    ]);
                    auditLog('admin_ubah_pasien', "Update pasien #{$id}: {$namaLengkap}");
                    $ok = 'Data pasien berhasil diperbarui.';
                } else {
                    $noRm = generateNoRM($db);
                    $stmt = $db->prepare(
                        "INSERT INTO pasien (no_rm, nama_lengkap, nik, tanggal_lahir, jenis_kelamin, gol_darah, alamat, telepon, email, alergi)
                         VALUES (?,?,?,?,?,?,?,?,?,?)"
                    );
                    $stmt->execute([
                        $noRm, $namaLengkap, $nik ?: null, $tglLahir ?: null, $jk,
                        $golDarah, $alamat ?: null, $telepon ?: null, $email ?: null, $alergi ?: null
                    ]);
                    auditLog('admin_tambah_pasien', "Tambah pasien: {$namaLengkap} ({$noRm})");
                    $ok = "Pasien baru berhasil ditambahkan dengan No. RM: {$noRm}";
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
    $where[]  = "(nama_lengkap LIKE ? OR no_rm LIKE ? OR nik LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM pasien WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT * FROM pasien WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$pasienList = $stmt->fetchAll();

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $es = $db->prepare("SELECT * FROM pasien WHERE id=?");
    $es->execute([$editId]);
    $editData = $es->fetch() ?: null;
}

$csrf = generateCsrf();
$pageTitle = 'Manajemen Pasien';
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
  <h3 style="margin-bottom:1rem"><?= $editData ? 'Edit Pasien' : 'Tambah Pasien Baru' ?></h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" value="<?= e((string)($editData['id'] ?? 0)) ?>">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" required value="<?= e($editData['nama_lengkap'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>NIK (16 digit)</label>
        <input type="text" name="nik" maxlength="16" value="<?= e($editData['nik'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?= e($editData['tanggal_lahir'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Jenis Kelamin *</label>
        <select name="jenis_kelamin" required>
          <option value="">— Pilih —</option>
          <option value="L" <?= (($editData['jenis_kelamin'] ?? '')==='L')?'selected':'' ?>>Laki-laki</option>
          <option value="P" <?= (($editData['jenis_kelamin'] ?? '')==='P')?'selected':'' ?>>Perempuan</option>
        </select>
      </div>
      <div class="form-group">
        <label>Golongan Darah</label>
        <select name="gol_darah">
          <?php foreach (['tidak_diketahui'=>'Tidak diketahui','A'=>'A','B'=>'B','AB'=>'AB','O'=>'O'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= (($editData['gol_darah'] ?? 'tidak_diketahui')===$v)?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= e($editData['telepon'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($editData['email'] ?? '') ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= e($editData['alamat'] ?? '') ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Alergi</label>
        <input type="text" name="alergi" value="<?= e($editData['alergi'] ?? '') ?>">
      </div>
    </div>
    <div style="margin-top:1rem;display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary"><?= $editData ? 'Simpan Perubahan' : 'Tambah Pasien' ?></button>
      <?php if ($editData): ?><a href="pasien.php" class="btn btn-secondary">Batal</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, No. RM, NIK…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="pasien.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> pasien ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>No. RM</th><th>Nama</th><th>JK</th><th>Tgl Lahir</th><th>Telepon</th><th>Gol. Darah</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($pasienList as $i => $p): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td><span class="badge badge-info"><?= e($p['no_rm']) ?></span></td>
          <td style="font-weight:600"><?= e($p['nama_lengkap']) ?></td>
          <td><?= $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
          <td><?= $p['tanggal_lahir'] ? date('d/m/Y', strtotime($p['tanggal_lahir'])) : '–' ?></td>
          <td><?= e($p['telepon'] ?? '–') ?></td>
          <td><?= $p['gol_darah'] === 'tidak_diketahui' ? '–' : e($p['gol_darah']) ?></td>
          <td style="display:flex;gap:.4rem">
            <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" onsubmit="return confirm('Hapus data pasien ini?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$pasienList): ?>
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data pasien.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>