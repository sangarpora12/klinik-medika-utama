<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();
requireRole('user');

$db = getDB();
$userId = $_SESSION['user_id'];
$errors = [];
$ok = '';
$csrf = generateCsrf();

$pasienStmt = $db->prepare("SELECT * FROM pasien WHERE user_id=? LIMIT 1");
$pasienStmt->execute([$userId]);
$pasien = $pasienStmt->fetch();

if (!$pasien) {
    $defaults = ['nama_lengkap'=>'', 'nik'=>'', 'tanggal_lahir'=>'', 'jenis_kelamin'=>'',
                 'gol_darah'=>'tidak_diketahui', 'alamat'=>'', 'telepon'=>'', 'email'=>'', 'alergi'=>''];
} else {
    $defaults = $pasien;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $nama  = sanitize($_POST['nama_lengkap'] ?? '');
        $nik   = sanitize($_POST['nik'] ?? '');
        $tgl   = sanitize($_POST['tanggal_lahir'] ?? '');
        $jk    = sanitize($_POST['jenis_kelamin'] ?? '');
        $gold  = sanitize($_POST['gol_darah'] ?? 'tidak_diketahui');
        $alm   = sanitize($_POST['alamat'] ?? '');
        $tel   = sanitize($_POST['telepon'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $alergi = sanitize($_POST['alergi'] ?? '');

        if ($nama === '' || mb_strlen($nama) < 3) $errors[] = 'Nama lengkap wajib diisi (min 3 karakter).';
        if (!in_array($jk, ['L','P'], true)) $errors[] = 'Jenis kelamin tidak valid.';

        if (empty($errors)) {
            if ($pasien) {
                $stmt = $db->prepare(
                    "UPDATE pasien SET nama_lengkap=?, nik=?, tanggal_lahir=?, jenis_kelamin=?,
                     gol_darah=?, alamat=?, telepon=?, email=?, alergi=? WHERE id=?"
                );
                $stmt->execute([$nama, $nik ?: null, $tgl ?: null, $jk, $gold, $alm ?: null, $tel ?: null, $email ?: null, $alergi ?: null, $pasien['id']]);
                $ok = 'Profil berhasil diperbarui.';
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO pasien (user_id, no_rm, nama_lengkap, nik, tanggal_lahir, jenis_kelamin, gol_darah, alamat, telepon, email, alergi)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?)"
                );
                $year = date('Y');
                $last = $db->query("SELECT no_rm FROM pasien WHERE no_rm LIKE 'RM-{$year}-%' ORDER BY id DESC LIMIT 1")->fetchColumn();
                $next = $last ? ((int)substr($last, -4) + 1) : 1;
                $noRm = sprintf('RM-%s-%04d', $year, $next);
                $stmt->execute([$userId, $noRm, $nama, $nik ?: null, $tgl ?: null, $jk, $gold, $alm ?: null, $tel ?: null, $email ?: null, $alergi ?: null]);
                $ok = 'Profil awal berhasil disimpan.';
            }
        }
    }
}

$pageTitle = 'Profil Saya';
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

<div class="card">
  <h3 style="margin-bottom:1rem">👤 Data Pribadi</h3>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
      <div class="form-group">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" required value="<?= e($defaults['nama_lengkap'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>NIK (16 digit)</label>
        <input type="text" name="nik" maxlength="16" value="<?= e($defaults['nik'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?= e($defaults['tanggal_lahir'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Jenis Kelamin *</label>
        <select name="jenis_kelamin" required>
          <option value="">— Pilih —</option>
          <option value="L" <?= (($defaults['jenis_kelamin'] ?? '')==='L')?'selected':'' ?>>Laki-laki</option>
          <option value="P" <?= (($defaults['jenis_kelamin'] ?? '')==='P')?'selected':'' ?>>Perempuan</option>
        </select>
      </div>
      <div class="form-group">
        <label>Golongan Darah</label>
        <select name="gol_darah">
          <?php foreach (['tidak_diketahui'=>'Tidak diketahui','A'=>'A','B'=>'B','AB'=>'AB','O'=>'O'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= (($defaults['gol_darah'] ?? 'tidak_diketahui')===$v)?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Telepon</label>
        <input type="text" name="telepon" value="<?= e($defaults['telepon'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($defaults['email'] ?? '') ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= e($defaults['alamat'] ?? '') ?>">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Alergi</label>
        <input type="text" name="alergi" value="<?= e($defaults['alergi'] ?? '') ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:1rem">Simpan Profil</button>
  </form>
</div>

</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>