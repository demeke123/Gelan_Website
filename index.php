<?php
/** Public website entry point: index.php?page=about etc. */
require __DIR__ . '/includes/bootstrap.php';

if (!is_installed()) {
    redirect('admin/setup.php');
}

$routes = [
    'home' => 'navHome', 'about' => 'aboutTitle', 'academics' => 'acadTitle', 'boarding' => 'boardTitle',
    'news' => 'newsTitle', 'post' => 'newsTitle', 'gallery' => 'galTitle', 'contact' => 'conTitle',
];
$page = $_GET['page'] ?? 'home';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = '404';
    $pageTitle = t('notFound');
} else {
    $pageTitle = t($routes[$page]);
}

// Pages that handle form posts or need data before output do it at the top of their file.
ob_start();
require __DIR__ . '/pages/' . $page . '.php';
$content = ob_get_clean();

require __DIR__ . '/includes/header.php';
echo $content;
require __DIR__ . '/includes/footer.php';
