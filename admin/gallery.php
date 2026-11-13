<?php
require __DIR__ . '/_layout.php';
$user = require_login();
admin_check_post();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload'])) {
        $files = $_FILES['photos'] ?? null;
        $ok = 0; $fail = [];
        if ($files && is_array($files['name'])) {
            foreach ($files['name'] as $i => $n) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                try {
                    $name = save_uploaded_image(['name' => $n, 'tmp_name' => $files['tmp_name'][$i], 'size' => $files['size'][$i], 'error' => $files['error'][$i]]);
                    db()->prepare('INSERT INTO gallery (filename, caption_en, caption_am, created_by, created_at) VALUES (?, ?, ?, ?, ?)')
                        ->execute([$name, trim($_POST['caption_en'] ?? ''), trim($_POST['caption_am'] ?? ''), $user['id'], date('Y-m-d H:i:s')]);
                    $ok++;
                } catch (RuntimeException $ex) {
                    $fail[] = $n . ': ' . $ex->getMessage();
                }
            }
        }
        if ($ok) flash($ok === 1 ? 'Photo uploaded.' : "$ok photos uploaded.");
        foreach ($fail as $f) flash($f, 'error');
        if (!$ok && !$fail) flash('Choose at least one photo to upload.', 'error');
    }
    if (isset($_POST['caption'])) {
        db()->prepare('UPDATE gallery SET caption_en = ?, caption_am = ? WHERE id = ?')
            ->execute([trim($_POST['caption_en'] ?? ''), trim($_POST['caption_am'] ?? ''), (int)$_POST['caption']]);
        flash('Caption saved.');
    }
    if (isset($_POST['delete'])) {
        $st = db()->prepare('SELECT filename FROM gallery WHERE id = ?');
        $st->execute([(int)$_POST['delete']]);
        if ($fn = $st->fetchColumn()) {
            delete_image($fn);
            db()->prepare('DELETE FROM gallery WHERE id = ?')->execute([(int)$_POST['delete']]);
            flash('Photo deleted.');
        }
    }
    redirect('gallery.php');
}

$photos = db()->query('SELECT * FROM gallery ORDER BY created_at DESC, id DESC')->fetchAll();
admin_header('Gallery', $user, 'gallery');
?>
<form method="post" enctype="multipart/form-data" class="form panel">
  <?= csrf_field() ?>
  <h2>Upload photos</h2>
  <p class="alert warn">Only publish photos of students with school authorization and appropriate consent. Location data stored inside photos is removed automatically.</p>
  <label>Photos <span class="hint">JPG, PNG or WebP, up to <?= MAX_UPLOAD_MB ?> MB each. You can choose several at once.</span>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
  <div class="row2">
    <label>Caption (English) <span class="hint">Used for every photo in this upload</span><input name="caption_en" maxlength="255"></label>
    <label>Caption (Amharic)<input name="caption_am" lang="am" maxlength="255"></label>
  </div>
  <div><button class="btn primary" type="submit" name="upload" value="1">Upload photos</button></div>
</form>

<h2><?= count($photos) ?> published photo<?= count($photos) === 1 ? '' : 's' ?></h2>
<?php if (!$photos): ?>
  <p class="empty">No photos yet. Upload the first approved photos above.</p>
<?php else: ?>
  <div class="admin-gallery">
  <?php foreach ($photos as $ph): ?>
    <div class="panel">
      <img src="../<?= e(UPLOAD_URL . '/thumbs/' . $ph['filename']) ?>" alt="" loading="lazy">
      <form method="post" class="form compact">
        <?= csrf_field() ?>
        <label>English caption<input name="caption_en" value="<?= e($ph['caption_en']) ?>" maxlength="255"></label>
        <label>Amharic caption<input name="caption_am" lang="am" value="<?= e($ph['caption_am']) ?>" maxlength="255"></label>
        <button class="btn small line" name="caption" value="<?= (int)$ph['id'] ?>">Save caption</button>
      </form>
      <form method="post" data-confirm="Delete this photo from the website?">
        <?= csrf_field() ?><button class="btn small danger" name="delete" value="<?= (int)$ph['id'] ?>">Delete photo</button>
      </form>
    </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php admin_footer();
