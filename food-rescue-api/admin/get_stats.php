<?php
// =============================================================
// admin/get_stats.php
// Admin Dashboard Overview Statistics
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

$conn = getDBConnection();

$stats = [];

// 1. Total Users
$sql = "SELECT COUNT(*) as cnt FROM users";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['total_users'] = (int) $row['CNT'];
oci_free_statement($stmt);

// 2. Active Alerts (AVAILABLE)
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE status = 'AVAILABLE'";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['active_alerts'] = (int) $row['CNT'];
oci_free_statement($stmt);

// 3. Completed Rescues
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE status = 'COMPLETED'";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['completed_rescues'] = (int) $row['CNT'];
oci_free_statement($stmt);

// 4. Monthly Growth (last 30 days) - Basic count
$sql = "SELECT COUNT(*) as cnt FROM food_alerts WHERE created_at >= SYSDATE - 30";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$stats['monthly_alerts'] = (int) $row['CNT'];
oci_free_statement($stmt);

sendSuccess($stats, 'Admin statistics loaded.');
