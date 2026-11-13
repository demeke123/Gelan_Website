<?php
$photos = db()->query('SELECT * FROM gallery ORDER BY created_at DESC, id DESC')->fetchAll();
?>
<?= page_head(t('galTitle'), t('galLead')) ?>
<section class="section"><div class="wrap">
  <?php if (!$photos): ?>
    <p class="empty"><?= e(t('galEmpty')) ?></p>
  <?php else: ?>
    <div class="gallery">
      <?php foreach ($photos as $ph): $cap = pick($ph, 'caption'); ?>
        <figure>
          <a href="<?= e(UPLOAD_URL . '/' . $ph['filename']) ?>" class="lb" data-cap="<?= e($cap) ?>">
            <img loading="lazy" src="<?= e(UPLOAD_URL . '/thumbs/' . $ph['filename']) ?>" alt="<?= e($cap) ?>">
          </a>
          <?php if ($cap): ?><figcaption><?= e($cap) ?></figcaption><?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div></section>
