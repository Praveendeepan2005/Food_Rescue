<?php
// =============================================================
// ngo/get_tracking_info.php
// Get delivery tracking details for an NGO
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['alert_id']) || !isset($_GET['ngo_id'])) {
    sendError('alert_id and ngo_id are required', 422);
}

$alertId = (int) $_GET['alert_id'];
$ngoId = (int) $_GET['ngo_id'];
$conn = getDBConnection();

$sql = "
    SELECT fa.alert_id, fa.food_type, fa.quantity, fa.delivery_status as status,
           d.name as donor_name, fa.latitude as donor_lat, fa.longitude as donor_lng, fa.pickup_address,
           n.name as ngo_name, n.latitude as ngo_lat, n.longitude as ngo_lng, n.address as ngo_address,
           v.name as volunteer_name, v.latitude as vol_lat, v.longitude as vol_lng, v.phone as vol_phone
    FROM food_alerts fa
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    LEFT JOIN users v ON fa.assigned_vol_id = v.user_id
    WHERE fa.alert_id = :aid AND fa.assigned_ngo_id = :nid
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':aid', $alertId);
oci_bind_by_name($stmt, ':nid', $ngoId);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$row) {
    sendError('Tracking details not found or unauthorized', 404);
}

sendSuccess(['tracking' => $row]);
