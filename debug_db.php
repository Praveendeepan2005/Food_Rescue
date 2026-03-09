<?php
require_once __DIR__ . '/backend/config/db.php';
$conn = getDBConnection();

echo "--- USERS (NGOs) ---\n";
$s1 = oci_parse($conn, "SELECT user_id, name, city, role FROM users WHERE role = 'NGO'");
oci_execute($s1);
while ($row = oci_fetch_assoc($s1)) {
    echo "ID: {$row['USER_ID']} | Name: {$row['NAME']} | City: {$row['CITY']}\n";
}

echo "\n--- FOOD ALERTS ---\n";
$s2 = oci_parse($conn, "SELECT alert_id, food_type, status, assigned_ngo_id, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS') as creation FROM food_alerts ORDER BY created_at DESC");
oci_execute($s2);
while ($row = oci_fetch_assoc($s2)) {
    echo "ID: {$row['ALERT_ID']} | Food: {$row['FOOD_TYPE']} | Status: {$row['STATUS']} | NGO_ID: {$row['ASSIGNED_NGO_ID']} | Date: {$row['CREATION']}\n";
}
