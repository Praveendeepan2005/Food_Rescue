<?php
// =============================================================
// ngo/get_profile.php
// Get ngo profile
// Method: GET | ?user_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['user_id']))
    sendError('user_id is required', 422);
$userId = (int) $_GET['user_id'];
$conn = getDBConnection();

$sql = "SELECT user_id, name, email, phone, NVL(address,'') AS address, NVL(ngo_reg_number,'') AS ngo_reg_number, role FROM users WHERE user_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $userId);
// Handle missing column gracefully, though it might throw ORA-00904, we can suppress it or just use a generic select
if (!@oci_execute($stmt)) {
    // Fallback if ngo_reg_number or address is missing
    $sqlFallback = "SELECT user_id, name, email, phone, '' AS address, '' AS ngo_reg_number, role FROM users WHERE user_id = :id";
    $stmtFallback = oci_parse($conn, $sqlFallback);
    oci_bind_by_name($stmtFallback, ':id', $userId);
    oci_execute($stmtFallback);
    $user = oci_fetch_assoc($stmtFallback);
    oci_free_statement($stmtFallback);
} else {
    $user = oci_fetch_assoc($stmt);
}
oci_free_statement($stmt);

if (!$user)
    sendError('User not found.', 404);

// Get points and stats for NGO
$sStats = oci_parse($conn, "
    SELECT 
        COUNT(assigned_vol_id) as total_assignments,
        SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as total_completed
    FROM food_alerts 
    WHERE assigned_ngo_id = :id
");
oci_bind_by_name($sStats, ':id', $userId);
oci_execute($sStats);
$statsRow = oci_fetch_assoc($sStats);
oci_free_statement($sStats);

$assignments = (int) ($statsRow['TOTAL_ASSIGNMENTS'] ?? 0);
$completed = (int) ($statsRow['TOTAL_COMPLETED'] ?? 0);

// Calculate points: 10 points per assignment, 50 points per completion
$points = ($assignments * 10) + ($completed * 50);

$user['stats'] = [
    'assignments' => $assignments,
    'completed' => $completed,
    'points' => $points
];

sendSuccess($user, 'Profile fetched.');
