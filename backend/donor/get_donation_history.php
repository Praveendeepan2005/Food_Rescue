<?php
// =============================================================
// donor/get_donation_history.php
// Completed + Expired donations for a donor with NGO details
// Method: GET | ?donor_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['donor_id']))
    sendError('donor_id is required', 422);
$donorId = (int) $_GET['donor_id'];
$conn = getDBConnection();

$sql = "
    SELECT
        fa.alert_id,
        fa.food_type,
        fa.quantity,
        fa.status,
        NVL(u.name, 'Not Assigned')                        AS ngo_assigned,
        TO_CHAR(fc.claimed_at, 'DD Mon YYYY HH24:MI')      AS pickup_date,
        TO_CHAR(fa.created_at, 'DD Mon YYYY')              AS created_at
    FROM   food_alerts fa
    LEFT   JOIN claims fc  ON fa.alert_id   = fc.alert_id
    LEFT   JOIN users  u   ON fc.volunteer_id = u.user_id
    WHERE  fa.donor_id = :id
       AND fa.status   IN ('COMPLETED', 'EXPIRED')
    ORDER  BY fa.created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $donorId);

if (!@oci_execute($stmt)) {
    // Fallback: simple query without join if claims doesn't exist
    oci_free_statement($stmt);
    $sql2 = "
        SELECT alert_id, food_type, quantity, status,
               'N/A' AS ngo_assigned, 'N/A' AS pickup_date,
               TO_CHAR(created_at, 'DD Mon YYYY') AS created_at
        FROM   food_alerts
        WHERE  donor_id = :id AND status IN ('COMPLETED','EXPIRED')
        ORDER  BY created_at DESC
    ";
    $stmt = oci_parse($conn, $sql2);
    oci_bind_by_name($stmt, ':id', $donorId);
    @oci_execute($stmt);
}

$history = [];
while ($row = oci_fetch_assoc($stmt))
    $history[] = $row;
oci_free_statement($stmt);

sendSuccess(['history' => $history], 'Donation history fetched.');
