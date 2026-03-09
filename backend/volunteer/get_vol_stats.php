<?php
// =============================================================
// volunteer/get_vol_stats.php
// Get summary stats for identifying volunteer performance cards
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id']))
    sendError('volunteer_id is required', 422);

$volId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

// Assigned Deliveries: Active claims (not completed or cancelled)
$sqlAssigned = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND status NOT IN ('COMPLETED', 'CANCELLED', 'Delivered')";
$s1 = oci_parse($conn, $sqlAssigned);
oci_bind_by_name($s1, ':id', $volId);
oci_execute($s1);
$row1 = oci_fetch_assoc($s1);

// Completed Deliveries: Claims with status COMPLETED or Delivered
$sqlCompleted = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND (status = 'COMPLETED' OR status = 'Delivered')";
$s2 = oci_parse($conn, $sqlCompleted);
oci_bind_by_name($s2, ':id', $volId);
oci_execute($s2);
$row2 = oci_fetch_assoc($s2);

// Pending Pickups: Active claims with status 'Assigned' or 'ACCEPTED' or 'VOLUNTEER_ASSIGNED'
$sqlPending = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND status IN ('Assigned', 'ACCEPTED', 'VOLUNTEER_ASSIGNED')";
$s3 = oci_parse($conn, $sqlPending);
oci_bind_by_name($s3, ':id', $volId);
oci_execute($s3);
$row3 = oci_fetch_assoc($s3);

// Today's Tasks: Rescues completed today (directly from claims for accuracy)
$sqlToday = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND status = 'COMPLETED' AND TRUNC(completed_at) = TRUNC(SYSDATE)";
$s4 = oci_parse($conn, $sqlToday);
oci_bind_by_name($s4, ':id', $volId);
oci_execute($s4);
$row4 = oci_fetch_assoc($s4);

// 5. NGO Rating: Average rating from completed claims
$sqlRating = "SELECT AVG(rating) as avg_r FROM claims WHERE volunteer_id = :id AND status = 'COMPLETED' AND (rating > 0 AND rating IS NOT NULL)";
$s5 = oci_parse($conn, $sqlRating);
oci_bind_by_name($s5, ':id', $volId);
oci_execute($s5);
$row5 = oci_fetch_assoc($s5);
$rating = (float) ($row5['AVG_R'] ?? 4.5); // Default to 4.5 if no ratings yet

// 6. Growth Rate: Mission increase compare (This month vs Last Month)
$sqlThisMonth = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND status = 'COMPLETED' AND completed_at >= TRUNC(SYSDATE, 'MM')";
$s6 = oci_parse($conn, $sqlThisMonth);
oci_bind_by_name($s6, ':id', $volId);
oci_execute($s6);
$row6 = oci_fetch_assoc($s6);
$thisMonthCount = (int) ($row6['CNT'] ?? 0);

$sqlLastMonth = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :id AND status = 'COMPLETED' AND completed_at >= ADD_MONTHS(TRUNC(SYSDATE, 'MM'), -1) AND completed_at < TRUNC(SYSDATE, 'MM')";
$s7 = oci_parse($conn, $sqlLastMonth);
oci_bind_by_name($s7, ':id', $volId);
oci_execute($s7);
$row7 = oci_fetch_assoc($s7);
$lastMonthCount = (int) ($row7['CNT'] ?? 0);

$growth = 0;
if ($lastMonthCount > 0) {
    $growth = (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100;
} else if ($thisMonthCount > 0) {
    $growth = 100.0;
}

sendSuccess([
    'assigned' => (int) ($row1['CNT'] ?? 0),
    'completed' => (int) ($row2['CNT'] ?? 0),
    'pending' => (int) ($row3['CNT'] ?? 0),
    'today' => (int) ($row4['CNT'] ?? 0),
    'rating' => round($rating, 1),
    'growth' => round($growth, 1),
    'daily_stats' => getDailyStats($conn, $volId)
]);

function getDailyStats($conn, $volId)
{
    $labels = [];
    $data = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateStr = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('D', strtotime($dateStr));

        $sql = "SELECT COUNT(*) as cnt FROM claims WHERE volunteer_id = :vid AND status = 'COMPLETED' AND TRUNC(completed_at) = TO_DATE(:dt, 'YYYY-MM-DD')";
        $s = oci_parse($conn, $sql);
        oci_bind_by_name($s, ':vid', $volId);
        oci_bind_by_name($s, ':dt', $dateStr);
        oci_execute($s);
        $r = oci_fetch_assoc($s);
        $data[] = (int) ($r['CNT'] ?? 0);
    }
    return ['labels' => $labels, 'data' => $data];
}
