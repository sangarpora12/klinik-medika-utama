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

        if ($aksi === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                $db->prepare("DELETE FROM dokter WHERE id=?")->execute([$id]);
                auditLog('admin_hapus_dokter', "Hapus dokter #{$id}");
                $ok = 'Data dokter berhasil dihapus.';
            } catch (PDOException $e) {
                $errors[] = 'Tidak dapat menghapus: dokter masih memiliki data antrian/rekam medis terkait.';
            }
        } else {
            $id           = (int)($_POST['id'] ?? 0);
            $namaDokter   = sanitize($_POST['nama_dokter'] ?? '');
            $bidang       = sanitize($_POST['bidang_keahlian'] ?? '');
            $nomorIzin    = sanitize($_POST['nomor_izin'] ?? '');
            $kontak       = sanitize($_POST['kontak'] ?? '');
            $surel        = sanitize($_POST['surel'] ?? '');
            $jadwalJson   = $_POST['jadwal_praktek'] ?? '{}';
            $statusAktif  = isset($_POST['status_aktif']) ? 1 : 0;

            if ($namaDokter === '' || mb_strlen($namaDokter) < 3) {
                $errors[] = 'Nama dokter wajib diisi (minimal 3 karakter).';
            }
            if ($bidang === '') $errors[] = 'Spesialisasi wajib diisi.';

            if (empty($errors) && $nomorIzin !== '') {
                $cek = $db->prepare("SELECT id FROM dokter WHERE no_sip=? AND id<>?");
                $cek->execute([$nomorIzin, $id]);
                if ($cek->fetch()) $errors[] = 'Nomor SIP sudah terdaftar untuk dokter lain.';
            }

            if (empty($errors)) {
                if ($id > 0) {
                    $stmt = $db->prepare(
                        "UPDATE dokter SET nama=?, spesialisasi=?, no_sip=?, telepon=?, email=?, jadwal=?, aktif=? WHERE id=?"
                    );
                    $stmt->execute([$namaDokter, $bidang, $nomorIzin ?: null, $kontak ?: null, $surel ?: null, $jadwalJson, $statusAktif, $id]);
                    auditLog('admin_ubah_dokter', "Update dokter #{$id}: {$namaDokter}");
                    $ok = 'Data dokter berhasil diperbarui.';
                } else {
                    $stmt = $db->prepare(
                        "INSERT INTO dokter (nama, spesialisasi, no_sip, telepon, email, jadwal, aktif)
                         VALUES (?,?,?,?,?,?,?)"
                    );
                    $stmt->execute([$namaDokter, $bidang, $nomorIzin ?: null, $kontak ?: null, $surel ?: null, $jadwalJson, $statusAktif]);
                    auditLog('admin_tambah_dokter', "Tambah dokter: {$namaDokter}");
                    $ok = "Dokter baru berhasil ditambahkan.";
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
    $where[] = "(nama LIKE ? OR spesialisasi LIKE ? OR no_sip LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare("SELECT COUNT(*) FROM dokter WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $db->prepare(
    "SELECT * FROM dokter WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$dokterList = $stmt->fetchAll();

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $es = $db->prepare("SELECT * FROM dokter WHERE id=?");
    $es->execute([$editId]);
    $editData = $es->fetch() ?: null;
}

$csrf = generateCsrf();
$pageTitle = 'Manajemen Dokter';
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
  <h3 style="margin-bottom:1rem"><?= $editData ? 'Edit Dokter' : 'Tambah Dokter Baru' ?></h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" value="<?= e((string)($editData['id'] ?? 0)) ?>">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group">
        <label>Nama Dokter *</label>
        <input type="text" name="nama_dokter" required value="<?= e($editData['nama'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Spesialisasi *</label>
        <input type="text" name="bidang_keahlian" required value="<?= e($editData['spesialisasi'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>No. SIP</label>
        <input type="text" name="nomor_izin" value="<?= e($editData['no_sip'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Telepon</label>
        <input type="text" name="kontak" value="<?= e($editData['telepon'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="surel" value="<?= e($editData['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Jadwal Praktek (JSON)</label>
        <input type="text" name="jadwal_praktek" value="<?= e($editData['jadwal'] ?? '{}') ?>">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status_aktif">
          <option value="1" <?= (($editData['aktif'] ?? 1)==1)?'selected':'' ?>>Aktif</option>
          <option value="0" <?= (($editData['aktif'] ?? 1)==0)?'selected':'' ?>>Nonaktif</option>
        </select>
      </div>
    </div>
    <div style="margin-top:1rem;display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary"><?= $editData ? 'Simpan Perubahan' : 'Tambah Dokter' ?></button>
      <?php if ($editData): ?><a href="dokter.php" class="btn btn-secondary">Batal</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" class="d-flex gap-1 align-center" style="flex-wrap:wrap">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, spesialisasi, SIP…" style="width:260px">
    <button type="submit" class="btn btn-sm btn-primary">🔍 Cari</button>
    <a href="dokter.php" class="btn btn-sm btn-secondary">Reset</a>
    <span class="text-muted" style="margin-left:auto"><?= $total ?> dokter ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Dokter</th><th>Spesialisasi</th><th>No. SIP</th><th>Kontak</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($dokterList as $i => $d): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td style="font-weight:600"><?= e($d['nama']) ?></td>
          <td><?= e($d['spesialisasi']) ?></td>
          <td><?= e($d['no_sip'] ?? '–') ?></td>
          <td><?= e($d['telepon'] ?? '–') ?></td>
          <td>
            <span class="badge <?= $d['aktif'] ? 'badge-success' : 'badge-danger' ?>">
              <?= $d['aktif'] ? 'Aktif' : 'Nonaktif' ?>
            </span>
          </td>
          <td style="display:flex;gap:.4rem">
            <a href="?edit=<?= $d['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
            <form method="POST" onsubmit="return confirm('Hapus data dokter ini?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$dokterList): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada data dokter.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>