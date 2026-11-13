<?php
require __DIR__ . '/_layout.php';
if (!is_installed()) redirect('setup.php');
if (current_user()) redirect('index.php');

$error = '';
$email = trim($_POST['email'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'The form expired. Please try again.';
    } elseif (login_blocked()) {
        $error = 'Too many failed sign-in attempts. Wait 15 minutes and try again.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE email = ? AND active = 1');
        $st->execute([strtolower($email)]);
        $u = $st->fetch();
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $u['id']]);
            }
            db()->prepare('UPDATE users SET last_login = ? WHERE id = ?')->execute([date('Y-m-d H:i:s'), $u['id']]);
            redirect('index.php');
        }
        login_failed();
        $error = 'The email or password is incorrect.';
    }
}
admin_header('Staff sign in');
?>
<?php if ($error): ?><p class="alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="form narrow">
  <?= csrf_field() ?>
  <label>Email<input name="email" type="email" required value="<?= e($email) ?>" autocomplete="username" autofocus></label>
  <label>Password<input name="password" type="password" required autocomplete="current-password"></label>
  <div><button class="btn primary" type="submit">Sign in</button></div>
</form>
<p class="status">Forgot your password? Ask the website administrator to set a new one.</p>
<?php admin_footer();
