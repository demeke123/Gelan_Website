<?php
$st = db()->prepare('SELECT * FROM posts WHERE id = ? AND is_published = 1');
$st->execute([(int)($_GET['id'] ?? 0)]);
$post = $st->fetch();
if (!$post) {
    http_response_code(404);
    $pageTitle = t('notFound');
    require __DIR__ . '/404.php';
    return;
}
$pageTitle = pick($post, 'title');
$metaDesc = excerpt(pick($post, 'body'), 155);
?>
<article class="section"><div class="wrap measure">
  <p><a href="<?= url('news') ?>"><?= e(t('backNews')) ?></a></p>
  <span class="tag <?= e($post['type']) ?>"><?= e(t('tag_' . $post['type'])) ?></span>
  <span class="date"><?= e(fmt_date($post['publish_date'])) ?></span>
  <h1 class="post-title"><?= e($pageTitle) ?></h1>
  <?php if ($post['type'] === 'event' && $post['event_date']): ?>
    <p class="eventdate"><b><?= e(t('eventOn')) ?>:</b> <?= e(fmt_date($post['event_date'])) ?></p>
  <?php endif; ?>
  <div class="post-body"><?= paras(pick($post, 'body')) ?></div>
</div></article>
