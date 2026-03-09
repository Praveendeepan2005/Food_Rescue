<?php
/**
 * Food Rescue – Session Sync: Set Session
 * auth/set_session.php
 *
 * Called by app.js after a successful login.
 * Stores the user object into $_SESSION so PHP pages can validate auth.
 *
 * Method : POST
 * Body   : { "user": { user_id, name, email, role, ... } }
 *
 * Response: { "success": true }
 */

// Allow CORS for the same origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user']) || !isset($input['user']['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user data']);
    exit();
}

// Store full user object in session
$_SESSION['fr_user'] = $input['user'];
$_SESSION['fr_user_id'] = (int) $input['user']['user_id'];
$_SESSION['fr_role'] = $input['user']['role'] ?? '';

echo json_encode([
    'success' => true,
    'message' => 'Session established',
    'session_id' => session_id()
]);
