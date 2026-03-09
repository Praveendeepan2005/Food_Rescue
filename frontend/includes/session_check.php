<?php
/**
 * Food Rescue – Session Guard
 * Include on any protected page that requires authentication.
 *
 * Usage:
 *   <?php require_once __DIR__ . '/../includes/session_check.php'; ?>
 *
 * Behaviour:
 *   - If $_SESSION['fr_user'] is not set → redirect to login.php
 *   - If it IS set → $sessionUser is available as an array
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['fr_user'])) {
    header('Location: /login.php');
    exit();
}

$sessionUser = $_SESSION['fr_user'];
