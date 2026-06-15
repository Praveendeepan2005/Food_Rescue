<?php
// =============================================================
// volunteer/get_delivery_detail.php
// Get details of a specific delivery claim for tracking
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['claim_id']) || !isset($_GET['volunteer_id'])) {
    sendError('claim_id and volunteer_id are required', 422);
}

$claimId = (int) $_GET['claim_id'];
$volId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

$sql = "
    SELECT c.claim_id, c.alert_id, fa.food_type, fa.quantity, fa.pickup_address, 
           d.name as donor_name, n.name as ngo_name, 
           c.status as claim_status, fa.status as alert_status,
           fa.latitude as donor_lat, fa.longitude as donor_lng,
           o.orphanage_name, o.address as delivery_address,
           o.latitude as orphanage_lat, o.longitude as orphanage_lng
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    LEFT JOIN deliveries dl ON fa.alert_id = dl.donation_id
    LEFT JOIN orphanages o ON dl.orphanage_id = o.orphanage_id
    WHERE c.claim_id = :cid AND c.volunteer_id = :vid
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':cid', $claimId);
oci_bind_by_name($stmt, ':vid', $volId);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$row) {
    sendError('Delivery not found or unauthorized', 404);
}

sendSuccess(['delivery' => $row]);
