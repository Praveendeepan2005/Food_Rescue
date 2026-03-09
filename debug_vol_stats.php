<?php
require_once __DIR__ . '/backend/config/db.php';
$conn = getDBConnection();

echo "<h2>Claims Table - Status Check</h2><pre>";

// Check what statuses exist
$s = oci_parse($conn, "SELECT DISTINCT status, COUNT(*) as cnt FROM claims GROUP BY status ORDER BY status");
oci_execute($s);
echo "=== DISTINCT STATUSES IN CLAIMS ===\n";
while ($r = oci_fetch_assoc($s)) {
    echo "Status: [" . $r['STATUS'] . "] Count: " . $r['CNT'] . "\n";
}

// Check volunteer claims
$s2 = oci_parse($conn, "SELECT claim_id, volunteer_id, UPPER(status) as ustatus, status FROM claims WHERE volunteer_id IS NOT NULL ORDER BY claim_id DESC FETCH FIRST 10 ROWS ONLY");
oci_execute($s2);
echo "\n=== RECENT VOLUNTEER CLAIMS ===\n";
while ($r = oci_fetch_assoc($s2)) {
    echo "claim_id=" . $r['CLAIM_ID'] . " vol=" . $r['VOLUNTEER_ID'] . " status=[" . $r['STATUS'] . "] UPPER=[" . $r['USTATUS'] . "]\n";
}

// Test the exact query
$volId = 0;
$s3 = oci_parse($conn, "SELECT volunteer_id, COUNT(*) as cnt FROM claims WHERE volunteer_id IS NOT NULL GROUP BY volunteer_id");
oci_execute($s3);
echo "\n=== VOLUNTEER ID COUNTS ===\n";
while ($r = oci_fetch_assoc($s3)) {
    echo "vol_id=" . $r['VOLUNTEER_ID'] . " claims=" . $r['CNT'] . "\n";
    $volId = $r['VOLUNTEER_ID'];
}

// Now simulate the dashboard query with the last found volId
if ($volId) {
    $s4 = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM claims WHERE volunteer_id = :id AND UPPER(status) IN ('COMPLETED','DELIVERED')");
    oci_bind_by_name($s4, ':id', $volId);
    oci_execute($s4);
    $r4 = oci_fetch_assoc($s4);
    echo "\n=== COMPLETED COUNT for vol_id=$volId ===\n";
    echo "Count: " . $r4['CNT'] . "\n";
}
echo "</pre>";
