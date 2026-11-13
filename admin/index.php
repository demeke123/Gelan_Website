<?php
require __DIR__ . '/_layout.php';
if (!is_installed()) redirect('setup.php');
$user = require_login();

$c = fn(string $sql) => (int)db()->query($sql)->fetchColumn();
$stats = [
    ['Published posts', $c('SELECT COUNT(*) FROM posts WHERE is_published = 1'), 'posts.php'],
    ['Drafts', $c('SELECT COUNT(*) FROM posts WHERE is_published = 0'), 'posts.php?status=draft'],
    ['Gallery photos', $c('SELECT COUNT(*) FROM gallery'), 'gallery.php'],
    ['Unread messages', $c('SELECT COUNT(*) FROM messages WHERE is_read = 0'), 'messages.php'],
];
$recent = db()->query('SELECT id, type, title_en, title_am, publish_date, is_published FROM posts ORDER BY id DESC LIMIT 5')->fetchAll();

admin_header('Dashboard', $user, 'index');
?>
<div class="stats">
  <?php foreach ($stats as [$label, $n, $href]): ?>
    <a class="panel stat" href="<?= e($href) ?>"><b><?= $n ?></b><span><?= e($label) ?></span></a>
  <?php endforeach; ?>
</div>
<div class="actions-row">
  <a class="btn primary" href="posts.php?action=new">Write a post</a>
  <a class="btn line" href="gallery.php">Upload photos</a>
  <a class="btn line" href="pages.php">Edit page text</a>
</div>
<h2>Recent posts</h2>
<?php if (!$recent): ?>
  <p class="empty">No posts yet. Write the first announcement for the website.</p>
<?php else: ?>
  <div class="scroll"><table class="table">
    <thead><tr><th>Title</th><th>Type</th><th>Date</th><th>Status</th><th></th></tr></thead>
    <tbody><?php foreach ($recent as $p): ?>
      <tr><td><?= e($p['title_en'] ?: $p['title_am']) ?></td><td><?= e(ucfirst($p['type'])) ?></td><td class="nowrap"><?= e($p['publish_date']) ?></td>
      <td><?= $p['is_published'] ? 'Published' : 'Draft' ?></td><td><a href="posts.php?action=edit&amp;id=<?= (int)$p['id'] ?>">Edit</a></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
<?php endif; ?>
<?php admin_footer();
