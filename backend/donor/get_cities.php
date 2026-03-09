<?php
// =============================================================
// backend/utils/get_cities.php
// Publicly fetch cities for dropdowns
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();
$sql = "SELECT city_name FROM tamilnadu_cities ORDER BY city_name ASC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$cities = [];
while ($row = oci_fetch_assoc($stmt)) {
    $cities[] = $row['CITY_NAME'];
}
oci_free_statement($stmt);

sendSuccess(['cities' => $cities]);
