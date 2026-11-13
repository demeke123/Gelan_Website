<?php
$latest = db()->query("SELECT * FROM posts WHERE is_published = 1 ORDER BY publish_date DESC, id DESC LIMIT 3")->fetchAll();
?>
<section class="hero"><div class="wrap">
  <h1><?= e(t('schoolName')) ?></h1>
  <div class="alt" lang="<?= lang() === 'am' ? 'en' : 'am' ?>"><?= e(t('schoolAlt')) ?></div>
  <p><?= e(t('heroLead')) ?></p>
  <div class="actions">
    <a class="btn gold" href="<?= url('news') ?>"><?= e(t('heroCta1')) ?></a>
    <a class="btn ghost" href="<?= url('contact') ?>"><?= e(t('heroCta2')) ?></a>
  </div>
</div></section>

<section class="section"><div class="wrap grid2">
  <div class="welcome">
    <h2><?= e(t('welcomeTitle')) ?></h2>
    <blockquote><?= paras(setting_t('welcome')) ?></blockquote>
    <p class="cite"><?= e(setting('leader_1') ?: t('welcomeSign')) ?><?= setting('leader_1') ? ', ' . e(t('welcomeSign')) : '' ?></p>
  </div>
  <div>
    <h2><?= e(t('quickTitle')) ?></h2>
    <ul class="quick">
      <li><a href="<?= url('academics') ?>"><b><?= e(t('q1')) ?></b><span><?= e(t('q1d')) ?></span></a></li>
      <li><a href="<?= url('boarding') ?>"><b><?= e(t('q2')) ?></b><span><?= e(t('q2d')) ?></span></a></li>
      <li><a href="<?= url('about') ?>"><b><?= e(t('q3')) ?></b><span><?= e(t('q3d')) ?></span></a></li>
      <li><a href="<?= url('contact') ?>"><b><?= e(t('q4')) ?></b><span><?= e(t('q4d')) ?></span></a></li>
    </ul>
  </div>
</div></section>

<section class="section tint"><div class="wrap">
  <h2><?= e(t('latestTitle')) ?></h2>
  <?php if (!$latest): ?>
    <p class="empty"><?= e(t('noPosts')) ?></p>
  <?php else: ?>
    <div class="cards"><?php foreach ($latest as $p) echo post_card($p); ?></div>
  <?php endif; ?>
  <p class="more"><a href="<?= url('news') ?>"><?= e(t('seeAll')) ?></a></p>
</div></section>
