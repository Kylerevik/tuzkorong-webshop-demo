<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
date_default_timezone_set('Europe/Budapest');

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/catalog.php';
