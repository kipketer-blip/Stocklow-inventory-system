<?php
require __DIR__ . '/app/bootstrap.php';
require_role('admin', 'cashier', 'customer');

$u = db_one('SELECT * FROM users WHERE id = ?', [Auth::id()]);
if (is_post()) {
    verify_csrf();
    $d = ['full_name' => input('full_name'), 'email' => input('email')];
    $err = validate($d, ['full_name' => 'required|min:3|max:120', 'email' => 'email|max:120']);
    if ($err) {
        flash('danger', implode(' ', $err));
    } else {
        try {
            db_query('UPDATE users SET full_name = ?, email = ? WHERE id = ?', [$d['full_name'], $d['email'] ?: null, Auth::id()]);
            if (Auth::role() === 'customer') db_query('UPDATE customers SET full_name = ?, email = ? WHERE user_id = ?', [$d['full_name'], $d['email'] ?: null, Auth::id()]);
            $_SESSION['name'] = $d['full_name'];
            audit('profile_updated', 'user', Auth::id());
            flash('success', 'Profile updated.');
        } catch (PDOException $e) {
            if (!is_duplicate_error($e)) throw $e;
            flash('danger', 'That email is already used by another account.');
        }
    }
    redirect('profile.php');
}
$pageTitle = 'My Profile';
$active = '';
require OSMS_ROOT . '/app/layout/header.php';
?>
<div class="row justify-content-center"><div class="col-md-8 col-lg-6"><div class="card">
  <div class="card-header"><span><i class="bi bi-person"></i> My profile</span></div>
  <div class="card-body">
    <form method="post"><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Username</label><input class="form-control" value="<?= e($u['username']) ?>" disabled></div>
      <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" value="<?= e($u['phone']) ?>" disabled><div class="form-text">Ask the administrator to change your phone number.</div></div>
      <div class="mb-3"><label class="form-label">Full name</label><input name="full_name" class="form-control" value="<?= e($u['full_name']) ?>" required maxlength="120"></div>
      <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= e($u['email']) ?>" maxlength="120"></div>
      <div class="d-flex gap-2"><button class="btn btn-primary">Save</button><a class="btn btn-outline-secondary" href="<?= url('change_password.php') ?>">Change password</a></div>
    </form>
  </div>
</div></div></div>
<?php require OSMS_ROOT . '/app/layout/footer.php'; ?>
