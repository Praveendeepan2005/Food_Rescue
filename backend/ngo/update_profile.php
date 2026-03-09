<?php
// =============================================================
// ngo/update_profile.php
// Update NGO profile details
// Method: POST | { user_id, name, phone, address, ngo_reg_number }
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
$ngoRegNumber = trim($input['ngo_reg_number'] ?? '');
$conn = getDBConnection();

// Try update with optional columns
$sql = "UPDATE users SET name = :name, phone = :phone, address = :address, ngo_reg_number = :ngo_reg_number WHERE user_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':address', $address);
oci_bind_by_name($stmt, ':ngo_reg_number', $ngoRegNumber);
oci_bind_by_name($stmt, ':id', $userId);

if (!@oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    // If it fails because missing columns, we can inject them first in a smart way, or just update base columns
    // We will just try altering the table in a helper function or catching it
    oci_free_statement($stmt);

    // Add columns dynamically to prevent future errors
    @oci_execute(oci_parse($conn, "ALTER TABLE users ADD address VARCHAR2(255)"));
    @oci_execute(oci_parse($conn, "ALTER TABLE users ADD ngo_reg_number VARCHAR2(100)"));

    // Try again
    $stmt3 = oci_parse($conn, $sql);
    oci_bind_by_name($stmt3, ':name', $name);
    oci_bind_by_name($stmt3, ':phone', $phone);
    oci_bind_by_name($stmt3, ':address', $address);
    oci_bind_by_name($stmt3, ':ngo_reg_number', $ngoRegNumber);
    oci_bind_by_name($stmt3, ':id', $userId);

    if (!@oci_execute($stmt3, OCI_NO_AUTO_COMMIT)) {
        // Fallback: update only existing base columns
        oci_free_statement($stmt3);
        $stmt2 = oci_parse($conn, "UPDATE users SET name = :name, phone = :phone WHERE user_id = :id");
        oci_bind_by_name($stmt2, ':name', $name);
        oci_bind_by_name($stmt2, ':phone', $phone);
        oci_bind_by_name($stmt2, ':id', $userId);
        if (!@oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($conn);
            sendError('Failed to update profile.', 500);
        }
        oci_commit($conn);
        oci_free_statement($stmt2);
        sendSuccess(['name' => $name], 'Profile partially updated.');
    } else {
        oci_commit($conn);
        oci_free_statement($stmt3);
        sendSuccess(['name' => $name], 'Profile updated successfully.');
    }
} else {
    oci_commit($conn);
    oci_free_statement($stmt);
    sendSuccess(['user_id' => $userId, 'name' => $name, 'phone' => $phone], 'Profile updated successfully.');
}
