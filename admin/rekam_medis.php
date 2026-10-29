<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$errors = [];
$ok     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $antrianId = (int)($_POST['antrian_id'] ?? 0);
        $pasienId  = (int)($_POST['pasien_id'] ?? 0);
        $dokterId  = (int)($_POST['dokter_id'] ?? 0);
        $tanggal   = sanitize($_POST['tanggal'] ?? date('Y-m-d'));
        $anamnesis = sanitize($_POST['anamnesis'] ?? '');
        $diagnosa  = sanitize($_POST['diagnosa'] ?? '');
        $kodeIcd   = sanitize($_POST['kode_icd'] ?? '');
        $tindakan  = sanitize($_POST['tindakan'] ?? '');
        $resep     = sanitize($_POST['resep'] ?? '');
        $tekanan   = sanitize($_POST['tekanan_darah'] ?? '');
        $suhu      = (float)($_POST['suhu'] ?? 0);

        if (!$antrianId || !$pasienId || !$dokterId) $errors[] = 'Data antrian, pasien, dan dokter wajib diisi.';

        if (empty($errors)) {
            $db->prepare(
                "INSERT INTO rekam_medis
                 (antrian_id, pasien_id, dokter_id, tanggal, anamnesis, diagnosa, kode_icd, tindakan, resep, tekanan_darah, suhu)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            )->execute([$antrianId, $pasienId, $dokterId, $tanggal, $anamnesis, $diagnosa, $kodeIcd, $tindakan, $resep, $tekanan, $suhu]);
            $ok = 'Rekam medis berhasil ditambahkan.';
        }
    }
}

$search  = sanitize($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = "(p.nama_lengkap LIKE ? OR d.nama LIKE ? OR r.diagnosa LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
}
$whereStr = implode(' AND ', $where);

$totalStmt = $db->prepare(
    "SELECT COUNT(*) FROM rekam_medis r
     JOIN pasien p ON p.id = r.pasien_id
     JOIN dokter d ON d.id = r.dokter_id
     WHERE $whereStr"
);
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

// Ambil daftar obat untuk dropdown resep
$obatList = $db->query("SELECT id, nama_obat, stok FROM obat ORDER BY nama_obat")->fetchAll();

$stmt = $db->prepare(
    "SELECT r.*, p.nama_lengkap, p.no_rm, d.nama AS nama_dokter
     FROM rekam_medis r
     JOIN pasien p ON p.id = r.pasien_id
     JOIN dokter d ON d.id = r.dokter_id
     WHERE $whereStr
     ORDER BY r.tanggal DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$rekamList = $stmt->fetchAll();

$csrf = generateCsrf();
$pageTitle = 'Rekam Medis';
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
  <h3 style="margin-bottom:1rem">📝 Tambah Rekam Medis Baru</h3>
  <form method="POST" id="rm-form">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="aksi" value="tambah">
    <input type="hidden" name="resep" id="resep-hidden" value="">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group">
        <label>Antrian</label>
        <select name="antrian_id" required>
          <option value="">-- Pilih Antrian --</option>
          <?php foreach ($db->query("SELECT a.id, a.no_antrian, a.tanggal, p.nama_lengkap FROM antrian a JOIN pasien p ON p.id = a.pasien_id WHERE a.status IN ('menunggu','dipanggil') ORDER BY a.tanggal DESC, a.no_antrian DESC") as $ant): ?>
          <option value="<?= $ant['id'] ?>">A-<?= str_pad($ant['no_antrian'],3,'0',STR_PAD_LEFT) ?> — <?= e($ant['nama_lengkap']) ?> (<?= date('d/m/Y', strtotime($ant['tanggal'])) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Pasien</label>
        <select name="pasien_id" required>
          <option value="">-- Pilih Pasien --</option>
          <?php foreach ($db->query("SELECT id, no_rm, nama_lengkap FROM pasien ORDER BY nama_lengkap") as $p): ?>
          <option value="<?= $p['id'] ?>"><?= e($p['no_rm']) ?> – <?= e($p['nama_lengkap']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Dokter</label>
        <select name="dokter_id" required>
          <option value="">-- Pilih Dokter --</option>
          <?php foreach ($db->query("SELECT id, nama FROM dokter WHERE aktif=1 ORDER BY nama") as $d): ?>
          <option value="<?= $d['id'] ?>"><?= e($d['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tanggal Periksa</label>
        <input type="date" name="tanggal" value="<?= e(date('Y-m-d')) ?>" required>
      </div>
      <div class="form-group">
        <label>Tensi Darah</label>
        <input type="text" name="tekanan_darah" placeholder="120/80">
      </div>
      <div class="form-group">
        <label>Suhu (°C)</label>
        <input type="number" step="0.1" name="suhu" value="36.5">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Anamnesis</label>
        <textarea name="anamnesis" rows="2" placeholder="Keluhan utama pasien…"></textarea>
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Diagnosa</label>
        <textarea name="diagnosa" rows="2"></textarea>
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Tindakan</label>
        <textarea name="tindakan" rows="2"></textarea>
      </div>
    </div>

    <h4 style="margin:1.5rem 0 .75rem">💊 Resep Obat</h4>
    <div id="obat-list" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.75rem">
      <div class="obat-row" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
        <select name="obat_id[]" style="flex:1;min-width:200px;padding:.6rem">
          <option value="">-- Pilih Obat --</option>
          <?php foreach ($obatList ?? [] as $o): ?>
          <option value="<?= $o['id'] ?>" data-nama="<?= e($o['nama_obat']) ?>"><?= e($o['nama_obat']) ?> (Stok: <?= $o['stok'] ?>)</option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="dosis[]" placeholder="Dosis (cth: 3x1)" style="width:120px;padding:.6rem">
        <input type="number" name="jumlah[]" placeholder="Jumlah" style="width:80px;padding:.6rem">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-secondary" onclick="tambahObat()">➕ Tambah Obat</button>

    <button type="submit" class="btn btn-primary" style="margin-top:1.5rem" onclick="kompilasiResep()">Simpan Rekam Medis</button>
  </form>
</div>

<script>
var obatData = [
  <?php foreach ($obatList ?? [] as $o): ?>
  {id:<?= $o['id'] ?>, nama:"<?= e($o['nama_obat']) ?>", stok:<?= $o['stok'] ?>},
  <?php endforeach; ?>
];

function tambahObat() {
  var container = document.getElementById('obat-list');
  var row = document.createElement('div');
  row.className = 'obat-row';
  row.style.cssText = 'display:flex;gap:.5rem;align-items:center;flex-wrap:wrap';
  var optHtml = '<option value="">-- Pilih Obat --</option>';
  obatData.forEach(function(o) {
    optHtml += '<option value="' + o.id + '">' + o.nama + ' (Stok: ' + o.stok + ')</option>';
  });
  row.innerHTML = 
    '<select name="obat_id[]" style="flex:1;min-width:200px;padding:.6rem">' + optHtml + '</select>' +
    '<input type="text" name="dosis[]" placeholder="Dosis" style="width:120px;padding:.6rem">' +
    '<input type="number" name="jumlah[]" placeholder="Jml" style="width:80px;padding:.6rem">' +
    '<button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>';
  container.appendChild(row);
}

function kompilasiResep() {
  var rows = document.querySelectorAll('.obat-row');
  var resepArr = [];
  rows.forEach(function(r) {
    var select = r.querySelector('select');
    var dosis = r.querySelector('input[name="dosis[]"]');
    var jumlah = r.querySelector('input[name="jumlah[]"]');
    if (select && select.value && dosis && dosis.value) {
      var nama = select.options[select.selectedIndex]?.getAttribute('data-nama') || select.options[select.selectedIndex]?.text || '';
      resepArr.push(nama.trim() + ' ' + dosis.value + (jumlah.value ? ' (' + jumlah.value + ')' : ''));
    }
  });
  document.getElementById('resep-hidden').value = resepArr.join('\n');
}
</script>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Diagnosa</th><th>Tensi</th><th>Suhu</th></tr></thead>
      <tbody>
        <?php foreach ($rekamList as $i => $r): ?>
        <tr>
          <td class="text-muted"><?= $offset + $i + 1 ?></td>
          <td class="text-muted"><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
          <td style="font-weight:600"><?= e($r['nama_lengkap']) ?> <span class="text-muted" style="font-size:.75rem">(<?= e($r['no_rm']) ?>)</span></td>
          <td><?= e($r['nama_dokter']) ?></td>
          <td><?= nl2br(e($r['diagnosa'] ?? '–')) ?></td>
          <td class="text-muted"><?= e($r['tekanan_darah'] ?? '–') ?></td>
          <td class="text-muted"><?= $r['suhu'] ? $r['suhu'].' °C' : '–' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rekamList): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-500)">Belum ada rekam medis.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>