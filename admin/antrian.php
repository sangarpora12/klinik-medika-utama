<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$errors = [];
$ok     = '';
$csrf   = generateCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'tambah') {
            $pasienId   = (int)($_POST['pasien_id'] ?? 0);
            $dokterId   = (int)($_POST['dokter_id'] ?? 0);
            $poliId     = (int)($_POST['poli_id']   ?? 0);
            $tanggal    = sanitize($_POST['tanggal'] ?? date('Y-m-d'));
            $keluhan    = sanitize($_POST['keluhan'] ?? '');

            if (!$pasienId || !$dokterId || !$poliId)
                $errors[] = 'Pasien, dokter, dan poli wajib dipilih.';
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal))
                $errors[] = 'Format tanggal tidak valid.';

            if (empty($errors)) {
                $maxStmt = $db->prepare(
                    "SELECT COALESCE(MAX(no_antrian),0)+1 FROM antrian WHERE dokter_id=? AND tanggal=?"
                );
                $maxStmt->execute([$dokterId, $tanggal]);
                $noAntrian = (int)$maxStmt->fetchColumn();

                $db->prepare(
                    "INSERT INTO antrian (pasien_id, dokter_id, poli_id, operator_id, tanggal, no_antrian, keluhan)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([$pasienId, $dokterId, $poliId, $_SESSION['user_id'], $tanggal, $noAntrian, $keluhan]);

                auditLog('tambah_antrian', "Admin: Pasien #{$pasienId} → Antrian #{$noAntrian} tgl {$tanggal}");
                $ok = 'Antrian nomor <strong>' . str_pad($noAntrian, 3, '0', STR_PAD_LEFT) . '</strong> berhasil dibuat.';
            }

        } elseif ($aksi === 'ubah_status') {
            $aid    = (int)($_POST['antrian_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['menunggu', 'dipanggil', 'selesai', 'batal'];
            if (!in_array($status, $allowed, true)) {
                $errors[] = 'Status tidak valid.';
            } else {
                $extra = '';
                if ($status === 'dipanggil') $extra = ', jam_panggil=NOW()';
                if ($status === 'selesai')   $extra = ', jam_selesai=NOW()';
                $db->prepare("UPDATE antrian SET status=? {$extra} WHERE id=?")->execute([$status, $aid]);
                auditLog('ubah_status_antrian', "Antrian #{$aid} → {$status}");
                $ok = 'Status antrian berhasil diperbarui.';
            }

        } elseif ($aksi === 'perbarui_status') {
            $aid    = (int)($_POST['antrian_id'] ?? 0);
            $cur = $db->prepare("SELECT status FROM antrian WHERE id=?");
            $cur->execute([$aid]);
            $curSts = $cur->fetchColumn();
            $mapNext = ['menunggu'=>'dipanggil','dipanggil'=>'selesai'];
            $next = $mapNext[$curSts] ?? $curSts;
            if ($next !== $curSts) {
                $extra = '';
                if ($next === 'dipanggil') $extra = ', jam_panggil=NOW()';
                if ($next === 'selesai')   $extra = ', jam_selesai=NOW()';
                $db->prepare("UPDATE antrian SET status=? {$extra} WHERE id=?")->execute([$next, $aid]);
                auditLog('ubah_status_antrian', "Antrian #{$aid} → {$next}");
                $ok = "Status antrian diperbarui menjadi <strong>{$next}</strong>.";
            } else {
                $errors[] = 'Antrian sudah selesai atau dibatalkan.';
            }
        } elseif ($aksi === 'hapus') {
            $aid = (int)($_POST['antrian_id'] ?? 0);
            try {
                $db->prepare("DELETE FROM antrian WHERE id=?")->execute([$aid]);
                auditLog('hapus_antrian', "Hapus antrian #{$aid}");
                $ok = 'Antrian berhasil dihapus.';
            } catch (PDOException $e) {
                $errors[] = 'Tidak dapat menghapus antrian yang sudah memiliki rekam medis.';
            }
        }
    }
}

$pasienList = $db->query("SELECT id, no_rm, nama_lengkap FROM pasien ORDER BY nama_lengkap")->fetchAll();
$dokterList = $db->query("SELECT id, nama, spesialisasi FROM dokter WHERE aktif=1 ORDER BY nama")->fetchAll();
$poliList   = $db->query("SELECT id, nama_poli FROM poli WHERE aktif=1 ORDER BY nama_poli")->fetchAll();

$tgl        = sanitize($_GET['tgl']    ?? date('Y-m-d'));
$filterPoli = (int)($_GET['poli_id']  ?? 0);
$filterSts  = sanitize($_GET['status'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) $tgl = date('Y-m-d');

$where  = ['a.tanggal = ?'];
$params = [$tgl];
if ($filterPoli) { $where[] = 'a.poli_id = ?';  $params[] = $filterPoli; }
if ($filterSts)  { $where[] = 'a.status = ?';   $params[] = $filterSts; }
$wStr = implode(' AND ', $where);

$antrian = $db->prepare(
    "SELECT a.*, p.nama_lengkap AS nama_pasien, p.no_rm,
            pl.nama_poli, d.nama AS nama_dokter
     FROM antrian a
     JOIN pasien p  ON p.id  = a.pasien_id
     JOIN poli   pl ON pl.id = a.poli_id
     JOIN dokter d  ON d.id  = a.dokter_id
     WHERE {$wStr}
     ORDER BY a.no_antrian ASC"
);
$antrian->execute($params);
$antrianList = $antrian->fetchAll();

$stats = $db->prepare(
    "SELECT status, COUNT(*) AS jml FROM antrian WHERE tanggal=? GROUP BY status"
);
$stats->execute([$tgl]);
$statMap = ['menunggu'=>0,'dipanggil'=>0,'selesai'=>0,'batal'=>0];
foreach ($stats->fetchAll() as $s) $statMap[$s['status']] = (int)$s['jml'];
$totalHari = array_sum($statMap);

$pageTitle = 'Kelola Antrian';
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

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;margin-bottom:1.25rem">
  <div class="card" style="text-align:center;padding:.9rem">
    <div style="font-size:1.75rem;font-weight:700"><?= $totalHari ?></div>
    <div class="text-muted" style="font-size:.8rem">Total Antrian</div>
  </div>
  <div class="card" style="text-align:center;padding:.9rem">
    <div style="font-size:1.75rem;font-weight:700;color:#f39c12"><?= $statMap['menunggu'] ?></div>
    <div class="text-muted" style="font-size:.8rem">Menunggu</div>
  </div>
  <div class="card" style="text-align:center;padding:.9rem">
    <div style="font-size:1.75rem;font-weight:700;color:#3498db"><?= $statMap['dipanggil'] ?></div>
    <div class="text-muted" style="font-size:.8rem">Dipanggil</div>
  </div>
  <div class="card" style="text-align:center;padding:.9rem">
    <div style="font-size:1.75rem;font-weight:700;color:#27ae60"><?= $statMap['selesai'] ?></div>
    <div class="text-muted" style="font-size:.8rem">Selesai</div>
  </div>
  <div class="card" style="text-align:center;padding:.9rem">
    <div style="font-size:1.75rem;font-weight:700;color:#e74c3c"><?= $statMap['batal'] ?></div>
    <div class="text-muted" style="font-size:.8rem">Batal</div>
  </div>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="tambah">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group" style="margin:0">
        <label>Pasien</label>
        <select name="pasien_id" required>
          <option value="">-- Pilih Pasien --</option>
          <?php foreach ($pasienList as $p): ?>
          <option value="<?= $p['id'] ?>"><?= e($p['no_rm']) ?> – <?= e($p['nama_lengkap']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Poli</label>
        <select name="poli_id" required>
          <option value="">-- Pilih Poli --</option>
          <?php foreach ($poliList as $pl): ?>
          <option value="<?= $pl['id'] ?>"><?= e($pl['nama_poli']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Dokter</label>
        <select name="dokter_id" required>
          <option value="">-- Pilih Dokter --</option>
          <?php foreach ($dokterList as $d): ?>
          <option value="<?= $d['id'] ?>"><?= e($d['nama']) ?> – <?= e($d['spesialisasi']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Tanggal Kunjungan</label>
        <input type="date" name="tanggal" value="<?= e(date('Y-m-d')) ?>" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group" style="margin:0;grid-column:1/-1">
        <label>Keluhan Utama</label>
        <input type="text" name="keluhan" placeholder="Deskripsikan keluhan pasien…" maxlength="500">
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:1rem">Daftarkan Antrian</button>
  </form>
</div>

<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end">
    <div class="form-group" style="margin:0">
      <label>Tanggal</label>
      <input type="date" name="tgl" value="<?= e($tgl) ?>" style="width:160px">
    </div>
    <div class="form-group" style="margin:0">
      <label>Poli</label>
      <select name="poli_id" style="width:160px">
        <option value="">Semua Poli</option>
        <?php foreach ($poliList as $pl): ?>
        <option value="<?= $pl['id'] ?>" <?= $filterPoli==$pl['id']?'selected':'' ?>><?= e($pl['nama_poli']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label>Status</label>
      <select name="status" style="width:140px">
        <option value="">Semua Status</option>
        <?php foreach (['menunggu'=>'Menunggu','dipanggil'=>'Dipanggil','selesai'=>'Selesai','batal'=>'Batal'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= $filterSts===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary btn-sm">🔍 Filter</button>
    <a href="antrian.php" class="btn btn-secondary btn-sm">Reset</a>
    <span class="text-muted" style="margin-left:auto;font-size:.85rem"><?= count($antrianList) ?> antrian ditemukan</span>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>No.</th><th>Pasien</th><th>Poli</th><th>Dokter</th>
          <th>Keluhan</th><th>Daftar</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $bc = ['menunggu'=>'badge-warning','dipanggil'=>'badge-info','selesai'=>'badge-success','batal'=>'badge-danger']; ?>
        <?php foreach ($antrianList as $a): ?>
        <tr>
          <td style="font-size:1.4rem;font-weight:800;color:var(--primary)">
            <?= str_pad($a['no_antrian'], 3, '0', STR_PAD_LEFT) ?>
          </td>
          <td>
            <div style="font-weight:600"><?= e($a['nama_pasien']) ?></div>
            <div class="text-muted" style="font-size:.8rem">RM: <?= e($a['no_rm']) ?></div>
          </td>
          <td><?= e($a['nama_poli']) ?></td>
          <td><?= e($a['nama_dokter']) ?></td>
          <td style="max-width:160px;font-size:.85rem"><?= e($a['keluhan'] ?? '–') ?></td>
          <td class="text-muted" style="font-size:.82rem;white-space:nowrap">
            <?= date('d/m/Y H:i', strtotime($a['jam_daftar'])) ?>
          </td>
          <td><span class="badge <?= $bc[$a['status']] ?? '' ?>"><?= ucfirst($a['status']) ?></span></td>
          <td>
            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
              <?php $nextStatus = ['menunggu'=>'dipanggil','dipanggil'=>'selesai','selesai'=>'selesai','batal'=>'batal']; ?>
              <?php $nextLabel  = ['menunggu'=>'Panggil','dipanggil'=>'Selesai','selesai'=>'✔ Selesai','batal'=>'✘ Batal']; ?>
              <?php if ($a['status'] !== 'selesai' && $a['status'] !== 'batal'): ?>
              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="aksi" value="perbarui_status">
                <input type="hidden" name="antrian_id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">Perbarui</button>
              </form>
              <?php else: ?>
              <span class="text-muted" style="font-size:.82rem">Selesai</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$antrianList): ?>
        <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--gray-500)">
          Tidak ada antrian pada tanggal <?= date('d/m/Y', strtotime($tgl)) ?>.
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>