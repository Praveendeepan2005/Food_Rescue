<?php
// =============================================================
// alerts/get_donor_stats.php
// Get Impact Statistics for a specific Donor
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['donor_id'])) {
    sendError('donor_id is required', 422);
}

$donorId = (int) $_GET['donor_id'];
$conn = getDBConnection();

$stats = [];

// 1. Total Donations
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE donor_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $donorId);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['total_donations'] = (int) $row['CNT'];
oci_free_statement($stmt);

// 2. Completed Rescues
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE donor_id = :id AND status = 'COMPLETED'";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $donorId);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['completed_rescues'] = (int) $row['CNT'];
oci_free_statement($stmt);

// 3. active alerts
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE donor_id = :id AND status = 'AVAILABLE'";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $donorId);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['active_alerts'] = (int) $row['CNT'];
oci_free_statement($stmt);

sendSuccess($stats, 'Donor impact stats fetched.');
