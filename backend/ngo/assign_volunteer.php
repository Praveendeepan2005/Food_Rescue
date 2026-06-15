<?php
// =============================================================
// ngo/assign_volunteer.php
// NGO assigns a volunteer and recipient location to an accepted mission
// Method: POST | { alert_id, volunteer_id, ngo_id, orphanage_id }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id', 'ngo_id', 'orphanage_id']);

$alertId = (int) $input['alert_id'];
$volId = (int) $input['volunteer_id'];
$ngoId = (int) $input['ngo_id'];
$orpId = (int) $input['orphanage_id'];

$conn = getDBConnection();

// 1. Fetch Pickup and Delivery Addresses
$dataSql = "
    SELECT fa.pickup_address, o.address as delivery_address
    FROM food_alerts fa, orphanages o
    WHERE fa.alert_id = :aid AND o.orphanage_id = :oid
";
$stmtData = oci_parse($conn, $dataSql);
oci_bind_by_name($stmtData, ':aid', $alertId);
oci_bind_by_name($stmtData, ':oid', $orpId);
oci_execute($stmtData);
$details = oci_fetch_assoc($stmtData);

if (!$details) {
    sendError('Invalid mission or orphanage selection.', 422);
}

$pickupAddr = $details['PICKUP_ADDRESS'];
$deliveryAddr = $details['DELIVERY_ADDRESS'];

// 2. Insert into deliveries table (New Project Table)
$delSql = "INSERT INTO deliveries (donation_id, ngo_id, volunteer_id, orphanage_id, pickup_address, delivery_address, status, assigned_at)
           VALUES (:aid, :nid, :vid, :oid, :paddr, :daddr, 'ASSIGNED', SYSDATE)";
$stmtDel = oci_parse($conn, $delSql);
oci_bind_by_name($stmtDel, ':aid', $alertId);
oci_bind_by_name($stmtDel, ':nid', $ngoId);
oci_bind_by_name($stmtDel, ':vid', $volId);
oci_bind_by_name($stmtDel, ':oid', $orpId);
oci_bind_by_name($stmtDel, ':paddr', $pickupAddr);
oci_bind_by_name($stmtDel, ':daddr', $deliveryAddr);

if (!oci_execute($stmtDel, OCI_NO_AUTO_COMMIT)) {
    $e = oci_error($stmtDel);
    sendError('Failed to create delivery record: ' . $e['message'], 500);
}

// 3. Update Existing food_alerts status
$upFa = oci_parse($conn, "UPDATE food_alerts SET assigned_vol_id = :vid, status = 'VOLUNTEER_ASSIGNED', assigned_time = SYSDATE WHERE alert_id = :aid");
oci_bind_by_name($upFa, ':vid', $volId);
oci_bind_by_name($upFa, ':aid', $alertId);
oci_execute($upFa, OCI_NO_AUTO_COMMIT);

// 4. Update/Insert Claims (for volunteer dashboard visibility in existing logic)
$checkClaim = oci_parse($conn, "SELECT count(*) as cnt FROM claims WHERE alert_id = :aid");
oci_bind_by_name($checkClaim, ':aid', $alertId);
oci_execute($checkClaim);
$cRow = oci_fetch_assoc($checkClaim);

if ((int)$cRow['CNT'] > 0) {
    $upClaim = oci_parse($conn, "UPDATE claims SET volunteer_id = :vid, status = 'ASSIGNED' WHERE alert_id = :aid");
    oci_bind_by_name($upClaim, ':vid', $volId);
    oci_bind_by_name($upClaim, ':aid', $alertId);
    oci_execute($upClaim, OCI_NO_AUTO_COMMIT);
} else {
    $insClaim = oci_parse($conn, "INSERT INTO claims (alert_id, volunteer_id, status, claimed_at) VALUES (:aid, :vid, 'ASSIGNED', SYSDATE)");
    oci_bind_by_name($insClaim, ':aid', $alertId);
    oci_bind_by_name($insClaim, ':vid', $volId);
    oci_execute($insClaim, OCI_NO_AUTO_COMMIT);
}

oci_commit($conn);
sendSuccess(null, "Volunteer assigned to orphanage delivery successfully!");
