<?php
// =============================================================
// volunteer/get_vol_dashboard.php
// Volunteer Dashboard Stats & Recent Tasks
// Method: GET | ?volunteer_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id']))
    sendError('volunteer_id is required', 422);
$volId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

// 1. Assigned / In-Progress Pickups
// Use UPPER() so 'Active', 'ACTIVE', 'Assigned', 'ASSIGNED' all match
$s1 = oci_parse(
    $conn,
    "SELECT COUNT(*) AS cnt FROM claims
     WHERE volunteer_id = :id
       AND UPPER(status) IN ('ACTIVE','ASSIGNED','ON_THE_WAY','PICKUP_STARTED','FOOD_PICKED','PICKED_UP','DELIVERING','ON_DELIVERY')"
);
oci_bind_by_name($s1, ':id', $volId);
oci_execute($s1);
$r1 = oci_fetch_assoc($s1);
$assigned = (int) ($r1['CNT'] ?? 0);

// 2. Pending = same as in-progress (tasks awaiting next action)
$pending = $assigned;

// 3. Completed Pickups — covers 'COMPLETED', 'Completed', 'DELIVERED', 'Delivered'
$s3 = oci_parse(
    $conn,
    "SELECT COUNT(*) AS cnt FROM claims
     WHERE volunteer_id = :id
       AND UPPER(status) IN ('COMPLETED','DELIVERED')"
);
oci_bind_by_name($s3, ':id', $volId);
oci_execute($s3);
$r3 = oci_fetch_assoc($s3);
$completed = (int) ($r3['CNT'] ?? 0);

// 4. Recent Tasks (last 5, any status)
$sql = "
    SELECT c.claim_id, c.alert_id, fa.food_type, fa.quantity,
           u.name as donor_name, fa.pickup_address, c.status
    FROM claims c
    JOIN food_alerts fa ON c.alert_id = fa.alert_id
    JOIN users u ON fa.donor_id = u.user_id
    WHERE c.volunteer_id = :id
    ORDER BY c.claimed_at DESC
    FETCH FIRST 5 ROWS ONLY
";
$s4 = oci_parse($conn, $sql);
oci_bind_by_name($s4, ':id', $volId);
oci_execute($s4);
$recent = [];
while ($row = oci_fetch_assoc($s4))
    $recent[] = $row;

sendSuccess([
    'stats' => [
        'assigned' => $assigned,
        'pending' => $pending,
        'completed' => $completed,
        'meals' => $completed * 12  // ~12 meals per rescue
    ],
    'recent' => $recent
]);
