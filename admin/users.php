<?php
/** Staff accounts (administrators only). Roles: admin = everything; editor = content only. */
require __DIR__ . '/_layout.php';
$user = require_login('admin');
admin_check_post();

$roles = ['editor' => 'Content editor', 'admin' => 'Administrator'];
$adminCount = fn() => (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND active = 1")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($act === 'add') {
        $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = (string)($_POST['password'] ?? ''); $role = array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'editor';
        $exists = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?'); $exists->execute([$email]);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) flash('Enter a name and a valid email address.', 'error');
        elseif ($exists->fetchColumn()) flash('An account with that email already exists.', 'error');
        elseif (strlen($pass) < 10) flash('Use a password of at least 10 characters.', 'error');
        else {
            db()->prepare('INSERT INTO users (name, email, password_hash, role, active, created_at) VALUES (?, ?, ?, ?, 1, ?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role, date('Y-m-d H:i:s')]);
            flash("Account created for $name. Give them the password in person, and ask them to change it under “My account”.");
        }
    } elseif ($id === (int)$user['id'] && in_array($act, ['role', 'toggle'], true)) {
        flash('You can’t change your own role or disable your own account.', 'error');
    } elseif ($act === 'role') {
        $role = array_key_exists($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'editor';
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
        flash('Role updated.');
    } elseif ($act === 'toggle') {
        db()->prepare('UPDATE users SET active = 1 - active WHERE id = ?')->execute([$id]);
        flash('Account status updated.');
    } elseif ($act === 'reset') {
        $pass = (string)($_POST['password'] ?? '');
        if (strlen($pass) < 10) flash('Use a password of at least 10 characters.', 'error');
        else { db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]); flash('Password changed.'); }
    }
    redirect('users.php');
}

$users = db()->query('SELECT id, name, email, role, active, last_login FROM users ORDER BY active DESC, name')->fetchAll();
admin_header('Staff accounts', $user, 'users');
?>
<p class="lead"><b>Content editors</b> can manage news, notices, events, photos, page text and messages. <b>Administrators</b> can also manage staff accounts and backups.</p>
<div class="scroll"><table class="table">
  <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last sign-in</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): $me = (int)$u['id'] === (int)$user['id']; ?>
    <tr class="<?= $u['active'] ? '' : 'muted' ?>">
      <td><?= e($u['name']) ?><?= $me ? ' (you)' : '' ?></td>
      <td><?= e($u['email']) ?></td>
      <td>
        <?php if ($me): echo e($roles[$u['role']]); else: ?>
        <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="act" value="role"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <select name="role" aria-label="Role for <?= e($u['name']) ?>" data-autosubmit><?php foreach ($roles as $k => $l): ?><option value="<?= $k ?>" <?= $u['role'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
          <noscript><button class="btn small line">Save</button></noscript>
        </form>
        <?php endif; ?>
      </td>
      <td class="nowrap"><?= e($u['last_login'] ?: 'Never') ?></td>
      <td><?= $u['active'] ? 'Active' : 'Disabled' ?></td>
      <td>
        <?php if (!$me): ?>
          <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small <?= $u['active'] ? 'danger' : 'line' ?>"><?= $u['active'] ? 'Disable' : 'Enable' ?></button></form>
        <?php endif; ?>
        <details class="inline-details"><summary>Set password</summary>
          <form method="post" class="form compact"><?= csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <input type="password" name="password" minlength="10" required placeholder="New password (10+ characters)" autocomplete="new-password" aria-label="New password">
            <button class="btn small primary">Save password</button></form>
        </details>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>

<form method="post" class="form panel">
  <?= csrf_field() ?><input type="hidden" name="act" value="add">
  <h2>Add a staff account</h2>
  <div class="row2">
    <label>Full name<input name="name" required></label>
    <label>Email<input name="email" type="email" required autocomplete="off"></label>
    <label>Temporary password <span class="hint">At least 10 characters</span><input name="password" type="password" minlength="10" required autocomplete="new-password"></label>
    <label>Role<select name="role"><?php foreach ($roles as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?></select></label>
  </div>
  <div><button class="btn primary" type="submit">Add account</button></div>
</form>
<?php admin_footer();
