<?php
// =============================================================
// admin/export.php
// Admin Data Export Utility (CSV)
// =============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

// Note: We don't use setCORSHeaders() here because this is a direct download link
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$type = $_GET['type'] ?? 'impact';
$conn = getDBConnection();

$filename = "food_rescue_" . $type . "_" . date('Ymd') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type === 'users') {
    fputcsv($output, ['User ID', 'Name', 'Email', 'Role', 'Status', 'Joined']);
    $sql = "SELECT user_id, name, email, role, status, TO_CHAR(created_at, 'YYYY-MM-DD') as joined FROM users ORDER BY created_at DESC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    while ($row = oci_fetch_assoc($stmt)) {
        fputcsv($output, $row);
    }
} elseif ($type === 'alerts') {
    fputcsv($output, ['Alert ID', 'Food Type', 'Quantity', 'Status', 'Created', 'Expiry']);
    $sql = "SELECT alert_id, food_type, quantity, status, TO_CHAR(created_at, 'YYYY-MM-DD') as created, TO_CHAR(expiry_time, 'YYYY-MM-DD') as expiry FROM food_alerts ORDER BY created_at DESC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    while ($row = oci_fetch_assoc($stmt)) {
        fputcsv($output, $row);
    }
} elseif ($type === 'claims' || $type === 'impact') {
    // Detailed impact report
    fputcsv($output, ['Claim ID', 'Food Type', 'Donor', 'Volunteer', 'Status', 'Claim Time']);
    $sql = "
        SELECT 
            c.claim_id, fa.food_type, d.name as donor, v.name as volunteer, c.status, TO_CHAR(c.claimed_at, 'YYYY-MM-DD') as claim_time
        FROM claims c
        JOIN food_alerts fa ON c.alert_id = fa.alert_id
        JOIN users d ON fa.donor_id = d.user_id
        JOIN users v ON c.volunteer_id = v.user_id
        ORDER BY c.claimed_at DESC
    ";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    while ($row = oci_fetch_assoc($stmt)) {
        fputcsv($output, $row);
    }
}

fclose($output);
oci_close($conn);
exit;
