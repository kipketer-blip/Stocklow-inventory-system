<?php
/** Customer self-registration (online shopping account). Staff accounts are created by the administrator. */
require __DIR__ . '/app/bootstrap.php';
if (Auth::check()) redirect(Auth::homeFor(Auth::role()));

$errors = [];
$old = ['full_name' => '', 'phone' => '', 'email' => ''];
if (is_post()) {
    verify_csrf();
    foreach ($old as $k => $_) $old[$k] = input($k);
    $pw = (string)($_POST['password'] ?? '');
    $errors = validate($old, ['full_name' => 'required|min:3|max:120', 'phone' => 'required|phone', 'email' => 'email|max:120']);
    $phone = normalise_phone($old['phone']);
    if (!isset($errors['phone']) && !preg_match('/^\+2547\d{8}$|^\+2541\d{8}$/', $phone)) $errors['phone'] = 'Enter a Kenyan mobile number, e.g. 0712 345 678.';
    if ($e = password_errors($pw)) $errors['password'] = $e;
    if ($pw !== (string)($_POST['confirm_password'] ?? '')) $errors['confirm'] = 'Passwords do not match.';
    if (empty($_POST['consent'])) $errors['consent'] = 'You must agree to the use of your details as described.';
    if (!$errors) {
        $existing = db_one('SELECT id, user_id FROM customers WHERE phone = ?', [$phone]);
        if ($existing && $existing['user_id']) $errors['phone'] = 'An account with this phone number already exists. Sign in instead.';
        elseif ($old['email'] !== '' && db_value('SELECT id FROM users WHERE email = ?', [$old['email']])) $errors['email'] = 'This email is already registered.';
    }
    if (!$errors) {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $username = 'C' . preg_replace('/\D/', '', $phone);
        $uid = db_insert("INSERT INTO users (role, username, email, phone, full_name, password_hash) VALUES ('customer', ?, ?, ?, ?, ?)",
            [$username, $old['email'] ?: null, $phone, $old['full_name'], password_hash($pw, PASSWORD_DEFAULT)]);
        if ($existing) {
            // A cashier already registered this shopper at the till: link the online account and keep their points.
            db_query('UPDATE customers SET user_id = ?, email = COALESCE(?, email) WHERE id = ?', [$uid, $old['email'] ?: null, $existing['id']]);
            $cid = (int)$existing['id'];
        } else {
            $cid = db_insert('INSERT INTO customers (user_id, customer_no, full_name, phone, email) VALUES (?, ?, ?, ?, ?)',
                [$uid, 'TMP' . bin2hex(random_bytes(4)), $old['full_name'], $phone, $old['email'] ?: null]);
            db_query('UPDATE customers SET customer_no = ? WHERE id = ?', [sprintf('CUS%05d', $cid), $cid]);
        }
        $pdo->commit();
        audit('customer_registered', 'customer', $cid, null, $uid);
        Auth::attempt($username, $pw);
        flash('success', 'Welcome, ' . first_name($old['full_name']) . '! Your account is ready. Start adding items to your cart.');
        redirect('customer/shop.php');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create account · OSMS</title>
  <link rel="icon" href="<?= url('assets/img/logo.svg') ?>">
  <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>?v=<?= filemtime(OSMS_ROOT . '/assets/css/app.css') ?>">
</head>
<body class="login-body d-flex align-items-center justify-content-center p-3" style="min-height:100vh">
  <div class="card shadow-sm p-4" style="width:100%;max-width:460px">
    <div class="text-center mb-3">
      <img src="<?= url('assets/img/logo.svg') ?>" width="48" height="48" alt="">
      <h1 class="h5 fw-bold text-navy mt-2 mb-0">Create a customer account</h1>
      <div class="small text-muted"><?= e(setting('store_name')) ?> · shop online, collect in store</div>
    </div>
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="mb-2"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required maxlength="120"></div>
      <div class="row g-2 mb-2">
        <div class="col-sm-6"><label class="form-label" for="phone">Mobile number</label><input class="form-control" id="phone" name="phone" value="<?= e($old['phone']) ?>" required placeholder="0712 345 678"></div>
        <div class="col-sm-6"><label class="form-label" for="email">Email (optional)</label><input class="form-control" type="email" id="email" name="email" value="<?= e($old['email']) ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <div class="col-sm-6"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" required></div>
        <div class="col-sm-6"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" type="password" id="confirm_password" name="confirm_password" required></div>
      </div>
      <div class="form-text mb-2">At least 8 characters with upper-case, lower-case letters and a number.</div>
      <div class="form-check mb-3 small">
        <input class="form-check-input" type="checkbox" id="consent" name="consent" value="1">
        <label class="form-check-label" for="consent">I agree that the supermarket may store my name, phone number and purchases to process my orders and loyalty points, as required by the Data Protection Act, 2019. My details will not be shared with third parties.</label>
      </div>
      <button class="btn btn-primary w-100">Create account</button>
    </form>
    <p class="small text-center mt-3 mb-0">Already have an account? <a href="<?= url('login.php') ?>">Sign in</a></p>
  </div>
</body>
</html>
