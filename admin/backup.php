<?php
/**
 * Backup and restore of website content (administrators only).
 * Downloads posts, gallery records, page text and messages as one JSON file.
 * Photos are in the /uploads folder: back that folder up with your hosting file manager.
 */
require __DIR__ . '/_layout.php';
$user = require_login('admin');
admin_check_post();

$tables = ['posts', 'gallery', 'settings', 'messages'];

if (isset($_GET['download'])) {
    $out = ['site' => 'gelan-school', 'version' => 1, 'created' => date('c')];
    foreach ($tables as $tb) $out[$tb] = db()->query("SELECT * FROM $tb")->fetchAll();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="gelan-website-backup-' . date('Y-m-d') . '.json"');
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    $data = json_decode((string)@file_get_contents($_FILES['backup']['tmp_name'] ?? ''), true);
    if (!is_array($data) || ($data['site'] ?? '') !== 'gelan-school') {
        flash('That file is not a backup from this website.', 'error');
    } elseif (($_POST['confirm'] ?? '') !== 'RESTORE') {
        flash('Type RESTORE to confirm. Restoring replaces all current content.', 'error');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            foreach ($tables as $tb) {
                $pdo->exec("DELETE FROM $tb");
                foreach ($data[$tb] ?? [] as $row) {
                    $cols = array_keys($row);
                    foreach ($cols as $c) if (!preg_match('/^[a-z_]+$/', $c)) throw new RuntimeException('Invalid column in backup.');
                    $pdo->prepare("INSERT INTO $tb (" . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')
                        ->execute(array_values($row));
                }
            }
            $pdo->commit();
            flash('Content restored from the backup from ' . ($data['created'] ?? 'unknown date') . '.');
        } catch (Throwable $ex) {
            $pdo->rollBack();
            flash('Restore failed; nothing was changed. ' . (DEBUG ? $ex->getMessage() : ''), 'error');
        }
    }
    redirect('backup.php');
}

admin_header('Backup', $user, 'backup');
?>
<div class="panel">
  <h2>Download a backup</h2>
  <p>Saves all news, notices, events, photo captions, page text and messages to one file. Do this at least once a month and keep the file somewhere safe (for example, on a USB drive and in a school email account).</p>
  <p>Staff accounts are not included. Photos are stored in the <code>uploads</code> folder; copy that folder with your hosting control panel’s file manager.</p>
  <a class="btn primary" href="backup.php?download=1">Download backup file</a>
</div>
<form method="post" enctype="multipart/form-data" class="form panel" data-confirm="Replace all current content with this backup?">
  <?= csrf_field() ?>
  <h2>Restore from a backup</h2>
  <p class="alert warn">Restoring replaces everything currently on the website with the content of the backup file.</p>
  <label>Backup file (.json)<input type="file" name="backup" accept=".json,application/json" required></label>
  <label>Type RESTORE to confirm<input name="confirm" autocomplete="off" required></label>
  <div><button class="btn danger" type="submit" name="restore" value="1">Restore content</button></div>
</form>
<?php admin_footer();
