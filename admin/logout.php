<?php
require __DIR__ . '/../includes/bootstrap.php';
$_SESSION = [];
session_destroy();
setcookie('gelan_sess', '', ['expires' => time() - 3600, 'path' => '/']);
header('Location: login.php');
