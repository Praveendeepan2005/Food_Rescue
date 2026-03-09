<?php
// =============================================================
// admin/get_all_deliveries.php
// Get all active deliveries for admin monitoring
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

$sql = "
    SELECT fa.alert_id, fa.food_type, fa.quantity, fa.delivery_status as status, fa.status as fa_status,
           d.name as donor_name, fa.latitude as donor_lat, fa.longitude as donor_lng, fa.pickup_address,
           n.name as ngo_name, n.latitude as ngo_lat, n.longitude as ngo_lng,
           v.name as volunteer_name, v.latitude as vol_lat, v.longitude as vol_lng,
           fa.created_at
    FROM food_alerts fa
    JOIN users d ON fa.donor_id = d.user_id
    LEFT JOIN users n ON fa.assigned_ngo_id = n.user_id
    LEFT JOIN users v ON fa.assigned_vol_id = v.user_id
    WHERE fa.status != 'COMPLETED' AND fa.assigned_ngo_id IS NOT NULL
    ORDER BY fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$deliveries = [];
while ($row = oci_fetch_assoc($stmt)) {
    $deliveries[] = $row;
}
oci_free_statement($stmt);

sendSuccess(['deliveries' => $deliveries]);
