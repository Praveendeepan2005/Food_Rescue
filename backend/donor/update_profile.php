<?php
// =============================================================
// donor/update_profile.php
// Update donor profile details
// Method: POST | { user_id, name, phone, address, org_name }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['user_id', 'name', 'phone']);

$userId = (int) $input['user_id'];
$name = trim($input['name']);
$phone = trim($input['phone']);
$address = trim($input['address'] ?? '');
$orgName = trim($input['org_name'] ?? '');
$conn = getDBConnection();

// Try update with optional columns
$sql = "UPDATE users SET name = :name, phone = :phone, address = :address, org_name = :org_name WHERE user_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':address', $address);
oci_bind_by_name($stmt, ':org_name', $orgName);
oci_bind_by_name($stmt, ':id', $userId);

if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    // Fallback: update only existing base columns
    oci_free_statement($stmt);
    $stmt2 = oci_parse($conn, "UPDATE users SET name = :name, phone = :phone WHERE user_id = :id");
    oci_bind_by_name($stmt2, ':name', $name);
    oci_bind_by_name($stmt2, ':phone', $phone);
    oci_bind_by_name($stmt2, ':id', $userId);
    if (!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
        oci_rollback($conn);
        sendError('Failed to update profile.', 500);
    }
    oci_commit($conn);
    oci_free_statement($stmt2);
    sendSuccess(['name' => $name], 'Profile updated.');
}

oci_commit($conn);
oci_free_statement($stmt);
sendSuccess(['user_id' => $userId, 'name' => $name, 'phone' => $phone], 'Profile updated successfully.');
