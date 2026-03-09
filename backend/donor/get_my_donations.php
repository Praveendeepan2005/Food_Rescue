<?php
// =============================================================
// donor/get_my_donations.php
// All donations by a specific donor (active + pending)
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
        alert_id,
        food_type,
        quantity,
        NVL(pickup_address, 'N/A')                         AS pickup_address,
        category,
        status,
        TO_CHAR(created_at, 'DD Mon YYYY HH24:MI')         AS created_at,
        TO_CHAR(expiry_time, 'DD Mon YYYY HH24:MI')        AS expiry_time
    FROM   food_alerts
    WHERE  donor_id = :id
    ORDER  BY created_at DESC
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $donorId);
oci_execute($stmt);

$donations = [];
while ($row = oci_fetch_assoc($stmt))
    $donations[] = $row;
oci_free_statement($stmt);

sendSuccess(['donations' => $donations], 'Donations fetched.');
