<?php
/** Shared layout for the staff administration area. */
require_once __DIR__ . '/../includes/bootstrap.php';

function admin_header(string $title, ?array $user = null, string $active = ''): void
{
    $links = ['index' => 'Dashboard', 'posts' => 'News & notices', 'gallery' => 'Gallery', 'pages' => 'Page text', 'messages' => 'Messages'];
    if ($user && $user['role'] === 'admin') { $links['users'] = 'Staff accounts'; $links['backup'] = 'Backup'; }
    $unread = 0;
    if ($user) { try { $unread = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(); } catch (Throwable $e) {} }
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<script>document.documentElement.classList.add('js')</script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Gelan School Admin</title>
<link rel="icon" href="../assets/img/crest.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Noto+Serif+Ethiopic:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css?v=1">
<link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body class="admin">
<header class="site">
  <div class="wrap bar">
    <a class="brand" href="index.php"><img class="crest" src="../assets/img/crest.svg" alt="" width="34" height="38"><span><b>Gelan School</b><small>Staff administration</small></span></a>
    <?php if ($user): ?>
      <button class="menu-btn" type="button" aria-expanded="false" aria-controls="mainnav">Menu</button>
      <nav class="main" id="mainnav" aria-label="Admin">
        <ul>
          <?php foreach ($links as $k => $label): ?>
            <li><a href="<?= $k ?>.php" <?= $active === $k ? 'aria-current="page"' : '' ?>><?= e($label) ?><?= $k === 'messages' && $unread ? ' <span class="count">' . $unread . '</span>' : '' ?></a></li>
          <?php endforeach; ?>
          <li><a href="../index.php" target="_blank" rel="noopener">View site</a></li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</header>
<main class="section" id="main"><div class="wrap">
<?php if ($user): ?>
  <p class="whoami">Signed in as <b><?= e($user['name']) ?></b> (<?= e($user['role']) ?>) · <a href="account.php">My account</a> · <a href="logout.php">Sign out</a></p>
<?php endif; ?>
<h1><?= e($title) ?></h1>
<?php foreach (flash() as [$type, $msg]): ?>
  <p class="alert <?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>"><?= e($msg) ?></p>
<?php endforeach;
}

function admin_footer(): void
{
    ?></div></main>
<script src="../assets/js/main.js?v=1"></script>
</body>
</html><?php
}

/** Reject POST requests without a valid form token. */
function admin_check_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
        flash('The form expired. Please try again.', 'error');
        redirect($_SERVER['PHP_SELF']);
    }
}
