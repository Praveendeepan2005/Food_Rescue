<?php
// =============================================================
// admin/get_all_active_tracking.php
// Fetches all active delivery locations for the admin map
// Method: GET
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

// Fetch all missions that are in 'ON_DELIVERY' or 'PICKED_UP' or 'ACCEPTED' state
$sql = "
    SELECT 
        fa.alert_id, fa.food_type, fa.status, fa.delivery_status,
        fa.latitude as donor_lat, fa.longitude as donor_lng,
        fa.vol_lat as volunteer_lat, fa.vol_lng as volunteer_lng,
        ngo.latitude as ngo_lat, ngo.longitude as ngo_lng,
        ngo.name as ngo_name,
        v.name as volunteer_name
    FROM food_alerts fa
    LEFT JOIN users ngo ON fa.assigned_ngo_id = ngo.user_id
    LEFT JOIN claims c ON fa.alert_id = c.alert_id AND c.status != 'CANCELLED'
    LEFT JOIN users v ON c.volunteer_id = v.user_id
    WHERE fa.status IN ('ACCEPTED', 'PICKED_UP', 'ON_DELIVERY')
";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$missions = [];
while ($row = oci_fetch_assoc($stmt)) {
    $missions[] = $row;
}

sendSuccess(['missions' => $missions]);
