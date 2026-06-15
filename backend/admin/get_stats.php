<?php
// =============================================================
// admin/get_stats.php
// Admin Dashboard Comprehensive Statistics
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

$stats = [
    'top_cards' => [],
    'charts' => [
        'monthly_donations' => [],
        'user_growth' => [],
        'role_distribution' => [],
        'city_distribution' => ['labels' => [], 'data' => []]
    ],
    'recent_activity' => [
        'users' => [],
        'alerts' => [],
        'rescues' => []
    ]
];

// -- 1. TOP CARDS --

// Users
$sql = "SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'DONOR' THEN 1 ELSE 0 END) as total_donors,
        SUM(CASE WHEN role = 'NGO' THEN 1 ELSE 0 END) as total_ngos,
        SUM(CASE WHEN role = 'VOLUNTEER' THEN 1 ELSE 0 END) as total_volunteers
        FROM users";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['top_cards']['total_users'] = (int) $row['TOTAL_USERS'];
$stats['top_cards']['total_donors'] = (int) $row['TOTAL_DONORS'];
$stats['top_cards']['total_ngos'] = (int) $row['TOTAL_NGOS'];
$stats['top_cards']['total_volunteers'] = (int) $row['TOTAL_VOLUNTEERS'];
oci_free_statement($stmt);

// Alerts
$sql = "SELECT 
        SUM(CASE WHEN status NOT IN ('COMPLETED', 'EXPIRED', 'CANCELLED', 'EXPIRED_ALERT', 'VOID') THEN 1 ELSE 0 END) as active_alerts,
        SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_rescues,
        SUM(CASE WHEN status = 'EXPIRED' THEN 1 ELSE 0 END) as expired_alerts,
        SUM(quantity) as food_distributed,
        COUNT(*) as total_alerts
        FROM food_alerts";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$active = (int) ($row['ACTIVE_ALERTS'] ?? 0);
$completed = (int) ($row['COMPLETED_RESCUES'] ?? 0);
$expired = (int) ($row['EXPIRED_ALERTS'] ?? 0);
$total = (int) ($row['TOTAL_ALERTS'] ?? 0);
$foodDist = (int) ($row['FOOD_DISTRIBUTED'] ?? 0);

// Orphanages count
$orpCountSt = oci_parse($conn, "SELECT count(*) as cnt FROM orphanages");
oci_execute($orpCountSt);
$orpRow = oci_fetch_assoc($orpCountSt);
$totalOrp = (int) ($orpRow['CNT'] ?? 0);

$stats['top_cards']['active_alerts'] = $active;
$stats['top_cards']['completed_rescues'] = $completed;
$stats['top_cards']['expired_alerts'] = $expired;
$stats['top_cards']['total_orphanages'] = $totalOrp;
$stats['top_cards']['food_distributed'] = $foodDist;
$stats['top_cards']['completion_rate'] = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
oci_free_statement($stmt);

// Avg Delivery Time
$sqlEff = "SELECT AVG((delivery_time - assigned_time) * 24 * 60) as avg_mins
           FROM food_alerts
           WHERE delivery_time IS NOT NULL AND assigned_time IS NOT NULL";
$stmtEff = oci_parse($conn, $sqlEff);
oci_execute($stmtEff);
if ($rowEff = oci_fetch_assoc($stmtEff)) {
    $stats['top_cards']['avg_delivery_time'] = round((float) ($rowEff['AVG_MINS'] ?? 0), 1);
}
oci_free_statement($stmtEff);

// -- 2. CHARTS --

// Role Distribution
$stats['charts']['role_distribution'] = [
    'labels' => ['Donors', 'NGOs', 'Volunteers'],
    'data' => [$stats['top_cards']['total_donors'], $stats['top_cards']['total_ngos'], $stats['top_cards']['total_volunteers']]
];

// Monthly Registrations separated by Roles (Last 6 Months)
// Pre-fill the array with the last 6 months to ensure a continuous line graph
$monthsMap = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('M Y', mktime(0, 0, 0, date('m') - $i, 1));
    $monthsMap[$m] = 0;
}

$stats['charts']['user_growth'] = [
    'labels' => array_keys($monthsMap),
    'donors' => array_values($monthsMap),
    'ngos' => array_values($monthsMap),
    'volunteers' => array_values($monthsMap)
];

$sql = "SELECT INITCAP(TO_CHAR(created_at, 'Mon YYYY')) as month, role, COUNT(*) as cnt 
        FROM users 
        WHERE created_at >= ADD_MONTHS(SYSDATE, -6)
          AND role IN ('DONOR', 'NGO', 'VOLUNTEER')
        GROUP BY TO_CHAR(created_at, 'Mon YYYY'), role
        ORDER BY MIN(created_at) ASC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $m = $row['MONTH'];
    $role = strtoupper($row['ROLE']);

    // Find matching month index
    $idx = array_search($m, $stats['charts']['user_growth']['labels']);
    if ($idx === false) {
        foreach ($stats['charts']['user_growth']['labels'] as $k => $v) {
            if (strcasecmp($v, $m) === 0) {
                $idx = $k;
                break;
            }
        }
    }

    if ($idx !== false) {
        if ($role === 'DONOR')
            $stats['charts']['user_growth']['donors'][$idx] = (int) $row['CNT'];
        if ($role === 'NGO')
            $stats['charts']['user_growth']['ngos'][$idx] = (int) $row['CNT'];
        if ($role === 'VOLUNTEER')
            $stats['charts']['user_growth']['volunteers'][$idx] = (int) $row['CNT'];
    }
}
oci_free_statement($stmt);

// City Distribution
$citiesSql = "SELECT city, count(*) as count FROM food_alerts GROUP BY city ORDER BY count DESC FETCH FIRST 5 ROWS ONLY";
$citiesStmt = oci_parse($conn, $citiesSql);
oci_execute($citiesStmt);
while ($cRow = oci_fetch_assoc($citiesStmt)) {
    $stats['charts']['city_distribution']['labels'][] = $cRow['CITY'] ?: 'Unknown';
    $stats['charts']['city_distribution']['data'][] = (int) $cRow['COUNT'];
}
oci_free_statement($citiesStmt);

// -- 3. RECENT ACTIVITY --

// Latest 5 users
$sql = "SELECT name, role, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') as joined FROM users ORDER BY created_at DESC FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $stats['recent_activity']['users'][] = $row;
}
oci_free_statement($stmt);

// Latest 5 alerts
$sql = "SELECT food_type, quantity, status, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI') as created FROM food_alerts ORDER BY created_at DESC FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $stats['recent_activity']['alerts'][] = $row;
}
oci_free_statement($stmt);

// Latest 5 completed rescues
$sql = "SELECT f.food_type, u.name as donor_name, v.name as volunteer_name, TO_CHAR(f.delivery_time, 'YYYY-MM-DD HH24:MI') as delivered_at 
        FROM food_alerts f
        JOIN users u ON f.donor_id = u.user_id
        JOIN users v ON f.assigned_vol_id = v.user_id
        WHERE f.status = 'COMPLETED'
        ORDER BY f.delivery_time DESC FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $stats['recent_activity']['rescues'][] = $row;
}
oci_free_statement($stmt);

sendSuccess($stats, 'Enterprise dashboard statistics loaded.');
