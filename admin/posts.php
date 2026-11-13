<?php
require __DIR__ . '/_layout.php';
$user = require_login();
admin_check_post();

$types = ['news' => 'News', 'notice' => 'Notice', 'event' => 'Event'];
$action = $_GET['action'] ?? 'list';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    db()->prepare('DELETE FROM posts WHERE id = ?')->execute([(int)$_POST['delete']]);
    flash('Post deleted.');
    redirect('posts.php');
}

// Create or update
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = (int)($_POST['id'] ?? 0);
    $d = [
        'type' => array_key_exists($_POST['type'] ?? '', $types) ? $_POST['type'] : 'news',
        'title_en' => trim($_POST['title_en'] ?? ''), 'title_am' => trim($_POST['title_am'] ?? ''),
        'body_en' => trim($_POST['body_en'] ?? ''), 'body_am' => trim($_POST['body_am'] ?? ''),
        'publish_date' => $_POST['publish_date'] ?? '', 'event_date' => $_POST['event_date'] ?? '',
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];
    $validDate = fn($s) => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) && strtotime($s);
    if ($d['title_en'] === '' && $d['title_am'] === '') $errors[] = 'Add an English or Amharic title.';
    if (!$validDate($d['publish_date'])) $errors[] = 'Choose a publish date.';
    if ($d['type'] === 'event') {
        if (!$validDate($d['event_date'])) $errors[] = 'Events need an event date so they appear in the academic calendar.';
    } else {
        $d['event_date'] = null;
    }
    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE posts SET type=?, title_en=?, title_am=?, body_en=?, body_am=?, publish_date=?, event_date=?, is_published=?, updated_at=? WHERE id=?')
                ->execute([$d['type'], $d['title_en'], $d['title_am'], $d['body_en'], $d['body_am'], $d['publish_date'], $d['event_date'], $d['is_published'], date('Y-m-d H:i:s'), $id]);
            flash('Changes saved.');
        } else {
            db()->prepare('INSERT INTO posts (type, title_en, title_am, body_en, body_am, publish_date, event_date, is_published, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$d['type'], $d['title_en'], $d['title_am'], $d['body_en'], $d['body_am'], $d['publish_date'], $d['event_date'], $d['is_published'], $user['id'], date('Y-m-d H:i:s')]);
            flash($d['is_published'] ? 'Post published.' : 'Draft saved.');
        }
        redirect('posts.php');
    }
    $post = $d + ['id' => $id];
    $action = $id ? 'edit' : 'new';
}

if ($action === 'new' || $action === 'edit') {
    if (!isset($post)) {
        $post = ['id' => 0, 'type' => 'news', 'title_en' => '', 'title_am' => '', 'body_en' => '', 'body_am' => '',
                 'publish_date' => date('Y-m-d'), 'event_date' => '', 'is_published' => 1];
        if ($action === 'edit') {
            $st = db()->prepare('SELECT * FROM posts WHERE id = ?');
            $st->execute([(int)($_GET['id'] ?? 0)]);
            $post = $st->fetch();
            if (!$post) { flash('That post no longer exists.', 'error'); redirect('posts.php'); }
        }
    }
    admin_header($post['id'] ? 'Edit post' : 'Write a post', $user, 'posts');
    if ($errors) echo '<div class="alert error" role="alert">' . implode('', array_map(fn($x) => '<p>' . e($x) . '</p>', $errors)) . '</div>';
    ?>
    <form method="post" class="form" action="posts.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
      <div class="row3">
        <label>Type<select name="type" id="postType">
          <?php foreach ($types as $k => $label): ?><option value="<?= $k ?>" <?= $post['type'] === $k ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
        </select></label>
        <label>Publish date<input type="date" name="publish_date" required value="<?= e($post['publish_date']) ?>"></label>
        <label id="eventDateWrap">Event date<input type="date" name="event_date" value="<?= e($post['event_date']) ?>"></label>
      </div>
      <div class="row2">
        <label>Title (English)<input name="title_en" maxlength="255" value="<?= e($post['title_en']) ?>"></label>
        <label>Title (Amharic)<input name="title_am" lang="am" maxlength="255" value="<?= e($post['title_am']) ?>"></label>
      </div>
      <div class="row2">
        <label>Text (English) <span class="hint">Leave a blank line between paragraphs.</span><textarea name="body_en" rows="10"><?= e($post['body_en']) ?></textarea></label>
        <label>Text (Amharic)<textarea name="body_am" lang="am" rows="10"><?= e($post['body_am']) ?></textarea></label>
      </div>
      <label class="check"><input type="checkbox" name="is_published" <?= $post['is_published'] ? 'checked' : '' ?>> Show on the public website (untick to keep as a draft)</label>
      <p class="hint">If only one language is filled in, visitors see that language in both versions of the site.</p>
      <div class="actions-row"><button class="btn primary" type="submit" name="save" value="1"><?= $post['id'] ? 'Save changes' : 'Save post' ?></button><a class="btn line" href="posts.php">Cancel</a></div>
    </form>
    <?php
    admin_footer();
    exit;
}

// List
$status = $_GET['status'] ?? '';
$sql = 'SELECT * FROM posts' . ($status === 'draft' ? ' WHERE is_published = 0' : '') . ' ORDER BY publish_date DESC, id DESC';
$posts = db()->query($sql)->fetchAll();
admin_header('News & notices', $user, 'posts');
?>
<div class="actions-row">
  <a class="btn primary" href="posts.php?action=new">Write a post</a>
  <a class="btn line" href="posts.php"<?= $status ? '' : ' aria-current="true"' ?>>All</a>
  <a class="btn line" href="posts.php?status=draft"<?= $status === 'draft' ? ' aria-current="true"' : '' ?>>Drafts only</a>
</div>
<?php if (!$posts): ?>
  <p class="empty">No posts here yet.</p>
<?php else: ?>
  <div class="scroll"><table class="table">
    <thead><tr><th>Title</th><th>Type</th><th>Published</th><th>Event date</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
      <tr>
        <td><?= e($p['title_en'] ?: $p['title_am']) ?><?php if ($p['title_en'] && $p['title_am']): ?><br><small lang="am"><?= e($p['title_am']) ?></small><?php endif; ?></td>
        <td><?= e($types[$p['type']] ?? $p['type']) ?></td>
        <td class="nowrap"><?= e($p['publish_date']) ?></td>
        <td class="nowrap"><?= e($p['event_date'] ?? '') ?></td>
        <td><?= $p['is_published'] ? 'Published' : '<b>Draft</b>' ?></td>
        <td class="nowrap">
          <a class="btn small line" href="posts.php?action=edit&amp;id=<?= (int)$p['id'] ?>">Edit</a>
          <form method="post" class="inline" data-confirm="Delete this post? This can’t be undone.">
            <?= csrf_field() ?><button class="btn small danger" name="delete" value="<?= (int)$p['id'] ?>">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
<?php admin_footer();
