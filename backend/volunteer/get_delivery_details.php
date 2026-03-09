<?php
// =============================================================
// volunteer/get_delivery_details.php
// Fetch tracking details for a specific donation
// Method: GET | ?alert_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$alertId = isset($_GET['alert_id']) ? (int) $_GET['alert_id'] : 0;

if (!$alertId) {
    sendError('Alert ID is required.', 400);
}

$conn = getDBConnection();

$sql = "SELECT fa.*, 
               u_donor.name as donor_name, u_donor.phone as donor_phone,
               u_ngo.name as ngo_name, u_ngo.phone as ngo_phone, u_ngo.address as ngo_address,
               u_vol.name as vol_name, u_vol.user_id as assigned_vol_id_from_claim
        FROM food_alerts fa
        JOIN users u_donor ON fa.donor_id = u_donor.user_id
        LEFT JOIN users u_ngo ON fa.assigned_ngo_id = u_ngo.user_id
        LEFT JOIN claims c ON fa.alert_id = c.alert_id AND c.status != 'CANCELLED'
        LEFT JOIN users u_vol ON c.volunteer_id = u_vol.user_id
        WHERE fa.alert_id = :aid";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':aid', $alertId);
oci_execute($stmt);

$row = oci_fetch_assoc($stmt);

if (!$row) {
    sendError('Delivery details not found.', 404);
}

// Ensure NGO coordinates are synced if not already set
if (!$row['NGO_LAT'] && $row['ASSIGNED_NGO_ID']) {
    $ngoLat = $row['NGO_LAT'];
    $ngoLng = $row['NGO_LNG'];
    // Default to the user's registered location if track columns are empty
    $ngoQuery = oci_parse($conn, "SELECT latitude, longitude FROM users WHERE user_id = :nid");
    oci_bind_by_name($ngoQuery, ':nid', $row['ASSIGNED_NGO_ID']);
    oci_execute($ngoQuery);
    $ngoRow = oci_fetch_assoc($ngoQuery);
    if ($ngoRow) {
        $row['NGO_LAT'] = $ngoRow['LATITUDE'];
        $row['NGO_LNG'] = $ngoRow['LONGITUDE'];
    }
}

sendSuccess($row);
