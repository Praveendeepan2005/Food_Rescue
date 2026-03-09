<?php
/**
 * auth_guard.php
 * Include at the top of every protected page.
 * Redirects to /login.php if no valid session exists.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['fr_user']) || empty($_SESSION['fr_user']['user_id'])) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please log in to access this page.'];
    header('Location: /login.php');
    exit();
}

$sessionUser = $_SESSION['fr_user'];
