<?php
// =============================================================
// ngo/get_volunteers.php
// List all active volunteers
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['ngo_id']))
    sendError('ngo_id is required', 422);
$ngoId = (int) $_GET['ngo_id'];
$alertId = isset($_GET['alert_id']) ? (int) $_GET['alert_id'] : null;

$conn = getDBConnection();

$alertLat = null;
$alertLng = null;

if ($alertId) {
    $alertSql = "SELECT latitude, longitude FROM food_alerts WHERE alert_id = :id";
    $aStmt = oci_parse($conn, $alertSql);
    oci_bind_by_name($aStmt, ':id', $alertId);
    oci_execute($aStmt);
    $alert = oci_fetch_assoc($aStmt);
    oci_free_statement($aStmt);
    if ($alert) {
        $alertLat = (float) $alert['LATITUDE'];
        $alertLng = (float) $alert['LONGITUDE'];
    }
}

$sql = "SELECT user_id, name, email, phone, status, latitude, longitude, city, average_rating,
       (SELECT COUNT(*) FROM claims c WHERE c.volunteer_id = users.user_id AND c.status NOT IN ('DELIVERED', 'CANCELLED')) as active_deliveries";
if ($alertLat !== null) {
    $radLatA = $alertLat * 0.0174532925;
    $radLngA = $alertLng * 0.0174532925;
    $cosLatA = cos($radLatA);
    $sinLatA = sin($radLatA);

    $sql .= ", ROUND(6371 * acos(
        :cos_lat_a * cos(latitude * 0.0174532925) * 
        cos((longitude * 0.0174532925) - :rad_lng_a) + 
        :sin_lat_a * sin(latitude * 0.0174532925)
    ), 2) as distance_km";
}
$sql .= " FROM users WHERE role = 'VOLUNTEER' AND status = 'ACTIVE'";

// Primary sorting: Workload (Fewest first), distance (Closer first)
if ($alertLat !== null) {
    $sql .= " ORDER BY active_deliveries ASC, distance_km ASC";
} else {
    $sql .= " ORDER BY active_deliveries ASC, name ASC";
}

$stmt = oci_parse($conn, $sql);
if ($alertLat !== null) {
    oci_bind_by_name($stmt, ':cos_lat_a', $cosLatA);
    oci_bind_by_name($stmt, ':rad_lng_a', $radLngA);
    oci_bind_by_name($stmt, ':sin_lat_a', $sinLatA);
}

oci_execute($stmt);
$vols = [];
while ($row = oci_fetch_assoc($stmt))
    $vols[] = $row;
sendSuccess(['volunteers' => $vols]);

