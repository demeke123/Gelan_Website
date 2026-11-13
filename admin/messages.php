<?php
require __DIR__ . '/_layout.php';
$user = require_login();
admin_check_post();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['read']))   db()->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([(int)$_POST['read']]);
    if (isset($_POST['unread'])) db()->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([(int)$_POST['unread']]);
    if (isset($_POST['delete'])) { db()->prepare('DELETE FROM messages WHERE id = ?')->execute([(int)$_POST['delete']]); flash('Message deleted.'); }
    redirect('messages.php');
}

$messages = db()->query('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC LIMIT 200')->fetchAll();
admin_header('Messages', $user, 'messages');
?>
<p class="lead">Messages sent through the contact form on the website. Reply by phone or email, then mark them as read.</p>
<?php if (!$messages): ?>
  <p class="empty">No messages yet.</p>
<?php else: foreach ($messages as $m): ?>
  <article class="panel message <?= $m['is_read'] ? 'read' : 'unread' ?>">
    <header>
      <b><?= e($m['name']) ?></b><?= $m['is_read'] ? '' : ' <span class="count">New</span>' ?>
      <span class="date"><?= e($m['created_at']) ?></span>
    </header>
    <p class="meta">
      <?php if ($m['phone']): ?>Phone: <a href="tel:<?= e(preg_replace('/\s+/', '', $m['phone'])) ?>"><?= e($m['phone']) ?></a><?php endif; ?>
      <?php if ($m['phone'] && $m['email']): ?> · <?php endif; ?>
      <?php if ($m['email']): ?>Email: <a href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $m['subject'])) ?>"><?= e($m['email']) ?></a><?php endif; ?>
    </p>
    <?php if ($m['subject']): ?><h3><?= e($m['subject']) ?></h3><?php endif; ?>
    <?= paras($m['message']) ?>
    <form method="post" class="actions-row">
      <?= csrf_field() ?>
      <?php if ($m['is_read']): ?>
        <button class="btn small line" name="unread" value="<?= (int)$m['id'] ?>">Mark as unread</button>
      <?php else: ?>
        <button class="btn small primary" name="read" value="<?= (int)$m['id'] ?>">Mark as read</button>
      <?php endif; ?>
    </form>
    <form method="post" data-confirm="Delete this message?">
      <?= csrf_field() ?><button class="btn small danger" name="delete" value="<?= (int)$m['id'] ?>">Delete</button>
    </form>
  </article>
<?php endforeach; endif; ?>
<?php admin_footer();
