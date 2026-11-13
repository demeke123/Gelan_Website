<?php
/** Public page header. Expects $page and $pageTitle. */
$nav = ['home' => 'navHome', 'about' => 'navAbout', 'academics' => 'navAcademics', 'boarding' => 'navBoarding',
        'news' => 'navNews', 'gallery' => 'navGallery', 'contact' => 'navContact'];
$activeNav = $page === 'post' ? 'news' : $page;
$langLink = function (string $l) use ($page) {
    $q = $_GET; $q['lang'] = $l;
    if ($page !== 'home') $q['page'] = $page;
    return 'index.php?' . http_build_query($q);
};
$fullTitle = $page === 'home' ? t('schoolName') : $pageTitle . ' | ' . t('schoolName');
?><!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8">
<script>document.documentElement.classList.add('js')</script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($metaDesc ?? t('metaDesc')) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($metaDesc ?? t('metaDesc')) ?>">
<meta property="og:type" content="website">
<link rel="icon" href="assets/img/crest.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Noto+Serif+Ethiopic:wght@500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=1">
</head>
<body>
<a class="skip" href="#main"><?= e(t('skip')) ?></a>
<div class="tibeb" aria-hidden="true"></div>
<header class="site">
  <div class="wrap bar">
    <a class="brand" href="<?= url() ?>">
      <img class="crest" src="assets/img/crest.svg" alt="" width="40" height="44">
      <span><b><?= e(t('schoolName')) ?></b><small><?= e(t('schoolAlt')) ?></small></span>
    </a>
    <div class="lang" role="group" aria-label="Language / ቋንቋ">
      <a href="<?= e($langLink('en')) ?>" lang="en" <?= lang() === 'en' ? 'aria-current="true"' : '' ?>>English</a>
      <a href="<?= e($langLink('am')) ?>" lang="am" <?= lang() === 'am' ? 'aria-current="true"' : '' ?>>አማርኛ</a>
    </div>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="mainnav"><?= e(t('menu')) ?></button>
    <nav class="main" id="mainnav" aria-label="Main">
      <ul>
        <?php foreach ($nav as $key => $label): ?>
          <li><a href="<?= url($key) ?>" <?= $activeNav === $key ? 'aria-current="page"' : '' ?>><?= e(t($label)) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>
<main id="main" tabindex="-1">
