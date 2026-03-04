<?php
// =============================================================
// admin/manage_alerts.php
// Admin Alert Management API
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

$sql = "
    SELECT 
        fa.alert_id, fa.food_type, fa.quantity, fa.status,
        TO_CHAR(fa.expiry_time, 'YYYY-MM-DD HH24:MI') as expiry,
        u.name as donor_name
    FROM food_alerts fa
    JOIN users u ON fa.donor_id = u.user_id
    ORDER BY fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$alerts = [];
while ($row = oci_fetch_assoc($stmt)) {
    $alerts[] = $row;
}
oci_free_statement($stmt);

sendSuccess(['alerts' => $alerts], 'Alerts fetched for admin.');
