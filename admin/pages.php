<?php
/** Edit the basic page text and contact details shown on the public site. */
require __DIR__ . '/_layout.php';
$user = require_login();
admin_check_post();

$T = $GLOBALS['TEXT'];
// key => [label, page, rows]
$bilingual = [
    'welcome'  => ['Welcome message', 'Home', 6],
    'profile'  => ['School profile', 'About', 6],
    'history'  => ['History', 'About', 6],
    'vision'   => ['Vision', 'About', 3],
    'mission'  => ['Mission', 'About', 3],
    'programs' => ['Learning program', 'Academics', 5],
    'grades'   => ['Grade levels', 'Academics', 2],
    'boarding' => ['Boarding life', 'Boarding & Facilities', 5],
    'address'  => ['Address', 'Contact', 0],
    'hours'    => ['Office hours', 'Contact', 0],
];
$single = [
    'phone' => ['Phone number', 'tel'], 'email' => ['Email address', 'email'],
    'map_query' => ['Map search text (e.g. “Gelan, Oromia, Ethiopia”)', 'text'],
    'leader_1' => ['Name: School Director', 'text'], 'leader_2' => ['Name: Vice Director – Academic Affairs', 'text'],
    'leader_3' => ['Name: Vice Director – Administration', 'text'], 'leader_4' => ['Name: Boarding Supervisor', 'text'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($bilingual as $k => $_) foreach (['en', 'am'] as $l) setting_save("{$k}_{$l}", trim($_POST["{$k}_{$l}"] ?? ''));
    foreach ($single as $k => $_) setting_save($k, trim($_POST[$k] ?? ''));
    flash('Page text saved. Empty boxes show the default text on the website.');
    redirect('pages.php');
}

$s = settings_all();
admin_header('Page text', $user, 'pages');
?>
<p class="lead">Edit the text visitors see on the website. Leave a box empty to show the default wording (shown in grey). Leave a blank line between paragraphs.</p>
<form method="post" class="form">
  <?= csrf_field() ?>
  <?php $lastPage = ''; foreach ($bilingual as $k => [$label, $pg, $rows]): ?>
    <?php if ($pg !== $lastPage): $lastPage = $pg; ?><h2><?= e($pg) ?> page</h2><?php endif; ?>
    <fieldset>
      <legend><?= e($label) ?></legend>
      <div class="row2">
        <?php foreach (['en' => 'English', 'am' => 'Amharic'] as $l => $ln): $ph = $T[$l][$k . 'Default'] ?? ''; ?>
          <label><?= $ln ?>
            <?php if ($rows): ?>
              <textarea name="<?= $k . '_' . $l ?>" rows="<?= $rows ?>" <?= $l === 'am' ? 'lang="am"' : '' ?> placeholder="<?= e($ph) ?>"><?= e($s[$k . '_' . $l] ?? '') ?></textarea>
            <?php else: ?>
              <input name="<?= $k . '_' . $l ?>" <?= $l === 'am' ? 'lang="am"' : '' ?> placeholder="<?= e($ph) ?>" value="<?= e($s[$k . '_' . $l] ?? '') ?>">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
  <?php endforeach; ?>

  <h2>Contact details and leadership</h2>
  <div class="row2">
    <?php foreach ($single as $k => [$label, $type]): ?>
      <label><?= e($label) ?><input type="<?= $type ?>" name="<?= $k ?>" value="<?= e($s[$k] ?? '') ?>"></label>
    <?php endforeach; ?>
  </div>
  <div class="sticky-save"><button class="btn primary" type="submit">Save page text</button></div>
</form>
<?php admin_footer();
