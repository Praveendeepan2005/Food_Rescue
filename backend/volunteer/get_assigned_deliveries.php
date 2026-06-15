<?php
// =============================================================
// volunteer/get_assigned_deliveries.php
// List of active deliveries assigned to the volunteer
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id']))
    sendError('volunteer_id is required', 422);

$volId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

$sql = "
    SELECT c.claim_id, c.alert_id, fa.food_type, fa.quantity, fa.pickup_address, 
           fa.category, fa.preparation_time, fa.contact_number, fa.special_instructions,
           d.name as donor_name, n.name as ngo_name, c.status,
           fa.latitude as donor_lat, fa.longitude as donor_lng,
           o.orphanage_name, o.address as delivery_address, 
           o.latitude as orphanage_lat, o.longitude as orphanage_lng,
           fa.city
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    LEFT JOIN deliveries dl ON fa.alert_id = dl.donation_id
    LEFT JOIN orphanages o ON dl.orphanage_id = o.orphanage_id
    WHERE c.volunteer_id = :id AND c.status NOT IN ('COMPLETED', 'CANCELLED', 'DELIVERED')
    ORDER BY c.claimed_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $volId);
oci_execute($stmt);

$deliveries = [];
while ($row = oci_fetch_assoc($stmt))
    $deliveries[] = $row;

sendSuccess(['deliveries' => $deliveries]);
