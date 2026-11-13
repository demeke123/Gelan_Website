<?php
require __DIR__ . '/_layout.php';
$user = require_login();
admin_check_post();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $st->execute([$user['id']]);
    $new = (string)($_POST['new'] ?? '');
    if (!password_verify((string)($_POST['current'] ?? ''), (string)$st->fetchColumn())) flash('Your current password is incorrect.', 'error');
    elseif (strlen($new) < 10) flash('Use a new password of at least 10 characters.', 'error');
    elseif ($new !== ($_POST['new2'] ?? '')) flash('The two new passwords don’t match.', 'error');
    else {
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        session_regenerate_id(true);
        flash('Your password has been changed.');
    }
    redirect('account.php');
}
admin_header('My account', $user);
?>
<p class="lead"><?= e($user['name']) ?> · <?= e($user['email']) ?></p>
<form method="post" class="form narrow">
  <?= csrf_field() ?>
  <h2>Change password</h2>
  <label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
  <label>New password <span class="hint">At least 10 characters</span><input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
  <label>Repeat new password<input type="password" name="new2" required minlength="10" autocomplete="new-password"></label>
  <div><button class="btn primary" type="submit">Change password</button></div>
</form>
<?php admin_footer();
