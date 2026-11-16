<?php
require __DIR__ . '/app/bootstrap.php';

if (Auth::check()) redirect(Auth::homeFor(Auth::role()));

$error = null;
$login = '';
if (is_post()) {
    verify_csrf();
    $login = input('username');
    $password = (string)($_POST['password'] ?? '');
    if ($login === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        // Customers may also sign in with their phone number
        if (preg_match('/^(\+?254|0)7\d{8}$/', preg_replace('/\s+/', '', $login))) {
            $u = db_value('SELECT u.username FROM customers c JOIN users u ON u.id = c.user_id WHERE c.phone = ?', [normalise_phone($login)]);
            if ($u) $login = (string)$u;
        }
        $error = Auth::attempt($login, $password);
        if ($error === null) {
            redirect(!empty($_SESSION['must_change_password']) ? 'change_password.php' : Auth::homeFor(Auth::role()));
        }
    }
}
$store = setting('store_name', 'Supermarket');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · OSMS</title>
  <link rel="icon" href="<?= url('assets/img/logo.svg') ?>">
  <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>?v=<?= filemtime(OSMS_ROOT . '/assets/css/app.css') ?>">
</head>
<body class="login-body">
<div class="container-fluid">
  <div class="row min-vh-100">
    <div class="col-lg-6 d-none d-lg-flex login-hero flex-column justify-content-between p-5">
      <div class="d-flex align-items-center gap-2"><img src="<?= url('assets/img/logo.svg') ?>" width="44" height="44" alt=""><span class="fw-bold fs-5">OSMS</span></div>
      <div>
        <h2 class="display-6 fw-bold mb-3">Every sale counted.<br>Every shelf tracked.</h2>
        <p class="lead opacity-75 mb-4">Point of sale, inventory, suppliers, customers, online orders and reports for <?= e($store) ?> in one secure system.</p>
        <div class="d-flex flex-wrap gap-3 small">
          <span><i class="bi bi-cart-check me-1"></i> Automatic totals &amp; VAT</span>
          <span><i class="bi bi-box-seam me-1"></i> Live stock levels</span>
          <span><i class="bi bi-person-check me-1"></i> Cashier accountability</span>
        </div>
      </div>
      <div class="small opacity-50">&copy; <?= date('Y') ?> <?= e($store) ?> · <?= e(setting('store_location')) ?></div>
    </div>
    <div class="col-lg-6 d-flex align-items-center justify-content-center p-3">
      <div class="card shadow-sm p-4" style="width:100%;max-width:400px">
        <div class="text-center mb-4">
          <img src="<?= url('assets/img/logo.svg') ?>" width="52" height="52" alt="">
          <h1 class="h5 fw-bold text-navy mt-2 mb-0">Supermarket Management System</h1>
          <div class="small text-muted"><?= e($store) ?></div>
        </div>
        <?php foreach (take_flashes() as $f): ?>
          <div class="alert alert-<?= e($f['type']) ?> py-2"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
        <?php if ($error): ?><div class="alert alert-danger py-2" role="alert"><?= e($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label" for="username">Username, email or phone</label>
            <input class="form-control" id="username" name="username" value="<?= e($login) ?>" required autofocus maxlength="120" placeholder="e.g. CSH001 or 0712345678">
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required maxlength="255">
          </div>
          <button class="btn btn-primary w-100" type="submit">Sign in</button>
        </form>
        <hr>
        <p class="small text-center mb-1">New customer? <a href="<?= url('register.php') ?>">Create an account</a> to shop online and earn points.</p>
        <p class="small text-muted text-center mb-0">Staff who forget their password should contact the administrator.</p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
