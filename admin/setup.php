<?php
/**
 * First-time installation: creates the database tables and the first administrator.
 * Locks itself automatically once an administrator exists.
 */
require __DIR__ . '/_layout.php';

$error = '';
try {
    db_install();
} catch (Throwable $e) {
    $error = 'Could not connect to the database. Check the settings in config.php.' . (DEBUG ? ' (' . $e->getMessage() . ')' : '');
}

if (!$error && is_installed()) {
    flash('The website is already installed. Sign in below.');
    redirect('login.php');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = (string)($_POST['password'] ?? '');
    if (!csrf_ok()) $error = 'The form expired. Please try again.';
    elseif ($name === '') $error = 'Enter your name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($pass) < 10) $error = 'Use a password of at least 10 characters.';
    elseif ($pass !== ($_POST['password2'] ?? '')) $error = 'The two passwords don’t match.';
    else {
        db()->prepare('INSERT INTO users (name, email, password_hash, role, active, created_at) VALUES (?, ?, ?, ?, 1, ?)')
            ->execute([$name, strtolower($email), password_hash($pass, PASSWORD_DEFAULT), 'admin', date('Y-m-d H:i:s')]);
        flash('Installation complete. Sign in with the account you just created.');
        redirect('login.php');
    }
}

admin_header('Set up the school website');
?>
<?php if ($error): ?><p class="alert error" role="alert"><?= e($error) ?></p><?php endif; ?>
<p class="lead">Create the first administrator account. This person can add other staff accounts later.</p>
<form method="post" class="form narrow">
  <?= csrf_field() ?>
  <label>Full name<input name="name" required value="<?= e($name) ?>"></label>
  <label>Email<input name="email" type="email" required value="<?= e($email) ?>" autocomplete="username"></label>
  <label>Password <span class="hint">At least 10 characters</span><input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
  <label>Repeat password<input name="password2" type="password" required minlength="10" autocomplete="new-password"></label>
  <div><button class="btn primary" type="submit">Create administrator</button></div>
</form>
<?php admin_footer();
