<?php
// =============================================================
// admin/manage_users.php
// Admin User Management API
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if ($method === 'GET') {
    // List all users
    $sql = "SELECT user_id, name, email, role, city, status, TO_CHAR(created_at, 'YYYY-MM-DD') as joined FROM users ORDER BY created_at DESC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $users = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $users[] = $row;
    }
    oci_free_statement($stmt);
    sendSuccess(['users' => $users], 'Users fetched.');
} elseif ($method === 'POST') {
    // Update user status
    $input = getRequestBody();
    requireFields($input, ['user_id', 'status']);

    $userId = (int) $input['user_id'];
    $status = strtoupper(trim($input['status']));

    if (!in_array($status, ['ACTIVE', 'SUSPENDED'])) {
        sendError('Invalid status. Use ACTIVE or SUSPENDED.', 422);
    }

    $sql = "UPDATE users SET status = :status WHERE user_id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':status', $status);
    oci_bind_by_name($stmt, ':id', $userId);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        sendSuccess(null, 'User status updated to ' . $status);
    } else {
        $err = oci_error($stmt);
        sendError('Failed to update user: ' . $err['message'], 500);
    }
    oci_free_statement($stmt);
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($input['action']) && $input['action'] === 'DELETE')) {
    // Note: If using true DELETE method, input might be raw or in query. 
    // We'll support both true DELETE and POST with action=DELETE for flexibility.
    $input = ($method === 'DELETE') ? $_GET : getRequestBody();
    if (empty($input))
        $input = getRequestBody(); // fallback

    requireFields($input, ['user_id']);
    $userId = (int) $input['user_id'];

    // Protection: Don't allow deleting the user's self or admins if we had session data, 
    // but here we just check role for safety.
    $checkSql = "SELECT role FROM users WHERE user_id = :id";
    $checkStmt = oci_parse($conn, $checkSql);
    oci_bind_by_name($checkStmt, ':id', $userId);
    oci_execute($checkStmt);
    $user = oci_fetch_assoc($checkStmt);

    if (!$user) {
        sendError('User not found.', 404);
    }
    if ($user['ROLE'] === 'ADMIN') {
        sendError('Administrator accounts cannot be deleted.', 403);
    }

    $sql = "DELETE FROM users WHERE user_id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $userId);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        sendSuccess(null, 'User account permanently removed.');
    } else {
        $err = oci_error($stmt);
        sendError('Database error: ' . $err['message'], 500);
    }
    oci_free_statement($stmt);
} else {
    sendError('Method Not Allowed', 405);
}
