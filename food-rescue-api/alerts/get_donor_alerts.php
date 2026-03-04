<?php
// =============================================================
// alerts/get_donor_alerts.php
// Get Alerts for a specific Donor
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['donor_id'])) {
    sendError('donor_id is required', 422);
}

$donorId = (int) $_GET['donor_id'];
$conn = getDBConnection();

$sql = "
    SELECT 
        alert_id, food_type, quantity, status,
        TO_CHAR(expiry_time, 'YYYY-MM-DD HH24:MI') as expiry_time,
        TO_CHAR(created_at, 'YYYY-MM-DD') as created_at
    FROM food_alerts
    WHERE donor_id = :donor_id
    ORDER BY created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':donor_id', $donorId);
oci_execute($stmt);

$alerts = [];
while ($row = oci_fetch_assoc($stmt)) {
    $alerts[] = $row;
}
oci_free_statement($stmt);

sendSuccess(['alerts' => $alerts], 'Donor alerts fetched.');
