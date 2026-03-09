<?php
// =============================================================
// ngo/get_volunteer_location.php
// Fetches the latest known location of a volunteer for a specific mission
// Method: GET | ?alert_id=XXX
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['alert_id'])) {
    sendError('alert_id is required', 422);
}

$alertId = (int) $_GET['alert_id'];
$conn = getDBConnection();

// Fetch Donor location, Volunteer location, and NGO location
$sql = "
    SELECT 
        fa.alert_id, fa.food_type, fa.status, fa.delivery_status,
        fa.latitude as donor_lat, fa.longitude as donor_lng,
        fa.vol_lat as volunteer_lat, fa.vol_lng as volunteer_lng,
        ngo.latitude as ngo_lat, ngo.longitude as ngo_lng,
        v.name as volunteer_name, v.phone as volunteer_phone
    FROM food_alerts fa
    LEFT JOIN users ngo ON fa.assigned_ngo_id = ngo.user_id
    LEFT JOIN claims c ON fa.alert_id = c.alert_id AND c.status != 'CANCELLED'
    LEFT JOIN users v ON c.volunteer_id = v.user_id
    WHERE fa.alert_id = :aid
    FETCH FIRST 1 ROW ONLY
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':aid', $alertId);
oci_execute($stmt);

$data = oci_fetch_assoc($stmt);

if (!$data) {
    sendError('Mission not found.', 404);
}

sendSuccess($data);
