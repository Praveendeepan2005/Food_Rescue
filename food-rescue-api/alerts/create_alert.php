<?php
// =============================================================
// alerts/create_alert.php
// Create Food Alert API (DONOR only)
//
// Method : POST
// URL    : http://localhost/food-rescue-api/alerts/create_alert.php
//
// Request Body (JSON):
// {
//   "donor_id"    : 1,
//   "food_type"   : "Rice and Curry",
//   "quantity"    : "15 kg",
//   "expiry_time" : "2025-12-31 20:00:00",
//   "latitude"    : 12.9716,
//   "longitude"   : 77.5946
// }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../notifications/send_notification.php';

setCORSHeaders();
requireMethod('POST');

// -----------------------------------------------------------
// 1. Parse and validate required fields
// -----------------------------------------------------------
$input = getRequestBody();
requireFields($input, ['donor_id', 'food_type', 'quantity', 'expiry_time', 'latitude', 'longitude']);

$donorId    = (int)$input['donor_id'];
$foodType   = trim($input['food_type']);
$quantity   = trim($input['quantity']);
$expiryTime = trim($input['expiry_time']); // Expected: "YYYY-MM-DD HH:MM:SS"
$latitude   = (float)$input['latitude'];
$longitude  = (float)$input['longitude'];

// Validate expiry_time format (YYYY-MM-DD HH:MM:SS)
if (!DateTime::createFromFormat('Y-m-d H:i:s', $expiryTime)) {
    sendError('Invalid expiry_time format. Use: YYYY-MM-DD HH:MM:SS', 422);
}

// Reject alerts with expiry time in the past
if (strtotime($expiryTime) <= time()) {
    sendError('expiry_time must be a future date and time.', 422);
}

// Basic coordinate range validation
if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    sendError('Invalid latitude or longitude values.', 422);
}

// -----------------------------------------------------------
// 2. Connect and verify donor role
// -----------------------------------------------------------
$conn = getDBConnection();

$roleSql  = 'SELECT role, name, device_token FROM users WHERE user_id = :id';
$roleStmt = oci_parse($conn, $roleSql);
oci_bind_by_name($roleStmt, ':id', $donorId);

if (!oci_execute($roleStmt)) {
    $err = oci_error($roleStmt);
    error_log('Role check error: ' . $err['message']);
    sendError('Failed to verify user. Please try again.', 500);
}

$donor = oci_fetch_assoc($roleStmt);
oci_free_statement($roleStmt);

if (!$donor) {
    sendError('Donor not found. Invalid donor_id.', 404);
}

// Only DONOR role can create food alerts
if ($donor['ROLE'] !== 'DONOR') {
    sendError('Access denied. Only users with DONOR role can create food alerts.', 403);
}

// -----------------------------------------------------------
// 3. Insert food alert into Oracle DB
//    Using TO_DATE() to convert the string to Oracle DATE type
// -----------------------------------------------------------
$insertSql = "
    INSERT INTO food_alerts (donor_id, food_type, quantity, expiry_time, latitude, longitude, status)
    VALUES (:donor_id, :food_type, :quantity, TO_DATE(:expiry_time, 'YYYY-MM-DD HH24:MI:SS'), :latitude, :longitude, 'AVAILABLE')
";

$stmt = oci_parse($conn, $insertSql);
oci_bind_by_name($stmt, ':donor_id',    $donorId);
oci_bind_by_name($stmt, ':food_type',   $foodType);
oci_bind_by_name($stmt, ':quantity',    $quantity);
oci_bind_by_name($stmt, ':expiry_time', $expiryTime);
oci_bind_by_name($stmt, ':latitude',    $latitude);
oci_bind_by_name($stmt, ':longitude',   $longitude);

if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($stmt);
    error_log('Create alert insert error: ' . $err['message']);
    oci_rollback($conn);
    sendError('Failed to create food alert. Please try again.', 500);
}

// Commit the insert
oci_commit($conn);
oci_free_statement($stmt);

// -----------------------------------------------------------
// 4. Fetch the new alert_id (Oracle IDENTITY columns auto-generate it)
// -----------------------------------------------------------
$fetchSql  = '
    SELECT alert_id, created_at
    FROM   food_alerts
    WHERE  donor_id = :donor_id
      AND  food_type = :food_type
      AND  created_at = (SELECT MAX(created_at) FROM food_alerts WHERE donor_id = :donor_id2)
';
$fetchStmt = oci_parse($conn, $fetchSql);
oci_bind_by_name($fetchStmt, ':donor_id',  $donorId);
oci_bind_by_name($fetchStmt, ':food_type', $foodType);
oci_bind_by_name($fetchStmt, ':donor_id2', $donorId);
oci_execute($fetchStmt);
$newAlert = oci_fetch_assoc($fetchStmt);
oci_free_statement($fetchStmt);

$newAlertId = $newAlert ? (int)$newAlert['ALERT_ID'] : null;

// -----------------------------------------------------------
// 5. Notify nearby NGOs / Volunteers via FCM
//    Fetch all users with NGO or VOLUNTEER role who have device tokens
// -----------------------------------------------------------
$notifSql  = "SELECT device_token, name FROM users WHERE role IN ('NGO','VOLUNTEER') AND device_token IS NOT NULL";
$notifStmt = oci_parse($conn, $notifSql);
oci_execute($notifStmt);

while ($recipient = oci_fetch_assoc($notifStmt)) {
    sendPushNotification(
        $recipient['DEVICE_TOKEN'],
        '🍱 New Food Available Nearby!',
        $donor['NAME'] . ' is offering: ' . $foodType . ' (' . $quantity . '). Expires at: ' . $expiryTime
    );
}
oci_free_statement($notifStmt);

// -----------------------------------------------------------
// 6. Return success response
// -----------------------------------------------------------
sendSuccess([
    'alert_id'    => $newAlertId,
    'donor_id'    => $donorId,
    'food_type'   => $foodType,
    'quantity'    => $quantity,
    'expiry_time' => $expiryTime,
    'latitude'    => $latitude,
    'longitude'   => $longitude,
    'status'      => 'AVAILABLE',
    'created_at'  => $newAlert ? $newAlert['CREATED_AT'] : date('Y-m-d H:i:s'),
], 'Food alert created successfully. NGOs and volunteers have been notified.', 201);
