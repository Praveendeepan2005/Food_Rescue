<?php
// =============================================================
// alerts/get_volunteer_claims.php
// Get all claims made by a specific NGO/Volunteer
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id'])) {
    sendError('volunteer_id is required', 422);
}

$volunteerId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

$sql = "
    SELECT 
        c.claim_id, c.status as claim_status,
        fa.alert_id, fa.food_type, fa.quantity, fa.status as alert_status,
        TO_CHAR(fa.expiry_time, 'YYYY-MM-DD HH24:MI') as expiry_time,
        u.name as donor_name, u.phone as donor_phone
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users u ON fa.donor_id = u.user_id
    WHERE c.volunteer_id = :vol_id
    ORDER BY c.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':vol_id', $volunteerId);

if (oci_execute($stmt)) {
    $claims = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $claims[] = $row;
    }
    oci_free_statement($stmt);
    sendSuccess(['claims' => $claims], 'Volunteer claims fetched.');
} else {
    $err = oci_error($stmt);
    sendError('Failed to fetch claims: ' . $err['message'], 500);
}
