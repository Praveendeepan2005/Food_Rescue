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
    SELECT fa.alert_id, fa.food_type, fa.quantity, fa.pickup_address, 
           fa.category, fa.preparation_time, fa.contact_number, fa.special_instructions,
           fa.assigned_vol_id,
           u.name as donor_name, fa.status,
           (SELECT name FROM users WHERE user_id = c.volunteer_id) as volunteer_name
    FROM food_alerts fa
    JOIN users u ON fa.donor_id = u.user_id
    LEFT JOIN claims c ON fa.alert_id = c.alert_id
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
