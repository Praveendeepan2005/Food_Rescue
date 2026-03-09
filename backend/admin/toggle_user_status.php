<?php
// =============================================================
// admin/toggle_user_status.php
// Toggle user status between ACTIVE and SUSPENDED
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$body = getRequestBody();
if (!isset($body['user_id']) || !isset($body['new_status'])) {
    sendError('user_id and new_status are required', 422);
}

$userId = (int) $body['user_id'];
$newStatus = strtoupper($body['new_status']);

if (!in_array($newStatus, ['ACTIVE', 'SUSPENDED'])) {
    sendError('Invalid status', 400);
}

$conn = getDBConnection();

$sql = "UPDATE users SET status = :status WHERE user_id = :id AND role != 'ADMIN'";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':status', $newStatus);
oci_bind_by_name($stmt, ':id', $userId);

if (oci_execute($stmt)) {
    if (oci_num_rows($stmt) > 0) {
        sendSuccess([], "User status updated to $newStatus");
    } else {
        sendError('User not found or cannot modify ADMIN', 404);
    }
    oci_free_statement($stmt);
} else {
    $err = oci_error($stmt);
    sendError('Failed to update status: ' . $err['message'], 500);
}
