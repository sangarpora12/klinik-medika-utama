<?php
// auth/login.php – Login Klinik Medika Utama
require_once __DIR__ . '/../includes/config.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$errors  = [];
$warning = '';

if (isset($_GET['timeout'])) {
    $warning = 'Sesi Anda telah habis. Silakan login kembali.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $loginStr = sanitize($_POST['login'] ?? '');
        $pass     = $_POST['password'] ?? '';
        $captcha  = trim($_POST['captcha'] ?? '');

        if (empty($loginStr) || empty($pass)) {
            $errors[] = 'Username/email dan password wajib diisi.';
        }

        $captchaAnswer = $_SESSION[CAPTCHA_SESSION_KEY] ?? null;
        unset($_SESSION[CAPTCHA_SESSION_KEY]);

        if (!is_numeric($captcha) || (int)$captcha !== (int)$captchaAnswer) {
            $errors[] = 'Jawaban CAPTCHA salah. Silakan coba lagi.';
        }

        if (empty($errors)) {
            $db   = getDB();
            $stmt = $db->prepare(
                "SELECT u.*, r.nama_role FROM users u
                 JOIN roles r ON r.id=u.role_id
                 WHERE (u.username=? OR u.email=?) LIMIT 1"
            );
            $stmt->execute([$loginStr, $loginStr]);
            $user = $stmt->fetch();

            if ($user && $user['blokir_hingga'] && strtotime($user['blokir_hingga']) > time()) {
                $sisaMenit = ceil((strtotime($user['blokir_hingga']) - time()) / 60);
                $errors[]  = "Akun diblokir sementara. Coba lagi dalam {$sisaMenit} menit.";
                auditLog('login_blocked', "Coba login saat diblokir: {$loginStr}", $user['id'] ?? null);
            } elseif ($user && $user['status'] !== 'aktif') {
                $statusLabel = [
                    'belum_verifikasi' => 'belum diverifikasi',
                    'nonaktif'         => 'dinonaktifkan oleh administrator',
                ];
                $errors[] = 'Akun Anda ' . ($statusLabel[$user['status']] ?? 'tidak aktif') . '.';
            } elseif ($user && password_verify($pass, $user['password_hash'])) {
                $db->prepare("UPDATE users SET gagal_login=0, blokir_hingga=NULL, last_login=NOW() WHERE id=?")
                   ->execute([$user['id']]);

                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['role']     = $user['nama_role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama_lengkap'];

                if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost'=>12])) {
                    $newHash = password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]);
                    $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$newHash, $user['id']]);
                }

                auditLog('login_sukses', "Login berhasil: {$user['username']}", $user['id']);

                $redirect = [
                    'admin'    => BASE_URL . '/admin/',
                    'operator' => BASE_URL . '/operator/',
                    'user'     => BASE_URL . '/user/',
                ];
                header('Location: ' . ($redirect[$user['nama_role']] ?? BASE_URL . '/user/'));
                exit;
            } else {
                if ($user) {
                    $gagal = (int)$user['gagal_login'] + 1;
                    if ($gagal >= MAX_LOGIN_ATTEMPTS) {
                        $blokir = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'));
                        $db->prepare("UPDATE users SET gagal_login=?, blokir_hingga=? WHERE id=?")
                           ->execute([$gagal, $blokir, $user['id']]);
                        $errors[] = 'Terlalu banyak percobaan login. Akun diblokir selama ' . LOCKOUT_MINUTES . ' menit.';
                    } else {
                        $sisa = MAX_LOGIN_ATTEMPTS - $gagal;
                        $db->prepare("UPDATE users SET gagal_login=? WHERE id=?")->execute([$gagal, $user['id']]);
                        $errors[] = "Username/email atau password salah. Sisa percobaan: {$sisa}x.";
                    }
                    auditLog('login_gagal', "Gagal login: {$loginStr} (ke-{$user['gagal_login']})", $user['id']);
                } else {
                    $errors[] = 'Username/email atau password salah.';
                    auditLog('login_gagal', "Username tidak ditemukan: {$loginStr}");
                }
            }
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
<title>Masuk — <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body class="login-body">
<div class="login-left" style="flex:none;width:100%;max-width:520px;margin:0 auto;padding:2.5rem 1.5rem">
  <div class="login-left-inner">
    <div class="login-logo">
      <div class="login-logo-icon">🏥</div>
      <div>
        <span class="login-logo-text"><?= e(APP_NAME) ?></span>
        <span class="login-logo-tagline"><?= e(APP_TAGLINE) ?></span>
      </div>
    </div>

    <h1 class="login-title">Selamat Datang</h1>
    <p class="login-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

    <div class="login-card">
    <?php if ($warning): ?>
    <div class="alert alert-warning"><?= e($warning) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
      <div class="form-group">
        <label for="login">Username atau Email</label>
        <input id="login" type="text" name="login" placeholder="Masukkan username atau email" required autocomplete="username" autofocus>
      </div>
      <div class="form-group">
        <label for="password">Kata Sandi</label>
        <input id="password" type="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label>Verifikasi Keamanan</label>
        <div class="captcha-row">
          <img id="captcha-img" src="<?= BASE_URL ?>/auth/captcha.php?<?= time() ?>" alt="CAPTCHA" class="captcha-img">
          <button type="button" class="btn-icon" onclick="reloadCaptcha()" title="Muat Ulang">↻</button>
          <input type="number" name="captcha" id="captcha" placeholder="Jawaban" required autocomplete="off" class="captcha-input">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>

    <p style="text-align:center;margin-top:1.5rem;font-size:.85rem;color:var(--text-muted);">
      Belum punya akun? <a href="<?= BASE_URL ?>/auth/register.php" style="color:var(--primary);font-weight:600;text-decoration:none;">Daftar di sini</a>
    </p>
    </div>

    <div style="margin-top:1rem;padding:.6rem .85rem;background:var(--primary-soft);border-radius:10px;border:1px solid var(--primary-light);font-size:.78rem;color:var(--text-muted);text-align:center">
      <strong style="color:var(--primary)">🔐 Akun Demo:</strong><br>
      <span><strong>Admin</strong>: admin / Admin@1234</span><br>
      <span><strong>Operator</strong>: operator / Admin@1234</span><br>
      <span><strong>Pasien</strong>: pasien / Admin@1234</span>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>