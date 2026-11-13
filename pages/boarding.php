<?= page_head(t('boardTitle'), t('boardLead')) ?>
<section class="section"><div class="wrap grid2">
  <div class="measure">
    <h2><?= e(t('lifeTitle')) ?></h2>
    <?= paras(setting_t('boarding')) ?>
  </div>
  <div class="panel">
    <h3><?= e(t('dayTitle')) ?></h3>
    <table class="cal day"><tbody>
      <?php foreach (t('day') as [$when, $what]): ?>
        <tr><th scope="row"><?= e($when) ?></th><td><?= e($what) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <p class="status"><?= e(t('dayNote')) ?></p>
  </div>
</div></section>

<section class="section tint"><div class="wrap">
  <h2><?= e(t('facTitle')) ?></h2>
  <div class="grid3">
    <?php foreach (t('facilities') as [$name, $desc]): ?>
      <div class="panel facility"><h3><?= e($name) ?></h3><p><?= e($desc) ?></p></div>
    <?php endforeach; ?>
  </div>
</div></section>
