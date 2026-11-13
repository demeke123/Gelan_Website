<?php
$types = ['news', 'notice', 'event'];
$type = in_array($_GET['type'] ?? '', $types, true) ? $_GET['type'] : '';
$where = 'is_published = 1' . ($type ? ' AND type = ?' : '');
$args = $type ? [$type] : [];

$count = db()->prepare("SELECT COUNT(*) FROM posts WHERE $where");
$count->execute($args);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / POSTS_PER_PAGE));
$p = min($pages, max(1, (int)($_GET['p'] ?? 1)));
$offset = ($p - 1) * POSTS_PER_PAGE;

$st = db()->prepare("SELECT * FROM posts WHERE $where ORDER BY publish_date DESC, id DESC LIMIT " . POSTS_PER_PAGE . " OFFSET $offset");
$st->execute($args);
$posts = $st->fetchAll();
$link = fn(array $extra) => url('news', array_filter(array_merge(['type' => $type], $extra)));
?>
<?= page_head(t('newsTitle'), t('newsLead')) ?>
<section class="section"><div class="wrap">
  <nav class="filters" aria-label="<?= e(t('newsTitle')) ?>">
    <a class="chip" href="<?= e(url('news')) ?>" <?= $type === '' ? 'aria-current="true"' : '' ?>><?= e(t('fAll')) ?></a>
    <?php foreach ($types as $ty): ?>
      <a class="chip" href="<?= e(url('news', ['type' => $ty])) ?>" <?= $type === $ty ? 'aria-current="true"' : '' ?>><?= e(t('tag_' . $ty)) ?></a>
    <?php endforeach; ?>
  </nav>
  <?php if (!$posts): ?>
    <p class="empty"><?= e(t('noPosts')) ?></p>
  <?php else: ?>
    <div class="posts"><?php foreach ($posts as $post) echo post_card($post); ?></div>
    <?php if ($pages > 1): ?>
      <nav class="pager">
        <?php if ($p > 1): ?><a class="btn line" href="<?= e($link(['p' => $p - 1])) ?>"><?= e(t('prev')) ?></a><?php endif; ?>
        <span><?= e(sprintf(t('pageOf'), $p, $pages)) ?></span>
        <?php if ($p < $pages): ?><a class="btn line" href="<?= e($link(['p' => $p + 1])) ?>"><?= e(t('next')) ?></a><?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div></section>
