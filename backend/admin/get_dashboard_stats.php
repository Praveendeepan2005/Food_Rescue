<?php
// =============================================================
// admin/get_dashboard_stats.php
// Get system-wide statistics for Admin Monitoring Dashboard
// Method: GET
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

// System stats
$stats = [];

// Total NGOs
$ngoCount = oci_parse($conn, "SELECT count(*) as cnt FROM users WHERE role = 'NGO'");
oci_execute($ngoCount);
$stats['total_ngos'] = (int) oci_fetch_assoc($ngoCount)['CNT'];

// Total Volunteers 
$volCount = oci_parse($conn, "SELECT count(*) as cnt FROM users WHERE role = 'VOLUNTEER'");
oci_execute($volCount);
$stats['total_volunteers'] = (int) oci_fetch_assoc($volCount)['CNT'];

// Total Donors
$donorCount = oci_parse($conn, "SELECT count(*) as cnt FROM users WHERE role = 'DONOR'");
oci_execute($donorCount);
$stats['total_donors'] = (int) oci_fetch_assoc($donorCount)['CNT'];

// Total Orphanages (New)
$orpCount = oci_parse($conn, "SELECT count(*) as cnt FROM orphanages");
oci_execute($orpCount);
$stats['total_orphanages'] = (int) oci_fetch_assoc($orpCount)['CNT'];

// Total Donations
$donCount = oci_parse($conn, "SELECT count(*) as cnt FROM food_alerts");
oci_execute($donCount);
$stats['total_donations'] = (int) oci_fetch_assoc($donCount)['CNT'];

// Total Deliveries Tracked (New table)
$delCountMain = oci_parse($conn, "SELECT count(*) as cnt FROM deliveries");
oci_execute($delCountMain);
$stats['total_deliveries_tracked'] = (int) oci_fetch_assoc($delCountMain)['CNT'];

// Total Food Distributed (Quantity sum)
$sumFood = oci_parse($conn, "SELECT SUM(quantity) as val FROM food_alerts WHERE status = 'COMPLETED'");
oci_execute($sumFood);
$rowSum = oci_fetch_assoc($sumFood);
$stats['food_distributed'] = (int) ($rowSum['VAL'] ?? 0);

// Total Deliveries Completed
$delCount = oci_parse($conn, "SELECT count(*) as cnt FROM food_alerts WHERE status = 'COMPLETED'");
oci_execute($delCount);
$stats['total_deliveries'] = (int) oci_fetch_assoc($delCount)['CNT'];

// City-wise reports for charts
$citiesSql = "SELECT city, count(*) as count FROM food_alerts GROUP BY city ORDER BY count DESC FETCH FIRST 5 ROWS ONLY";
$citiesStmt = oci_parse($conn, $citiesSql);
oci_execute($citiesStmt);
$cityData = [];
while ($row = oci_fetch_assoc($citiesStmt)) {
    $cityData[] = $row;
}

sendSuccess([
    'stats' => $stats,
    'cityReports' => $cityData
]);
