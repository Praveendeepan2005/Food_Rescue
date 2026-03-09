<?php
// =============================================================
// ngo/get_available_donations.php
// List all donations with status AVAILABLE
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['ngo_id']))
    sendError('ngo_id is required', 422);
$ngoId = (int) $_GET['ngo_id'];

$conn = getDBConnection();

// Fetch NGO coordinates AND city
$ngoSql = "SELECT latitude, longitude, city FROM users WHERE user_id = :id";
$nStmt = oci_parse($conn, $ngoSql);
oci_bind_by_name($nStmt, ':id', $ngoId);
oci_execute($nStmt);
$ngo = oci_fetch_assoc($nStmt);
oci_free_statement($nStmt);

if (!$ngo)
    sendError('NGO not found', 404);

$ngoLat = (float) ($ngo['LATITUDE'] ?? 0);
$ngoLng = (float) ($ngo['LONGITUDE'] ?? 0);
$ngoCity = $ngo['CITY'] ?? '';

// Pre-calculate for Haversine
$radLatN = $ngoLat * 0.0174532925;
$radLngN = $ngoLng * 0.0174532925;
$cosLatN = cos($radLatN);
$sinLatN = sin($radLatN);

$sql = "
    SELECT fa.alert_id, fa.food_type, fa.quantity, fa.pickup_address, fa.city,
           fa.category, fa.preparation_time, fa.contact_number, fa.special_instructions,
           u.name as donor_name, TO_CHAR(fa.expiry_time, 'DD Mon YYYY HH24:MI') as expiry,
           ROUND(6371 * acos(
               GREATEST(-1, LEAST(1, :cos_lat_n * cos(fa.latitude * 0.0174532925) * 
               cos((fa.longitude * 0.0174532925) - :rad_lng_n) + 
               :sin_lat_n * sin(fa.latitude * 0.0174532925)))
           ), 2) as distance_km
    FROM food_alerts fa
    JOIN users u ON fa.donor_id = u.user_id
    WHERE (fa.status = 'NGO_ASSIGNED' AND fa.assigned_ngo_id = :ngo_id)
       OR ((fa.status = 'PENDING' OR fa.status = 'AVAILABLE') AND LOWER(fa.city) = LOWER(:ngo_city))
    ORDER BY distance_km ASC, fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':ngo_id', $ngoId);
oci_bind_by_name($stmt, ':ngo_city', $ngoCity);
oci_bind_by_name($stmt, ':cos_lat_n', $cosLatN);
oci_bind_by_name($stmt, ':rad_lng_n', $radLngN);
oci_bind_by_name($stmt, ':sin_lat_n', $sinLatN);

oci_execute($stmt);
$donations = [];
while ($row = oci_fetch_assoc($stmt))
    $donations[] = $row;
sendSuccess(['donations' => $donations]);

