<?php
// =============================================================
// admin/manage_alerts.php
// Admin Alert Management API
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
// Allow GET and POST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

if ($method === 'GET') {
    $sql = "
        SELECT 
            fa.alert_id, fa.food_type, fa.quantity, fa.status,
            TO_CHAR(fa.created_at, 'YYYY-MM-DD HH24:MI') as created_at,
            TO_CHAR(fa.expiry_time, 'YYYY-MM-DD HH24:MI') as expiry,
            u.name as donor_name,
            u.email as donor_email,
            u.phone as donor_phone
        FROM food_alerts fa
        JOIN users u ON fa.donor_id = u.user_id
        ORDER BY fa.created_at DESC
    ";

    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $alerts = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $alerts[] = $row;
    }
    oci_free_statement($stmt);

    sendSuccess(['alerts' => $alerts], 'Alerts fetched for admin.');

} elseif ($method === 'POST') {
    $input = getRequestBody();
    requireFields($input, ['alert_id', 'action']);

    $alertId = (int) $input['alert_id'];
    $action = $input['action'];

    if ($action === 'DELETE') {
        $sql = "DELETE FROM food_alerts WHERE alert_id = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id', $alertId);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            sendSuccess(null, 'Alert deleted successfully.');
        } else {
            sendError('Failed to delete alert', 500);
        }
        oci_free_statement($stmt);

    } elseif ($action === 'FORCE_CLOSE') {
        $sql = "UPDATE food_alerts SET status = 'EXPIRED' WHERE alert_id = :id AND status IN ('AVAILABLE', 'CLAIMED')";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id', $alertId);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            sendSuccess(null, 'Alert force closed (expired).');
        } else {
            sendError('Failed to close alert', 500);
        }
        oci_free_statement($stmt);
    } else {
        sendError('Invalid action', 400);
    }
} else {
    sendError('Method Not Allowed', 405);
}
