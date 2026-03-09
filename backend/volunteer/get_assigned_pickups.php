<?php
// =============================================================
// volunteer/get_assigned_pickups.php
// List of pickups assigned to a specific volunteer
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
           d.name as donor_name, n.name as ngo_name, c.status
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    WHERE c.volunteer_id = :id AND c.status != 'COMPLETED' AND c.status != 'CANCELLED'
    ORDER BY c.claimed_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $volId);
oci_execute($stmt);
$pickups = [];
while ($row = oci_fetch_assoc($stmt))
    $pickups[] = $row;
sendSuccess(['pickups' => $pickups]);
