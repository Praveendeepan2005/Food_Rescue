<?php
// =============================================================
// ngo/accept_donation.php
// NGO accepts an available donation
// Method: POST | { ngo_id, alert_id }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['ngo_id', 'alert_id']);

$ngoId = (int) $input['ngo_id'];
$alertId = (int) $input['alert_id'];

$conn = getDBConnection();

// Transaction: Check availability and update
$checkSql = "SELECT status, assigned_ngo_id FROM food_alerts WHERE alert_id = :aid FOR UPDATE";
$s1 = oci_parse($conn, $checkSql);
oci_bind_by_name($s1, ':aid', $alertId);
oci_execute($s1, OCI_NO_AUTO_COMMIT);
$row = oci_fetch_assoc($s1);

if (!$row) {
    oci_rollback($conn);
    sendError('Donation alert not found.', 404);
}

// If already claimed by THIS NGO, return success
if ($row['STATUS'] === 'CLAIMED' && (int) $row['ASSIGNED_NGO_ID'] === $ngoId) {
    oci_commit($conn);
    sendSuccess(null, 'Special: You have already accepted this donation.');
}

// Check availability
if ($row['STATUS'] !== 'AVAILABLE' && $row['STATUS'] !== 'NGO_ASSIGNED' && $row['STATUS'] !== 'PENDING') {
    oci_rollback($conn);
    sendError('Alert is no longer available. Current status: ' . $row['STATUS'], 409);
}

// If it was assigned specifically to another NGO, prevent this NGO from claiming it
if (
    ($row['STATUS'] === 'NGO_ASSIGNED' || $row['STATUS'] === 'CLAIMED') &&
    (int) $row['ASSIGNED_NGO_ID'] !== 0 && (int) $row['ASSIGNED_NGO_ID'] !== $ngoId
) {
    oci_rollback($conn);
    sendError('This donation mission is already assigned to or claimed by another NGO.', 403);
}


$updateSql = "UPDATE food_alerts SET status = 'CLAIMED', delivery_status = 'PENDING', assigned_ngo_id = :nid WHERE alert_id = :aid";
$s2 = oci_parse($conn, $updateSql);
oci_bind_by_name($s2, ':nid', $ngoId);
oci_bind_by_name($s2, ':aid', $alertId);

if (oci_execute($s2, OCI_NO_AUTO_COMMIT)) {
    oci_commit($conn);
    sendSuccess(null, 'Donation accepted successfully.');
} else {
    oci_rollback($conn);
    sendError('Failed to accept donation.', 500);
}
