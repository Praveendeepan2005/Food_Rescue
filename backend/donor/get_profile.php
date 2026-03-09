<?php
// =============================================================
// donor/get_profile.php
// Get donor profile
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

$sql = "SELECT user_id, name, email, phone, NVL(address,'') AS address, NVL(org_name,'') AS org_name, role FROM users WHERE user_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $userId);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$user)
    sendError('User not found.', 404);

sendSuccess($user, 'Profile fetched.');
