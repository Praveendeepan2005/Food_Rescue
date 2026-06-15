<?php
// =============================================================
// ngo/get_accepted_donations.php
// List donations accepted by a specific NGO
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['ngo_id']))
    sendError('ngo_id is required', 422);
$ngoId = (int) $_GET['ngo_id'];

$conn = getDBConnection();
$sql = "
    SELECT fa.alert_id, fa.food_type, fa.quantity, fa.pickup_address, fa.city,
           fa.category, fa.preparation_time, fa.contact_number, fa.special_instructions,
           fa.assigned_vol_id,
           u.name as donor_name, fa.status,
           v.name as volunteer_name,
           o.orphanage_name, o.address as delivery_address
    FROM food_alerts fa
    JOIN users u ON fa.donor_id = u.user_id
    LEFT JOIN users v ON fa.assigned_vol_id = v.user_id
    LEFT JOIN deliveries d ON fa.alert_id = d.donation_id
    LEFT JOIN orphanages o ON d.orphanage_id = o.orphanage_id
    WHERE fa.assigned_ngo_id = :nid 
    AND fa.status IN ('CLAIMED', 'VOLUNTEER_ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'DELIVERING', 'DELIVERED', 'COMPLETED')
    ORDER BY fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':nid', $ngoId);
oci_execute($stmt);
$donations = [];
while ($row = oci_fetch_assoc($stmt))
    $donations[] = $row;
sendSuccess(['donations' => $donations]);
