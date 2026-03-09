<?php
// =============================================================
// volunteer/get_delivery_history.php
// History of completed deliveries for the volunteer
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
           fa.category, fa.preparation_time, fa.contact_number, 
           d.name as donor_name, n.name as ngo_name, c.status,
           TO_CHAR(c.completed_at, 'DD Mon YYYY HH24:MI') as delivery_date
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    WHERE c.volunteer_id = :id AND (c.status = 'COMPLETED' OR c.status = 'Delivered')
    ORDER BY c.completed_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $volId);
oci_execute($stmt);

$history = [];
while ($row = oci_fetch_assoc($stmt))
    $history[] = $row;

sendSuccess(['history' => $history]);
