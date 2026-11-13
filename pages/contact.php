<?php
$errors = [];
$old = ['name' => '', 'phone' => '', 'email' => '', 'subject' => '', 'message' => ''];
$sent = isset($_GET['sent']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = trim((string)($_POST[$k] ?? ''));
    if (!csrf_ok()) $errors[] = t('fErrToken');
    if (!empty($_POST['website'])) $errors[] = t('fErrToken'); // hidden spam trap
    if ($old['name'] === '') $errors[] = t('fErrName');
    if ($old['phone'] === '' && $old['email'] === '') $errors[] = t('fErrContact');
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = t('fErrEmail');
    if (mb_strlen($old['message']) < 10) $errors[] = t('fErrMsg');

    if (!$errors) {
        $st = db()->prepare("SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at >= ?");
        $st->execute([client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
        if ((int)$st->fetchColumn() >= 5) $errors[] = t('fErrRate');
    }
    if (!$errors) {
        db()->prepare('INSERT INTO messages (name, phone, email, subject, message, ip, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([mb_substr($old['name'], 0, 120), mb_substr($old['phone'], 0, 40), mb_substr($old['email'], 0, 190),
                       mb_substr($old['subject'], 0, 200), mb_substr($old['message'], 0, 5000), client_ip(), date('Y-m-d H:i:s')]);
        redirect(url('contact', ['sent' => 1]) . '#form');
    }
}
$phone = setting('phone');
$email = setting('email');
$mapQ = setting('map_query', 'Gelan, Oromia, Ethiopia');
?>
<?= page_head(t('conTitle'), t('conLead')) ?>
<section class="section"><div class="wrap grid2">
  <div class="stack">
    <div class="panel contact"><dl>
      <dt><?= e(t('conAddr')) ?></dt><dd><?= e(setting_t('address')) ?></dd>
      <dt><?= e(t('conPhone')) ?></dt><dd><?= $phone ? '<a href="tel:' . e(preg_replace('/\s+/', '', $phone)) . '">' . e($phone) . '</a>' : '<span class="pending">' . e(t('pendingVal')) . '</span>' ?></dd>
      <dt><?= e(t('conEmail')) ?></dt><dd><?= $email ? '<a href="mailto:' . e($email) . '">' . e($email) . '</a>' : '<span class="pending">' . e(t('pendingVal')) . '</span>' ?></dd>
      <dt><?= e(t('conHours')) ?></dt><dd><?= e(setting_t('hours')) ?></dd>
    </dl></div>
    <div>
      <h2><?= e(t('mapTitle')) ?></h2>
      <p><?= e(t('mapText')) ?></p>
      <a class="btn primary" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($mapQ)) ?>" target="_blank" rel="noopener"><?= e(t('mapLink')) ?></a>
    </div>
  </div>

  <div id="form">
    <h2><?= e(t('formTitle')) ?></h2>
    <p><?= e(t('formLead')) ?></p>
    <?php if ($sent): ?><p class="alert ok" role="status"><?= e(t('fSent')) ?></p><?php endif; ?>
    <?php if ($errors): ?><div class="alert error" role="alert"><?php foreach (array_unique($errors) as $er) echo '<p>' . e($er) . '</p>'; ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('contact')) ?>#form" class="form">
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
      <label><?= e(t('fName')) ?><input name="name" required maxlength="120" value="<?= e($old['name']) ?>" autocomplete="name"></label>
      <div class="row2">
        <label><?= e(t('fPhone')) ?><input name="phone" type="tel" maxlength="40" value="<?= e($old['phone']) ?>" autocomplete="tel"></label>
        <label><?= e(t('fEmail')) ?><input name="email" type="email" maxlength="190" value="<?= e($old['email']) ?>" autocomplete="email"></label>
      </div>
      <label><?= e(t('fSubject')) ?><input name="subject" maxlength="200" value="<?= e($old['subject']) ?>"></label>
      <label><?= e(t('fMessage')) ?><textarea name="message" required minlength="10" maxlength="5000"><?= e($old['message']) ?></textarea></label>
      <p class="status"><?= e(t('fPrivacy')) ?></p>
      <div><button class="btn primary" type="submit"><?= e(t('fSend')) ?></button></div>
    </form>
  </div>
</div></section>
