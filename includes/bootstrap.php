<?php
/** Loaded by every page: configuration, database, session, language and helpers. */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session with safe cookie settings
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('gelan_sess');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
session_start();

// Language: ?lang=am|en, remembered in a cookie for a year
$GLOBALS['LANG'] = 'en';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'am'], true)) {
    $GLOBALS['LANG'] = $_GET['lang'];
    setcookie('gelan_lang', $_GET['lang'], ['expires' => time() + 31536000, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
} elseif (isset($_COOKIE['gelan_lang']) && in_array($_COOKIE['gelan_lang'], ['en', 'am'], true)) {
    $GLOBALS['LANG'] = $_COOKIE['gelan_lang'];
}
$GLOBALS['TEXT'] = require __DIR__ . '/lang.php';
