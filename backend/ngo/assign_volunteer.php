<?php
// =============================================================
// ngo/assign_volunteer.php
// NGO assigns a volunteer to an accepted mission
// Method: POST | { alert_id, volunteer_id, ngo_id }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id', 'ngo_id']);

$alertId = (int) $input['alert_id'];
$volId = (int) $input['volunteer_id'];
$ngoId = (int) $input['ngo_id'];

$conn = getDBConnection();

// 1. Check if already assigned
$check = oci_parse($conn, "SELECT count(*) as cnt FROM claims WHERE alert_id = :aid AND status != 'CANCELLED'");
oci_bind_by_name($check, ':aid', $alertId);
oci_execute($check);
$row = oci_fetch_assoc($check);
if ((int) $row['CNT'] > 0)
    sendError('Volunteer already assigned to this mission.', 422);

// 2. Insert into claims
$sql = "INSERT INTO claims (alert_id, volunteer_id, status, claimed_at) 
        VALUES (:aid, :vid, 'ASSIGNED', SYSDATE)";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':aid', $alertId);
oci_bind_by_name($stmt, ':vid', $volId);

if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    // 3. Update food_alerts tracking linkage and status
    $upFa = oci_parse($conn, "UPDATE food_alerts SET assigned_vol_id = :vid, status = 'VOLUNTEER_ASSIGNED', assigned_time = SYSDATE WHERE alert_id = :aid");
    oci_bind_by_name($upFa, ':vid', $volId);
    oci_bind_by_name($upFa, ':aid', $alertId);
    oci_execute($upFa, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);
    sendSuccess(null, "Volunteer assigned successfully!");
} else {
    sendError('Failed to assign volunteer.', 500);
}

