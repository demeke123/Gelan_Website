<?php
// Optional local overrides for testing (file is not needed on the live server)
if (is_file(__DIR__ . '/config.local.php')) require __DIR__ . '/config.local.php';
/**
 * Gelan Boys Boarding School website — configuration.
 * Settings for running on XAMPP (local computer).
 */

// Database: XAMPP's default MySQL login is user "root" with no password.
defined('DB_DRIVER') || define('DB_DRIVER', 'mysql');
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_NAME') || define('DB_NAME', 'gelan_school');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('SQLITE_PATH') || define('SQLITE_PATH', __DIR__ . '/data/gelan.sqlite');

// Uploads
defined('UPLOAD_DIR') || define('UPLOAD_DIR', __DIR__ . '/uploads');
defined('UPLOAD_URL') || define('UPLOAD_URL', 'uploads');
defined('MAX_UPLOAD_MB') || define('MAX_UPLOAD_MB', 5);

// Site
defined('POSTS_PER_PAGE') || define('POSTS_PER_PAGE', 10);
defined('DEBUG') || define('DEBUG', true); // true while testing; set to false on the live server

date_default_timezone_set('Africa/Addis_Ababa');