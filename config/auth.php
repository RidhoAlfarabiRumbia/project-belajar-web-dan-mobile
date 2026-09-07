<?php

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();


// Cek login
if (!isset($_SESSION['user_id'])) {

    header("Location: /portal_magang/index.php");
    exit;
}


// Timeout 2 jam
$timeout = 7200;

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > $timeout)
) {

    $_SESSION = [];

    session_destroy();

    header("Location: /portal_magang/index.php");
    exit;
}


// Perbarui aktivitas
$_SESSION['last_activity'] = time();

?>