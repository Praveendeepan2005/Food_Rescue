<?php
/**
 * dashboard.php — Role-based routing hub
 * Routes to the correct overview page based on session role.
 */
require_once __DIR__ . '/includes/auth_guard.php';

$role = $sessionUser['role'] ?? '';

switch ($role) {
    case 'DONOR':
        include __DIR__ . '/pages/donor/overview.php';
        break;
    case 'NGO':
        include __DIR__ . '/pages/ngo/overview.php';
        break;
    case 'VOLUNTEER':
        include __DIR__ . '/pages/volunteer/overview.php';
        break;
    case 'ADMIN':
        include __DIR__ . '/pages/admin/overview.php';
        break;
    default:
        session_destroy();
        header('Location: /login.php');
        exit();
}