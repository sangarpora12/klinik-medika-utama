<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('user');

$db = getDB();
$userId = $_SESSION['user_id'];

$pasien = $db->prepare("SELECT * FROM pasien WHERE user_id=? LIMIT 1");
$pasien->execute([$userId]);
$pasienData = $pasien->fetch();

if (!$pasienData) {
    header('Location: ' . BASE_URL . '/user/profil.php?tab=data_diri');
    exit;
}

$pageTitle = 'Dasbor Pasien';
$ok = '';
$errors = [];

// Ambil antrian terbaru pasien untuk hari ini
$today = date('Y-m-d');
$antrianSaya = $db->prepare("
  SELECT a.*, d.nama AS nama_dokter, pl.nama_poli
  FROM antrian a
  JOIN dokter d ON d.id = a.dokter_id
  JOIN poli pl ON pl.id = a.poli_id
  WHERE a.pasien_id=? AND a.tanggal=?
  ORDER BY a.no_antrian DESC LIMIT 1
");
$antrianSaya->execute([$pasienData['id'], $today]);
$antrianSayaData = $antrianSaya->fetch();

// Ambil antrian yang sedang dipanggil
$antrianDipanggil = $db->prepare("
  SELECT a.*, d.nama AS nama_dokter, pl.nama_poli
  FROM antrian a
  JOIN dokter d ON d.id = a.dokter_id
  JOIN poli pl ON pl.id = a.poli_id
  WHERE a.tanggal=? AND a.status='dipanggil'
  ORDER BY a.jam_panggil DESC LIMIT 1
");
$antrianDipanggil->execute([$today]);
$antrianDipanggilData = $antrianDipanggil->fetch();

// Cek apakah pasien sudah daftar antrian hari ini
$sudahAntriHariIni = $db->prepare("SELECT COUNT(*) FROM antrian WHERE pasien_id=? AND tanggal=?");
$sudahAntriHariIni->execute([$pasienData['id'], $today]);
$sudahAntri = (int)$sudahAntriHariIni->fetchColumn() > 0;

// Hitung estimasi waktu tunggu (jumlah antrian di depan * 5 menit)
$estimasiMenit = 0;
if ($antrianSayaData && $antrianSayaData['status'] === 'menunggu') {
  $diDepan = $db->prepare("SELECT COUNT(*) FROM antrian WHERE dokter_id=? AND tanggal=? AND no_antrian < ? AND status IN ('menunggu','dipanggil')");
  $diDepan->execute([$antrianSayaData['dokter_id'], $today, $antrianSayaData['no_antrian']]);
  $estimasiMenit = ((int)$diDepan->fetchColumn()) * 5;
}

// Riwayat kunjungan (5 terakhir)
$riwayat = $db->prepare("
  SELECT a.tanggal, a.keluhan, a.status, r.resep, r.diagnosa, r.tindakan
  FROM antrian a
  LEFT JOIN rekam_medis r ON r.antrian_id = a.id
  WHERE a.pasien_id=? AND a.status IN ('selesai','batal')
  ORDER BY a.tanggal DESC LIMIT 5
");
$riwayat->execute([$pasienData['id']]);
$riwayatList = $riwayat->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
<style>
.status-hero {
  background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
  border-radius: 20px;
  padding: 2rem;
  color: #fff;
  margin-bottom: 1.5rem;
  box-shadow: 0 12px 32px rgba(37,99,235,.3);
  position: relative;
  overflow: hidden;
}
.status-hero::before {
  content: '';
  position: absolute;
  top: -30%;
  right: -10%;
  width: 200px;
  height: 200px;
  background: rgba(255,255,255,.08);
  border-radius: 50%;
  pointer-events: none;
}
.status-hero::after {
  content: '';
  position: absolute;
  bottom: -40%;
  left: -5%;
  width: 160px;
  height: 160px;
  background: rgba(255,255,255,.06);
  border-radius: 50%;
  pointer-events: none;
}
.status-hero h2 {
  margin: 0 0 .75rem;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.status-hero .meta {
  font-size: .9rem;
  opacity: .9;
  margin-bottom: .3rem;
}
.status-box {
  background: rgba(255,255,255,.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 14px;
  padding: 1rem 1.25rem;
  margin-top: 1rem;
}
.status-box .label {
  font-size: .8rem;
  opacity: .8;
  margin-bottom: .2rem;
}
.status-box .value {
  font-size: 1.8rem;
  font-weight: 900;
  font-family: var(--font-display);
  line-height: 1;
}
.status-box .sub {
  font-size: .82rem;
  opacity: .85;
  margin-top: .2rem;
}
.btn-antri {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .75rem 1.5rem;
  border-radius: 12px;
  font-size: .95rem;
  font-weight: 700;
  font-family: inherit;
  color: #fff;
  background: rgba(239,68,68,.85);
  border: 1px solid rgba(255,255,255,.25);
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(239,68,68,.3);
  transition: all .2s;
  margin-top: 1rem;
}
.btn-antri:hover {
  background: rgba(239,68,68,1);
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(239,68,68,.4);
}
.btn-antri:disabled, .btn-antri.disabled {
  opacity: .5;
  cursor: not-allowed;
  pointer-events: none;
  background: rgba(255,255,255,.15);
  box-shadow: none;
}
.riwayat-section {
  margin-top: 1.5rem;
}
.riwayat-section h3 {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.1rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.badge-resep {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .35rem .7rem;
  border-radius: 8px;
  font-size: .75rem;
  font-weight: 700;
  background: #f0fdf4;
  color: #166534;
  text-decoration: none;
  border: 1px solid #bbf7d0;
}
.badge-resep:hover {
  background: #dcfce7;
}
@media (max-width: 768px) {
  .status-hero { padding: 1.25rem; }
  .status-hero h2 { font-size: 1.2rem; }
  .status-box .value { font-size: 1.4rem; }
}
</style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="page-body">

<?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="status-hero">
  <h2>📋 Status Antrean Hari Ini</h2>
  <div class="meta">Tanggal: <?= date('d F Y', strtotime($today)) ?></div>

  <?php if ($antrianDipanggilData): ?>
  <div class="status-box">
    <div class="label">🔔 Antrean Saat Ini di Klinik</div>
    <div class="value">A-<?= str_pad($antrianDipanggilData['no_antrian'], 3, '0', STR_PAD_LEFT) ?></div>
    <div class="sub"><?= e($antrianDipanggilData['nama_dokter']) ?> — <?= e($antrianDipanggilData['nama_poli']) ?></div>
  </div>
  <?php endif; ?>

  <?php if ($antrianSayaData): ?>
  <div class="status-box" style="background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.35)">
    <div class="label">🎫 Nomor Antrean Anda</div>
    <div class="value">A-<?= str_pad($antrianSayaData['no_antrian'], 3, '0', STR_PAD_LEFT) ?></div>
    <div class="sub">
      <?php if ($antrianSayaData['status'] === 'menunggu'): ?>
        Estimasi waktu tunggu: <strong><?= $estimasiMenit ?> menit</strong>
      <?php elseif ($antrianSayaData['status'] === 'dipanggil'): ?>
        Sedang dilayani di Ruang <?= e($antrianSayaData['nama_poli']) ?>
      <?php elseif ($antrianSayaData['status'] === 'selesai'): ?>
        Kunjungan selesai
      <?php else: ?>
        Antrean dibatalkan
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!$sudahAntri): ?>
    <a href="<?= BASE_URL ?>/user/antrian.php" class="btn-antri">
      ➕ Ambil Antrean Baru
    </a>
  <?php else: ?>
    <button class="btn-antri disabled" disabled>✓ Sudah Mendaftar Antrean</button>
  <?php endif; ?>
</div>

<div class="riwayat-section">
  <h3>📋 Riwayat Kunjungan</h3>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Tanggal Berobat</th>
            <th>Keluhan Singkat</th>
            <th>Status</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($riwayatList as $r): ?>
          <tr style="cursor:pointer" onclick="toggleDetail(this, 'detail-<?= md5($r['tanggal'].($r['keluhan']??'')) ?>')">
            <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
            <td><?= e(mb_substr($r['keluhan'] ?? 'Tanpa keluhan', 0, 50)) ?></td>
            <td>
              <span class="badge badge-<?= $r['status']==='selesai' ? 'success' : 'danger' ?>">
                <?= ucfirst($r['status']) ?>
              </span>
            </td>
            <td>
              <span style="color:var(--primary);font-weight:600;font-size:.82rem">
                <?= $r['status']==='selesai' ? 'Lihat Detail' : 'Dibatalkan' ?>
              </span>
            </td>
          </tr>
          <tr id="detail-<?= md5($r['tanggal'].($r['keluhan']??'')) ?>" style="display:none;background:var(--bg-body)">
            <td colspan="4" style="padding:1rem 1.25rem">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.85rem">
                <div><strong>Diagnosa:</strong> <?= nl2br(e($r['diagnosa'] ?? '–')) ?></div>
                <div><strong>Tindakan:</strong> <?= nl2br(e($r['tindakan'] ?? '–')) ?></div>
                <div style="grid-column:1/-1"><strong>Resep:</strong> <?= nl2br(e($r['resep'] ?? 'Tidak ada resep')) ?></div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($riwayatList)) : ?>
          <tr>
            <td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted)">
              Belum ada riwayat kunjungan.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function toggleDetail(row, detailId) {
  var detail = document.getElementById(detailId);
  if (detail) {
    detail.style.display = detail.style.display === 'none' ? 'table-row' : 'none';
  }
}
</script>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>