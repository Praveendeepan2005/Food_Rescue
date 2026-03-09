<?php
// =============================================================
// donor/get_donor_dashboard.php
// Donor Dashboard Stats: Total, Active, Completed, Meals
// Method: GET | ?donor_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['donor_id']))
    sendError('donor_id is required', 422);
$donorId = (int) $_GET['donor_id'];
$conn = getDBConnection();

// Total donations
$s = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM food_alerts WHERE donor_id = :id");
oci_bind_by_name($s, ':id', $donorId);
oci_execute($s);
$r = oci_fetch_assoc($s);
$total = (int) $r['CNT'];
oci_free_statement($s);

// Active (AVAILABLE or CLAIMED)
$s = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM food_alerts WHERE donor_id = :id AND status IN ('AVAILABLE','CLAIMED')");
oci_bind_by_name($s, ':id', $donorId);
oci_execute($s);
$r = oci_fetch_assoc($s);
$active = (int) $r['CNT'];
oci_free_statement($s);

// Completed
$s = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM food_alerts WHERE donor_id = :id AND status = 'COMPLETED'");
oci_bind_by_name($s, ':id', $donorId);
oci_execute($s);
$r = oci_fetch_assoc($s);
$completed = (int) $r['CNT'];
oci_free_statement($s);

// Expired
$s = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM food_alerts WHERE donor_id = :id AND status = 'EXPIRED'");
oci_bind_by_name($s, ':id', $donorId);
oci_execute($s);
$r = oci_fetch_assoc($s);
$expired = (int) $r['CNT'];
oci_free_statement($s);

// Recent 5 donations
$sql = "
    SELECT alert_id, food_type, quantity, status,
           TO_CHAR(created_at, 'DD Mon YYYY') AS created_at
    FROM   food_alerts
    WHERE  donor_id = :id
    ORDER  BY created_at DESC
    FETCH  FIRST 5 ROWS ONLY
";
$s = oci_parse($conn, $sql);
oci_bind_by_name($s, ':id', $donorId);
oci_execute($s);
$recent = [];
while ($row = oci_fetch_assoc($s))
    $recent[] = $row;
oci_free_statement($s);

sendSuccess([
    'total_donations' => $total,
    'active_donations' => $active,
    'completed_donations' => $completed,
    'expired_donations' => $expired,
    'meals_donated' => $completed * 10,   // estimated: 10 meals per completed rescue
    'recent_donations' => $recent
], 'Donor dashboard data fetched.');
