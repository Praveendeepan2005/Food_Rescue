<?php
// =============================================================
// backend/volunteer/update_profile.php
// Update volunteer profile
// Method: POST
// =============================================================
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['user_id', 'name']);

$userId = (int) $input['user_id'];
$name = trim($input['name']);
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$availability = trim($input['availability'] ?? 'FLEXIBLE');

$conn = getDBConnection();

$sql = "UPDATE users SET name = :name, phone = :phone, address = :address, city = :city, availability_status = :avail WHERE user_id = :id AND role = 'VOLUNTEER'";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':address', $address);
oci_bind_by_name($stmt, ':city', $city);
oci_bind_by_name($stmt, ':avail', $availability);
oci_bind_by_name($stmt, ':id', $userId);

if (oci_execute($stmt)) {
    sendSuccess(['name' => $name], 'Profile updated successfully.');
} else {
    sendError('Failed to update profile.', 500);
}
