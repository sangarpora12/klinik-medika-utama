<?php
// index.php – Redirect ke dashboard sesuai role
require_once __DIR__ . '/includes/config.php';
startSecureSession();

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php'); exit;
}

$role = $_SESSION['role'] ?? 'user';
$map  = [
    'admin'    => BASE_URL . '/admin/',
    'operator' => BASE_URL . '/operator/',
    'user'     => BASE_URL . '/user/',
];
header('Location: ' . ($map[$role] ?? BASE_URL . '/user/')); exit;
