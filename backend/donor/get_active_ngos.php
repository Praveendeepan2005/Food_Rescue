<?php
// =============================================================
// backend/donor/get_active_ngos.php
// Fetch all active NGOs for particularly selecting one
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

// Fetch only ACTIVE NGOs
$sql = "SELECT user_id, name, city FROM users WHERE role = 'NGO' AND status = 'ACTIVE' ORDER BY name ASC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$ngos = [];
while ($row = oci_fetch_assoc($stmt)) {
    $ngos[] = [
        'id' => $row['USER_ID'],
        'name' => $row['NAME'],
        'city' => $row['CITY']
    ];
}
oci_free_statement($stmt);

sendSuccess(['ngos' => $ngos]);
