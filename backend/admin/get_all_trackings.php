<?php
// =============================================================
// admin/get_all_trackings.php
// Fetch all active deliveries for monitoring
// Method: GET
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

// Update SQL to include new statuses and handle case sensitivity
$sql = "SELECT fa.*, 
               u_donor.name as donor_name,
               u_ngo.name as ngo_name,
               u_vol.name as vol_name
        FROM food_alerts fa
        JOIN users u_donor ON fa.donor_id = u_donor.user_id
        JOIN users u_ngo ON fa.assigned_ngo_id = u_ngo.user_id
        LEFT JOIN users u_vol ON fa.assigned_vol_id = u_vol.user_id
        WHERE fa.delivery_status != 'Delivered' 
        AND fa.status IN ('CLAIMED', 'COMPLETED')
        ORDER BY fa.created_at DESC";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$results = [];
while ($row = oci_fetch_assoc($stmt)) {
    $results[] = $row;
}

sendSuccess($results);
