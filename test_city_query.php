<?php
try {
    $projectRoot = __DIR__;
    require_once $projectRoot . '/backend/config/db.php';
    $dbConn = getDBConnection();
    if (!$dbConn) {
        die("Connection failed");
    }
    $cityStmt = oci_parse($dbConn, "SELECT city_name FROM tamilnadu_cities ORDER BY city_name ASC");
    if (!oci_execute($cityStmt)) {
        $e = oci_error($cityStmt);
        die("Query failed: " . $e['message']);
    }
    $cities = [];
    while ($cityRow = oci_fetch_assoc($cityStmt)) {
        $cities[] = $cityRow['CITY_NAME'];
    }
    echo "Count: " . count($cities) . "\n";
    print_r($cities);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
