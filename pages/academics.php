<?php
$events = db()->query("SELECT * FROM posts WHERE is_published = 1 AND type = 'event' AND event_date IS NOT NULL ORDER BY event_date ASC")->fetchAll();
$grades = setting_t('grades');
?>
<?= page_head(t('acadTitle'), t('acadLead')) ?>
<section class="section"><div class="wrap grid2">
  <div class="measure">
    <h2><?= e(t('progTitle')) ?></h2>
    <?= paras(setting_t('programs')) ?>
    <h3><?= e(t('gradesTitle')) ?></h3>
    <div class="<?= $grades === t('gradesDefault') ? 'pending' : '' ?>"><?= paras($grades) ?></div>
    <h3><?= e(t('supportTitle')) ?></h3>
    <p><?= e(t('support')) ?></p>
  </div>
  <div class="panel">
    <h3><?= e(t('subjTitle')) ?></h3>
    <ul class="subjects"><?php foreach (t('subjects') as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul>
  </div>
</div></section>

<section class="section tint" id="calendar"><div class="wrap">
  <h2><?= e(t('calTitle')) ?></h2>
  <?php if (!$events): ?>
    <p class="empty"><?= e(t('calEmpty')) ?></p>
  <?php else: ?>
    <div class="scroll"><table class="cal">
      <thead><tr><th scope="col"><?= e(t('calDate')) ?></th><th scope="col"><?= e(t('calEvent')) ?></th><th scope="col"><?= e(t('calDetails')) ?></th></tr></thead>
      <tbody>
      <?php foreach ($events as $ev): $past = $ev['event_date'] < date('Y-m-d'); ?>
        <tr class="<?= $past ? 'past' : '' ?>">
          <td class="nowrap"><?= e(fmt_date($ev['event_date'])) ?></td>
          <td><a href="<?= e(url('post', ['id' => $ev['id']])) ?>"><b><?= e(pick($ev, 'title')) ?></b></a></td>
          <td><?= e(excerpt(pick($ev, 'body'), 120)) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div></section>
