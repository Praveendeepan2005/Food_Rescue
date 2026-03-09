<?php
// =============================================================
// ngo/get_inventory.php
// List food inventory for a specific NGO
// Method: GET | ngo_id
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['ngo_id']))
    sendError('ngo_id is required', 422);
$ngoId = (int) $_GET['ngo_id'];

$conn = getDBConnection();

$sql = "SELECT inventory_id, food_type, quantity, status, 
               TO_CHAR(expiry_time, 'YYYY-MM-DD HH24:MI:SS') as expiry_time,
               TO_CHAR(added_at, 'YYYY-MM-DD HH24:MI:SS') as added_at
        FROM ngo_inventory 
        WHERE ngo_id = :nid 
        ORDER BY added_at DESC";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':nid', $ngoId);
oci_execute($stmt);

$items = [];
while ($row = oci_fetch_assoc($stmt)) {
    $items[] = $row;
}

sendSuccess(['inventory' => $items]);
