<?php
// =============================================================
// volunteer/get_live_tracking.php
// Get all active deliveries for a specific volunteer
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id'])) {
    sendError('Volunteer ID is required.', 422);
}
$volId = (int) $_GET['volunteer_id'];

$conn = getDBConnection();

$sql = "
    SELECT fa.alert_id, fa.food_type, fa.quantity, c.status, fa.status as fa_status,
           d.name as donor_name, fa.latitude as donor_lat, fa.longitude as donor_lng, fa.pickup_address,
           n.name as ngo_name,
           o.orphanage_name, o.latitude as orphanage_lat, o.longitude as orphanage_lng, o.address as delivery_address,
           v.name as volunteer_name, v.latitude as vol_lat, v.longitude as vol_lng,
           c.claim_id
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    LEFT JOIN users n ON fa.assigned_ngo_id = n.user_id
    LEFT JOIN users v ON c.volunteer_id = v.user_id
    LEFT JOIN deliveries dl ON fa.alert_id = dl.donation_id
    LEFT JOIN orphanages o ON dl.orphanage_id = o.orphanage_id
    WHERE c.volunteer_id = :vid 
      AND (
          UPPER(c.status) NOT IN ('COMPLETED', 'DELIVERED', 'CANCELLED')
          OR (UPPER(c.status) IN ('COMPLETED', 'DELIVERED') AND c.claimed_at > SYSDATE - 0.5)
      )
    ORDER BY c.claimed_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':vid', $volId);
oci_execute($stmt);

$deliveries = [];
while ($row = oci_fetch_assoc($stmt)) {
    $deliveries[] = $row;
}
oci_free_statement($stmt);

sendSuccess(['deliveries' => $deliveries]);
