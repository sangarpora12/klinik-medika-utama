<?php
// config.php – Klinik Medika Utama
define('APP_NAME', 'Klinik Medika Utama');
define('APP_TAGLINE', 'Sistem Informasi Manajemen Klinik');
define('APP_VERSION', '1.0.0');

define('DB_HOST', 'localhost');
define('DB_NAME', 'klinik_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', 'http://localhost:8000');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

session_start();

function getDB(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function e($v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function sanitize($v): string { return trim(strip_tags($v)); }

function startSecureSession(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCsrf(): string { return $_SESSION['csrf_token'] ?? ''; }

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function requireRole(string $role): void {
    if (empty($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }

function auditLog(string $aksi, string $detail = '', ?int $userId = null): void {
    $userId = $userId ?? $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $db = getDB();
    $db->prepare(
        "INSERT INTO audit_log (user_id, aksi, detail, ip_address, user_agent)
         VALUES (?,?,?,?,?)"
    )->execute([$userId, $aksi, $detail, $ip, $ua]);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 5);
define('CAPTCHA_SESSION_KEY', 'captcha_answer');