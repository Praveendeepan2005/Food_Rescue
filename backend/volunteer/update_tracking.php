<?php
// =============================================================
// volunteer/update_tracking.php
// Receives GPS location from volunteer and stores it
// Method: POST | { volunteer_id, donation_id, latitude, longitude, status }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['volunteer_id', 'donation_id', 'latitude', 'longitude']);

$volunteerId = (int) $input['volunteer_id'];
$donationId = (int) $input['donation_id'];
$lat = (float) $input['latitude'];
$lng = (float) $input['longitude'];
$status = $input['status'] ?? 'ON_DELIVERY';

$conn = getDBConnection();

// 1. Log the position in tracking table
$insertSql = "INSERT INTO volunteer_tracking 
    (volunteer_id, donation_id, latitude, longitude, status, updated_at) 
    VALUES (:vid, :did, :lat, :lng, :status, CURRENT_TIMESTAMP)";

$s1 = oci_parse($conn, $insertSql);
oci_bind_by_name($s1, ':vid', $volunteerId);
oci_bind_by_name($s1, ':did', $donationId);
oci_bind_by_name($s1, ':lat', $lat);
oci_bind_by_name($s1, ':lng', $lng);
oci_bind_by_name($s1, ':status', $status);

if (!oci_execute($s1)) {
    $e = oci_error($s1);
    sendError('Failed to update tracking: ' . $e['message'], 500);
}

// 2. Also update the main food_alerts table for quick dashboard visibility
$updateSql = "UPDATE food_alerts SET 
    vol_lat = :lat, 
    vol_lng = :lng, 
    delivery_status = :status,
    status = :status
    WHERE alert_id = :did";

$s2 = oci_parse($conn, $updateSql);
oci_bind_by_name($s2, ':lat', $lat);
oci_bind_by_name($s2, ':lng', $lng);
oci_bind_by_name($s2, ':status', $status);
oci_bind_by_name($s2, ':did', $donationId);

oci_execute($s2);

sendSuccess(null, 'Location updated successfully.');
