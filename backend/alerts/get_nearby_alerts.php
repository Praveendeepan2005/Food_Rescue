<?php
// =============================================================
// alerts/get_nearby_alerts.php
// Get Nearby Available Food Alerts API
//
// Method : GET
// URL    : http://localhost/food-rescue-api/alerts/get_nearby_alerts.php
//          ?latitude=12.9716&longitude=77.5946&radius=10
//
// Query Parameters:
//   latitude  (required) - Viewer's current latitude
//   longitude (required) - Viewer's current longitude
//   radius    (optional) - Search radius in kilometers (default: 10 km)
//
// Uses the Haversine Formula to calculate the straight-line distance
// between two geographic coordinates (great-circle distance).
//
// Haversine Formula:
//   a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)
//   c = 2 × atan2(√a, √(1−a))
//   distance = R × c    (R = 6371 km, Earth's radius)
//
// We pre-filter in SQL using a bounding box for performance,
// then apply exact Haversine filtering in PHP.
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

// -----------------------------------------------------------
// 1. Validate query parameters
// -----------------------------------------------------------
if (!isset($_GET['latitude']) || !isset($_GET['longitude'])) {
    sendError('latitude and longitude query parameters are required.', 422);
}

$userLat  = (float)$_GET['latitude'];
$userLon  = (float)$_GET['longitude'];
$radius   = isset($_GET['radius']) ? (float)$_GET['radius'] : 10.0; // Default 10 km

// Validate coordinate ranges
if ($userLat < -90 || $userLat > 90 || $userLon < -180 || $userLon > 180) {
    sendError('Invalid latitude or longitude values.', 422);
}

if ($radius <= 0 || $radius > 100) {
    sendError('Radius must be between 1 and 100 km.', 422);
}

// -----------------------------------------------------------
// 2. Calculate bounding box for a preliminary SQL filter
//    1 degree of latitude ≈ 111 km
//    This dramatically reduces the result set before Haversine
// -----------------------------------------------------------
$latDelta = $radius / 111.0;
$lonDelta = $radius / (111.0 * cos(deg2rad($userLat)));

$minLat = $userLat - $latDelta;
$maxLat = $userLat + $latDelta;
$minLon = $userLon - $lonDelta;
$maxLon = $userLon + $lonDelta;

// -----------------------------------------------------------
// 3. Connect and query AVAILABLE alerts within bounding box
//    Also joins users table to get donor name and phone
//    Auto-expires alerts past their expiry_time
// -----------------------------------------------------------
$conn = getDBConnection();

// Mark expired alerts before fetching (auto-expire logic)
$expireSql  = "UPDATE food_alerts SET status = 'EXPIRED' WHERE status = 'AVAILABLE' AND expiry_time < SYSDATE";
$expireStmt = oci_parse($conn, $expireSql);
oci_execute($expireStmt);
oci_free_statement($expireStmt);

$sql = "
    SELECT
        fa.alert_id,
        fa.donor_id,
        u.name          AS donor_name,
        u.phone         AS donor_phone,
        fa.food_type,
        fa.quantity,
        TO_CHAR(fa.expiry_time, 'YYYY-MM-DD HH24:MI:SS') AS expiry_time,
        fa.latitude,
        fa.longitude,
        fa.status,
        TO_CHAR(fa.created_at, 'YYYY-MM-DD HH24:MI:SS')  AS created_at
    FROM   food_alerts fa
    JOIN   users       u
        ON fa.donor_id = u.user_id
    WHERE  fa.status    = 'AVAILABLE'
      AND  fa.expiry_time >= SYSDATE
      AND  fa.latitude  BETWEEN :min_lat AND :max_lat
      AND  fa.longitude BETWEEN :min_lon AND :max_lon
    ORDER BY fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':min_lat', $minLat);
oci_bind_by_name($stmt, ':max_lat', $maxLat);
oci_bind_by_name($stmt, ':min_lon', $minLon);
oci_bind_by_name($stmt, ':max_lon', $maxLon);

if (!oci_execute($stmt)) {
    $err = oci_error($stmt);
    error_log('Get nearby alerts error: ' . $err['message']);
    sendError('Failed to fetch nearby alerts. Please try again.', 500);
}

// -----------------------------------------------------------
// 4. Apply exact Haversine filtering and build result set
// -----------------------------------------------------------
$alerts = [];
$earthRadiusKm = 6371;

while ($row = oci_fetch_assoc($stmt)) {
    $alertLat = (float)$row['LATITUDE'];
    $alertLon = (float)$row['LONGITUDE'];

    // Haversine formula
    $dLat     = deg2rad($alertLat - $userLat);
    $dLon     = deg2rad($alertLon - $userLon);
    $a        = sin($dLat / 2) ** 2
              + cos(deg2rad($userLat)) * cos(deg2rad($alertLat)) * sin($dLon / 2) ** 2;
    $c        = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadiusKm * $c;   // in km, rounded to 2 decimal places

    // Only include alerts within the specified radius
    if ($distance <= $radius) {
        $alerts[] = [
            'alert_id'     => (int)$row['ALERT_ID'],
            'donor_id'     => (int)$row['DONOR_ID'],
            'donor_name'   => $row['DONOR_NAME'],
            'donor_phone'  => $row['DONOR_PHONE'],
            'food_type'    => $row['FOOD_TYPE'],
            'quantity'     => $row['QUANTITY'],
            'expiry_time'  => $row['EXPIRY_TIME'],
            'latitude'     => (float)$row['LATITUDE'],
            'longitude'    => (float)$row['LONGITUDE'],
            'status'       => $row['STATUS'],
            'created_at'   => $row['CREATED_AT'],
            'distance_km'  => round($distance, 2), // Helpful for UI display
        ];
    }
}

oci_free_statement($stmt);

// Sort final results by distance (closest first)
usort($alerts, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

// -----------------------------------------------------------
// 5. Return results
// -----------------------------------------------------------
sendSuccess([
    'search_location' => [
        'latitude'  => $userLat,
        'longitude' => $userLon,
        'radius_km' => $radius,
    ],
    'total_found' => count($alerts),
    'alerts'      => $alerts,
], count($alerts) > 0
    ? count($alerts) . ' food alert(s) found within ' . $radius . ' km.'
    : 'No food alerts found near your location. Try increasing the radius.'
);
