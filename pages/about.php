<?= page_head(t('aboutTitle'), t('aboutLead')) ?>
<section class="section"><div class="wrap grid2">
  <div class="measure">
    <h2><?= e(t('profileTitle')) ?></h2>
    <?= paras(setting_t('profile')) ?>
    <h3><?= e(t('historyTitle')) ?></h3>
    <?php $h = setting_t('history'); ?>
    <div class="<?= $h === t('historyDefault') ? 'pending' : '' ?>"><?= paras($h) ?></div>
  </div>
  <div class="stack">
    <div class="panel"><h3><?= e(t('visionTitle')) ?></h3><?= paras(setting_t('vision')) ?></div>
    <div class="panel"><h3><?= e(t('missionTitle')) ?></h3><?= paras(setting_t('mission')) ?></div>
  </div>
</div></section>

<section class="section tint"><div class="wrap">
  <h2><?= e(t('valuesTitle')) ?></h2>
  <ul class="values">
    <?php foreach (t('values') as [$name, $desc]): ?>
      <li><b><?= e($name) ?></b><?= e($desc) ?></li>
    <?php endforeach; ?>
  </ul>
</div></section>

<section class="section"><div class="wrap">
  <h2><?= e(t('leadTitle')) ?></h2>
  <div class="leaders">
    <?php foreach (t('leaders') as $i => $role): $name = setting('leader_' . ($i + 1)); ?>
      <div class="panel leader">
        <div class="ph" aria-hidden="true"><?= e(mb_substr($name ?: $role, 0, 1)) ?></div>
        <b><?= e($role) ?></b>
        <div class="<?= $name ? '' : 'pending' ?>"><?= e($name ?: t('toConfirm')) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div></section>
