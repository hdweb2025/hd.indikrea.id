<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $envUser = getenv('HDI_ADMIN_USER') ?: '';
    $envPass = getenv('HDI_ADMIN_PASS') ?: '';
    if ($envUser !== '' && $envPass !== '' && hash_equals($envUser, $u) && hash_equals($envPass, $p)) {
        $_SESSION['is_admin'] = true;
        $next = $_GET['next'] ?? '/';
        header("Location: $next");
        exit;
    } else {
        $error = 'Login gagal';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 420px;">
    <h3 class="mb-3 text-center">Login Admin</h3>
    <?php if ($error) { echo '<div class="alert alert-danger">'.$error.'</div>'; } ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" autocomplete="current-password">
      </div>
      <button class="btn btn-primary w-100" type="submit">Masuk</button>
    </form>
    <div class="mt-3 text-center">
      <a href="/">Kembali</a>
    </div>
  </div>
</div>
</body>
</html>
