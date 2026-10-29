<?php
require_once __DIR__ . '/../includes/config.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$errors = [];
$values = ['nama_lengkap'=>'','username'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    } else {
        $nama  = sanitize($_POST['nama_lengkap'] ?? '');
        $uname = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['confirm_password'] ?? '';
        $values = compact('nama','uname','email');

        if (strlen($nama) < 3) $errors[] = 'Nama lengkap minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $uname)) $errors[] = 'Username 4-30 karakter, huruf, angka, underscore.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if (strlen($pass) < 8) $errors[] = 'Password minimal 8 karakter.';
        elseif (!preg_match('/[A-Z]/', $pass)) $errors[] = 'Password harus mengandung huruf kapital.';
        elseif (!preg_match('/[0-9]/', $pass)) $errors[] = 'Password harus mengandung angka.';
        elseif (!preg_match('/[^a-zA-Z0-9]/', $pass)) $errors[] = 'Password harus mengandung karakter spesial.';
        if ($pass !== $pass2) $errors[] = 'Konfirmasi password tidak cocok.';

        if (empty($errors)) {
            $db = getDB();
            $chk = $db->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
            $chk->execute([$uname, $email]);
            if ($chk->fetch()) $errors[] = 'Username atau email sudah terdaftar.';
        }

        if (empty($errors)) {
            $db     = getDB();
            $hash   = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $token  = bin2hex(random_bytes(32));
            $ins = $db->prepare("INSERT INTO users (role_id, nama_lengkap, username, email, password_hash, status, token_verifikasi) VALUES (3, ?, ?, ?, ?, 'belum_verifikasi', ?)");
            $ins->execute([$nama, $uname, $email, $hash, $token]);
            auditLog('register', "User baru: $uname", null);
            $vlink = BASE_URL . "/auth/verify.php?token=$token";
            $_SESSION['reg_success'] = true;
            $_SESSION['reg_vlink']   = $vlink;
            header('Location: ' . BASE_URL . '/auth/register.php?success=1'); exit;
        }
    }
}
$csrf = generateCsrf();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body class="login-body">
<div class="login-left">
  <div class="login-left-inner">
    <div class="login-logo">
      <div class="login-logo-icon">🏥</div>
      <div>
        <span class="login-logo-text"><?= e(APP_NAME) ?></span>
        <span class="login-logo-tagline"><?= e(APP_TAGLINE) ?></span>
      </div>
    </div>
    <h1 class="login-title">Buat Akun Baru</h1>
    <p class="login-subtitle">Daftar sebagai pasien di klinik kami</p>

    <div class="auth-card" style="border-radius:16px;padding:1.75rem 2rem;box-shadow:0 4px 24px rgba(0,0,0,.06);">
      <?php if (isset($_GET['success']) && !empty($_SESSION['reg_success'])): ?>
      <?php unset($_SESSION['reg_success']); $vlink = $_SESSION['reg_vlink'] ?? ''; unset($_SESSION['reg_vlink']); ?>
      <div class="alert alert-success">
        <strong>Pendaftaran berhasil!</strong> Akun Anda perlu diverifikasi sebelum dapat login.<br>
        <?php if ($vlink): ?><small><em>Demo:</em> <a href="<?= e($vlink) ?>">Klik di sini untuk verifikasi akun</a></small><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="form-group">
          <label for="nama_lengkap">Nama Lengkap</label>
          <input id="nama_lengkap" type="text" name="nama_lengkap" value="<?= e($values['nama_lengkap'] ?? '') ?>" placeholder="Masukkan nama lengkap" required autocomplete="name">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="<?= e($values['uname'] ?? '') ?>" placeholder="nama_pengguna" required autocomplete="username">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($values['email'] ?? '') ?>" placeholder="email@domain.com" required autocomplete="email">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-icon">
              <input id="password" type="password" name="password" placeholder="Min. 8 karakter" required autocomplete="new-password">
              <button type="button" class="toggle-pw" data-target="password" tabindex="-1">👁</button>
            </div>
            <div style="font-size:.72rem;color:var(--text-light);margin-top:.2rem;">Huruf kapital, angka, karakter spesial</div>
          </div>
          <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <div class="input-icon">
              <input id="confirm_password" type="password" name="confirm_password" placeholder="Ulangi password" required autocomplete="new-password">
              <button type="button" class="toggle-pw" data-target="confirm_password" tabindex="-1">👁</button>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
      </form>
      <p style="text-align:center;margin-top:1.5rem;font-size:.85rem;color:var(--text-muted);">Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--primary);font-weight:600;text-decoration:none;">Masuk di sini</a></p>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.toggle-pw').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = document.getElementById(btn.dataset.target);
    inp.type = inp.type === 'password' ? 'text' : 'password';
  });
});
</script>
</body>
</html>