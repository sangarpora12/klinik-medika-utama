<?php
// auth/verify.php – Verifikasi akun via token
require_once __DIR__ . '/../includes/config.php';
startSecureSession();

$token  = sanitize($_GET['token'] ?? '');
$status = 'invalid';

if (strlen($token) === 64) {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id, nama_lengkap, status FROM users
         WHERE token_verifikasi = ? AND status = 'belum_verifikasi' LIMIT 1"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $upd = $db->prepare(
            "UPDATE users SET status='aktif', token_verifikasi=NULL WHERE id=?"
        );
        $upd->execute([$user['id']]);
        auditLog('verify_email', 'Verifikasi email berhasil', $user['id']);
        $status = 'ok';
        $nama   = $user['nama_lengkap'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verifikasi Akun – <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
<meta http-equiv="refresh" content="4;url=<?= BASE_URL ?>/auth/login.php">
</head>
<body class="auth-body">
<div class="auth-wrap">
  <div class="auth-card" style="text-align:center;padding:3rem">
    <?php if ($status === 'ok'): ?>
      <div style="font-size:3rem">✅</div>
      <h2 style="color:#1a6b5a;margin:.5rem 0">Verifikasi Berhasil!</h2>
      <p>Halo, <strong><?= e($nama) ?></strong>! Akun Anda telah aktif.</p>
      <p style="color:#888;font-size:.9rem">Mengalihkan ke halaman login…</p>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary" style="margin-top:1rem">Login Sekarang</a>
    <?php else: ?>
      <div style="font-size:3rem">❌</div>
      <h2 style="color:#e74c3c;margin:.5rem 0">Token Tidak Valid</h2>
      <p>Link verifikasi tidak ditemukan atau sudah digunakan.</p>
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary" style="margin-top:1rem">Kembali Daftar</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
