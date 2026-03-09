<?php
require_once __DIR__ . '/food-rescue-api/config/db.php';
$conn = getDBConnection();
$s = oci_parse($conn, 'SELECT * FROM users WHERE ROWNUM = 1');
oci_execute($s);
echo json_encode(array_keys(oci_fetch_assoc($s)));
