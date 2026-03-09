<?php
// =============================================================
// donor/get_history.php
// Returns complete donation history for a donor
// Method: GET | donor_id
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['donor_id']))
    sendError('donor_id is required', 422);
$donorId = (int) $_GET['donor_id'];

$conn = getDBConnection();

$sql = "SELECT alert_id, food_type, category, quantity, status, 
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS') as created_at
        FROM food_alerts 
        WHERE donor_id = :did 
        ORDER BY created_at DESC";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':did', $donorId);
oci_execute($stmt);

$history = [];
while ($row = oci_fetch_assoc($stmt)) {
    $history[] = $row;
}
sendSuccess(['history' => $history]);
