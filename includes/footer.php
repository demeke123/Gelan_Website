</main>
<footer class="site">
  <div class="tibeb" aria-hidden="true"></div>
  <div class="wrap">
    <div><b><?= e(t('schoolName')) ?></b><br><span lang="<?= lang() === 'am' ? 'en' : 'am' ?>"><?= e(t('schoolAlt')) ?></span></div>
    <div>
      <?= e(setting_t('address')) ?>
      <?php if ($ph = setting('phone')): ?><br><a href="tel:<?= e(preg_replace('/\s+/', '', $ph)) ?>"><?= e($ph) ?></a><?php endif; ?>
      <?php if ($em = setting('email')): ?><br><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a><?php endif; ?>
    </div>
    <div>© <?= date('Y') ?> <?= e(t('schoolName')) ?>. <?= e(t('rights')) ?><br><a href="admin/"><?= e(t('staffLogin')) ?></a></div>
  </div>
</footer>
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo" hidden>
  <button class="btn gold" type="button" id="lbClose"><?= e(t('close')) ?></button>
  <img id="lbImg" alt="">
  <p id="lbCap"></p>
</div>
<script src="assets/js/main.js?v=1"></script>
</body>
</html>
