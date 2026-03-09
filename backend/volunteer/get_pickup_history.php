<?php
// =============================================================
// volunteer/get_pickup_history.php
// Volunteer's completed contribution history
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
    SELECT c.claim_id, fa.food_type, fa.quantity, d.name as donor_name, 
           TO_CHAR(COALESCE(c.completed_at, c.picked_up_at, c.claimed_at), 'DD Mon YYYY') as completed_date, c.status
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    WHERE c.volunteer_id = :id AND c.status IN ('DELIVERED', 'COMPLETED')
    ORDER BY COALESCE(c.completed_at, c.picked_up_at, c.claimed_at) DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $volId);
oci_execute($stmt);
$history = [];
while ($row = oci_fetch_assoc($stmt))
    $history[] = $row;
sendSuccess(['history' => $history]);
