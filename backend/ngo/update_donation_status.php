<?php
// =============================================================
// ngo/update_donation_status.php
// NGO updates status (Picked Up, Completed, etc.)
// Method: POST | { alert_id, status }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'status']);

$alertId = (int) $input['alert_id'];
$status = strtoupper(trim($input['status'])); // CLAIMED, COMPLETED

$conn = getDBConnection();
$sql = "UPDATE food_alerts SET status = :status WHERE alert_id = :aid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':status', $status);
oci_bind_by_name($stmt, ':aid', $alertId);

if (oci_execute($stmt)) {
    oci_commit($conn);
    sendSuccess(null, "Status updated to $status.");
} else {
    sendError('Failed to update status.', 500);
}
