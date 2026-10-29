<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('user');

$db     = getDB();
$userId = $_SESSION['user_id'];
$errors = [];
$ok     = '';
$csrf   = generateCsrf();

$pasienStmt = $db->prepare("SELECT * FROM pasien WHERE user_id=? LIMIT 1");
$pasienStmt->execute([$userId]);
$pasien = $pasienStmt->fetch();

if (!$pasien) {
    header('Location: ' . BASE_URL . '/user/profil.php?tab=data_diri');
    exit;
}
$pasienId = $pasien['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'daftar') {
            $poliId   = (int)($_POST['poli_id'] ?? 0);
            $dokterId = (int)($_POST['dokter_id'] ?? 0);
            $tanggal  = sanitize($_POST['tanggal'] ?? date('Y-m-d'));
            $keluhan  = sanitize($_POST['keluhan'] ?? '');

            if (!$poliId || !$dokterId) $errors[] = 'Poli dan dokter wajib dipilih.';
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) $errors[] = 'Format tanggal tidak valid.';

            if (empty($errors)) {
                $maxStmt = $db->prepare(
                    "SELECT COALESCE(MAX(no_antrian),0)+1 FROM antrian WHERE dokter_id=? AND tanggal=?"
                );
                $maxStmt->execute([$dokterId, $tanggal]);
                $noAntrian = (int)$maxStmt->fetchColumn();

                $db->prepare(
                    "INSERT INTO antrian (pasien_id, dokter_id, poli_id, operator_id, tanggal, no_antrian, keluhan)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([$pasienId, $dokterId, $poliId, null, $tanggal, $noAntrian, $keluhan]);

                $ok = "Pendaftaran antrian berhasil. Nomor antrian Anda: <strong>" . str_pad($noAntrian, 3, '0', STR_PAD_LEFT) . "</strong>";
            }
        }

        if ($aksi === 'batal') {
            $aid = (int)($_POST['antrian_id'] ?? 0);
            $db->prepare("UPDATE antrian SET status='batal' WHERE id=? AND pasien_id=?")->execute([$aid, $pasienId]);
            $ok = 'Antrian berhasil dibatalkan.';
        }
    }
}

$dokterList = $db->query("SELECT id, nama, spesialisasi FROM dokter WHERE aktif=1 ORDER BY nama")->fetchAll();
$poliList   = $db->query("SELECT id, nama_poli FROM poli WHERE aktif=1 ORDER BY nama_poli")->fetchAll();

$tgl = sanitize($_GET['tgl'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) $tgl = date('Y-m-d');

$stmt = $db->prepare(
    "SELECT a.*, pl.nama_poli, d.nama AS nama_dokter
     FROM antrian a
     JOIN poli pl ON pl.id = a.poli_id
     JOIN dokter d ON d.id = a.dokter_id
     WHERE a.pasien_id=? AND a.tanggal=?
     ORDER BY a.jam_daftar DESC"
);
$stmt->execute([$pasienId, $tgl]);
$antrianList = $stmt->fetchAll();

$pageTitle = 'Daftar Antrian Saya';
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

<?php if ($ok): ?><div class="alert alert-success"><?= $ok ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $e_): ?><li><?= e($e_) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card" style="margin-bottom:1.25rem">
  <h3 style="margin-bottom:1rem">📋 Daftar Antrian Baru</h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="daftar">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group" style="margin:0">
        <label>Poli *</label>
        <select name="poli_id" required>
          <option value="">-- Pilih Poli --</option>
          <?php foreach ($poliList as $p): ?>
          <option value="<?= $p['id'] ?>"><?= e($p['nama_poli']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Dokter *</label>
        <select name="dokter_id" required>
          <option value="">-- Pilih Dokter --</option>
          <?php foreach ($dokterList as $d): ?>
          <option value="<?= $d['id'] ?>"><?= e($d['nama']) ?> – <?= e($d['spesialisasi']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Tanggal *</label>
        <input type="date" name="tanggal" value="<?= e($tgl) ?>" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group" style="margin:0;grid-column:1/-1">
        <label>Keluhan</label>
        <input type="text" name="keluhan" placeholder="Jelaskan keluhan Anda…">
      </div>
      <div class="form-group" style="margin:0;grid-column:1/-1">
        <button type="submit" class="btn btn-primary">Daftar Antrian</button>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <h3 style="margin-bottom:1rem">🩺 Antrian Hari Ini</h3>
  <div class="table-wrap">
    <table>
      <thead><tr><th>No.</th><th>Poli</th><th>Dokter</th><th>Keluhan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($antrianList as $a): ?>
        <tr>
          <td style="font-weight:700;color:var(--primary);font-size:1.2rem"><?= str_pad($a['no_antrian'],3,'0',STR_PAD_LEFT) ?></td>
          <td><?= e($a['nama_poli']) ?></td>
          <td><?= e($a['nama_dokter']) ?></td>
          <td style="max-width:180px;font-size:.85rem"><?= e($a['keluhan'] ?? '–') ?></td>
          <td><span class="badge badge-<?= $a['status']==='menunggu'?'warning':($a['status']==='dipanggil'?'info':($a['status']==='selesai'?'success':'danger')) ?>"><?= ucfirst($a['status']) ?></span></td>
          <td>
            <?php if ($a['status']==='menunggu'): ?>
            <form method="POST" onsubmit="return confirm('Batalkan antrian ini?')">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <input type="hidden" name="aksi" value="batal">
              <input type="hidden" name="antrian_id" value="<?= $a['id'] ?>">
              <button class="btn btn-sm btn-danger">Batal</button>
            </form>
            <?php else: ?>
            <span class="text-muted" style="font-size:.8rem">Selesai</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$antrianList): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--gray-500)">Tidak ada antrian hari ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>