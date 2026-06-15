<?php
// =============================================================
// ngo/get_orphanages.php
// Get all orphanages for a specific NGO
// Method: GET | ?ngo_id=...&city=... (optional)
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['ngo_id'])) {
    sendError('NGO ID is required.', 422);
}

$ngoId = (int) $_GET['ngo_id'];
$city = $_GET['city'] ?? null;

$conn = getDBConnection();

$sql = "SELECT * FROM orphanages WHERE ngo_id = :ngo_id";
if ($city) {
    $sql .= " AND LOWER(city) = LOWER(:city)";
}
$sql .= " ORDER BY orphanage_name ASC";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':ngo_id', $ngoId);
if ($city) {
    oci_bind_by_name($stmt, ':city', $city);
}

oci_execute($stmt);

$orphanages = [];
while ($row = oci_fetch_assoc($stmt)) {
    $orphanages[] = $row;
}

oci_free_statement($stmt);
sendSuccess(['orphanages' => $orphanages]);
