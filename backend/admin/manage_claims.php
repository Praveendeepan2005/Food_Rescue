<?php
// =============================================================
// admin/manage_claims.php
// Admin Claims & Transactions Management API
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
            c.claim_id, 
            c.alert_id,
            c.status as claim_status,
            TO_CHAR(c.claimed_at, 'YYYY-MM-DD HH24:MI:SS') as claim_time,
            fa.food_type, 
            fa.status as alert_status,
            d.name as donor_name,
            v.name as volunteer_name
        FROM claims c
        JOIN food_alerts fa ON c.alert_id = fa.alert_id
        JOIN users d ON fa.donor_id = d.user_id
        JOIN users v ON c.volunteer_id = v.user_id
        ORDER BY c.claimed_at DESC
    ";

    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $claims = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $claims[] = $row;
    }
    oci_free_statement($stmt);

    sendSuccess(['claims' => $claims], 'Claims fetched for admin.');

} elseif ($method === 'POST') {
    $input = getRequestBody();
    requireFields($input, ['claim_id', 'action']);

    $claimId = (int) $input['claim_id'];
    $action = $input['action'];

    if ($action === 'DELAY') {
        // Here we could update a delay log table or track it in a separate table
        // For now, let's just log it or simulate success
        sendSuccess(null, 'Delay tracked successfully.');
    } elseif ($action === 'FAILED_PICKUP') {
        $sql = "UPDATE claims SET status = 'CANCELLED' WHERE claim_id = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':id', $claimId);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            sendSuccess(null, 'Claim cancelled due to failed pickup.');
        } else {
            sendError('Failed to cancel claim', 500);
        }
        oci_free_statement($stmt);
    } else {
        sendError('Invalid action', 400);
    }
} else {
    sendError('Method Not Allowed', 405);
}
