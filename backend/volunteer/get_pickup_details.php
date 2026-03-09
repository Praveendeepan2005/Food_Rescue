<?php
// =============================================================
// volunteer/get_pickup_details.php
// Full details of a pickup mission
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['claim_id']))
    sendError('claim_id is required', 422);
$claimId = (int) $_GET['claim_id'];

$conn = getDBConnection();
$sql = "
    SELECT 
        c.claim_id, c.alert_id, c.volunteer_id, c.status,
        fa.food_type, fa.quantity, fa.category, TO_CHAR(fa.expiry_time, 'DD Mon HH24:MI') as expiry,
        d.name as donor_name, d.phone as donor_phone, fa.pickup_address,
        n.name as ngo_name, n.phone as ngo_phone
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users d ON fa.donor_id = d.user_id
    JOIN users n ON fa.assigned_ngo_id = n.user_id
    WHERE c.claim_id = :id
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $claimId);
oci_execute($stmt);
$details = oci_fetch_assoc($stmt);
if (!$details)
    sendError('Pickup not found.', 404);
sendSuccess($details);
