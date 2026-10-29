<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('admin');

$db     = getDB();
$errors = [];
$ok     = '';
$csrf   = generateCsrf();
$laporanData = null;
$laporanJudul = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $aksi     = $_POST['aksi'] ?? '';
        $tglAwal  = sanitize($_POST['tgl_awal'] ?? '');
        $tglAkhir = sanitize($_POST['tgl_akhir'] ?? '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglAwal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglAkhir)) {
            $errors[] = 'Format tanggal tidak valid.';
        } elseif (strtotime($tglAwal) > strtotime($tglAkhir)) {
            $errors[] = 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.';
        } else {
            if ($aksi === 'cetak_kunjungan') {
                $stmt = $db->prepare(
                    "SELECT a.tanggal, a.no_antrian, p.nama_lengkap, p.no_rm,
                            pl.nama_poli, d.nama AS nama_dokter, a.status, a.keluhan
                     FROM antrian a
                     JOIN pasien p ON p.id = a.pasien_id
                     JOIN poli pl ON pl.id = a.poli_id
                     JOIN dokter d ON d.id = a.dokter_id
                     WHERE a.tanggal BETWEEN ? AND ?
                     ORDER BY a.tanggal ASC, a.no_antrian ASC"
                );
                $stmt->execute([$tglAwal, $tglAkhir]);
                $laporanData = $stmt->fetchAll();
                $laporanJudul = 'Laporan Kunjungan Pasien';
            } elseif ($aksi === 'cetak_rekam') {
                $stmt = $db->prepare(
                    "SELECT r.tanggal, p.nama_lengkap, p.no_rm, d.nama AS nama_dokter,
                            r.diagnosa, r.tindakan, r.resep
                     FROM rekam_medis r
                     JOIN pasien p ON p.id = r.pasien_id
                     JOIN dokter d ON d.id = r.dokter_id
                     WHERE r.tanggal BETWEEN ? AND ?
                     ORDER BY r.tanggal DESC"
                );
                $stmt->execute([$tglAwal, $tglAkhir]);
                $laporanData = $stmt->fetchAll();
                $laporanJudul = 'Laporan Rekam Medis';
            } elseif ($aksi === 'cetak_obat') {
                $stmt = $db->prepare(
                    "SELECT o.nama_obat, o.kategori, o.satuan, o.stok, o.harga, o.keterangan
                     FROM obat o
                     WHERE EXISTS (
                       SELECT 1 FROM rekam_medis r
                       WHERE r.resep IS NOT NULL AND TRIM(r.resep) <> ''
                         AND (r.resep LIKE CONCAT(o.nama_obat, ' %') OR r.resep = o.nama_obat)
                     )
                     ORDER BY o.nama_obat ASC"
                );
                $stmt->execute();
                $laporanData = $stmt->fetchAll();
                $laporanJudul = 'Laporan Stok Obat Terpakai';
            }

            if (empty($errors) && empty($laporanData)) {
                $errors[] = 'Tidak ada data untuk periode tersebut.';
            }
        }
    }
}

$pageTitle = 'Cetak Laporan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> – <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
<style>
/* Print-only: clean layout + watermark */
@media print {
  body { margin: 0; padding: 0; background: white; }
  .no-print { display: none !important; }
  .watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 4rem;
    font-weight: 900;
    color: rgba(0,0,0,0.08);
    white-space: nowrap;
    pointer-events: none;
    z-index: 9999;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    letter-spacing: 0.1em;
    display: block !important;
  }
  @page { margin: 1.5cm; }
}
.watermark { display: none; }

/* Report layout */
.laporan-wrap {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 2rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
}
.laporan-header {
  text-align: center;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--border);
  margin-bottom: 1.5rem;
}
.laporan-header h2 {
  font-family: var(--font-display);
  font-size: 1.4rem;
  font-weight: 800;
}
.laporan-header .meta {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 0.3rem;
}
.laporan-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.laporan-table th,
.laporan-table td {
  padding: 0.55rem 0.8rem;
  border: 1px solid var(--border);
  text-align: left;
}
.laporan-table th {
  background: var(--bg-body);
  font-weight: 700;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.laporan-table tbody tr:nth-child(even) { background: var(--bg-body); }
</style>
</head>
<body>

<!-- WATERMARK -->
<div class="watermark">Klinik Medika Utama</div>

<?php if ($laporanData === null): ?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-body">
  <?php if ($errors): ?><div class="alert alert-danger"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <div class="card">
    <h3 style="margin-bottom:1rem">📊 Unduh Laporan</h3>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end">
        <div class="form-group" style="margin:0">
          <label>Tanggal Awal</label>
          <input type="date" name="tgl_awal" value="<?= e(date('Y-m-d', strtotime('-30 days'))) ?>" required>
        </div>
        <div class="form-group" style="margin:0">
          <label>Tanggal Akhir</label>
          <input type="date" name="tgl_akhir" value="<?= e(date('Y-m-d')) ?>" required>
        </div>
        <button type="submit" name="aksi" value="cetak_kunjungan" class="btn btn-primary">📋 Laporan Kunjungan</button>
        <button type="submit" name="aksi" value="cetak_rekam" class="btn btn-primary">📄 Laporan Rekam Medis</button>
        <button type="submit" name="aksi" value="cetak_obat" class="btn btn-primary">💊 Laporan Stok Obat</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php else: ?>

<div class="laporan-wrap">
  <div class="laporan-header">
    <h2><?= e($laporanJudul) ?></h2>
    <div class="meta">Klinik Medika Utama</div>
    <?php if (in_array($_POST['aksi'] ?? '', ['cetak_kunjungan', 'cetak_rekam'])): ?>
    <div class="meta">Periode: <?= e($_POST['tgl_awal']) ?> s/d <?= e($_POST['tgl_akhir']) ?></div>
    <?php endif; ?>
    <div class="meta">Dicetak: <?= date('d/m/Y H:i') ?></div>
  </div>

  <table class="laporan-table">
    <thead>
      <tr>
        <?php foreach (array_keys($laporanData[0]) as $col): ?>
        <th><?= e(ucwords(str_replace('_', ' ', $col))) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($laporanData as $row): ?>
      <tr>
        <?php foreach ($row as $cell): ?>
        <td><?= nl2br(e($cell ?? '–')) ?></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="no-print" style="text-align:center;padding:1rem">
  <button class="btn btn-secondary" onclick="window.history.back()">← Kembali</button>
  <button class="btn btn-primary" onclick="window.print()">🖨 Cetak Lagi</button>
</div>

<script>
window.print();
</script>

<?php endif; ?>

</body>
</html>