<?php
// =============================================================
// backend/volunteer/get_profile.php
// Fetch volunteer profile
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

$stmt = oci_parse($conn, "SELECT user_id, name, email, phone, address, city, availability_status as availability, status, TO_CHAR(created_at,'DD Mon YYYY') as joined FROM users WHERE user_id = :id AND role = 'VOLUNTEER'");
oci_bind_by_name($stmt, ':id', $userId);
oci_execute($stmt);
$profile = oci_fetch_assoc($stmt);
if (!$profile)
    sendError('Volunteer not found', 404);

// Stats
$s1 = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM claims WHERE volunteer_id = :id AND UPPER(status) IN ('COMPLETED','DELIVERED')");
oci_bind_by_name($s1, ':id', $userId);
oci_execute($s1);
$profile['COMPLETED'] = (int) (oci_fetch_assoc($s1)['CNT'] ?? 0);

$s2 = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM claims WHERE volunteer_id = :id AND UPPER(status) NOT IN ('COMPLETED','CANCELLED','DELIVERED')");
oci_bind_by_name($s2, ':id', $userId);
oci_execute($s2);
$profile['ACTIVE'] = (int) (oci_fetch_assoc($s2)['CNT'] ?? 0);

$profile['MEALS'] = $profile['COMPLETED'] * 12;
$profile['POINTS'] = $profile['COMPLETED'] * 50;

sendSuccess(['profile' => $profile]);
