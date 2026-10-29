<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 – Access Denied | <?= defined('APP_NAME') ? APP_NAME : 'MediCare Pro' ?></title>
<link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/css/main.css">
</head>
<body class="auth-body">
<div class="auth-wrap" style="max-width:420px">
  <div class="auth-card" style="text-align:center;padding:3rem">
    <div style="font-size:3.5rem;margin-bottom:.5rem">🚫</div>
    <h2 style="color:var(--danger);margin:.5rem 0">Access Denied</h2>
    <p style="color:var(--gray-500)">You don't have permission to access this page.</p>
    <a href="javascript:history.back()" class="btn btn-secondary" style="margin-top:1.25rem">← Go Back</a>
  </div>
</div>
</body>
</html>