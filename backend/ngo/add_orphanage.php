<?php
// =============================================================
// ngo/add_orphanage.php
// Add a new orphanage/recipient location
// Method: POST | { ngo_id, name, contact_person, phone, address, city, latitude, longitude }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['ngo_id', 'name', 'address', 'city']);

$ngoId = (int) $input['ngo_id'];
$name = trim($input['name']);
$contact = trim($input['contact_person'] ?? '');
$phone = trim($input['phone_number'] ?? '');
$address = trim($input['address']);
$city = trim($input['city']);
$lat = isset($input['latitude']) ? (float) $input['latitude'] : 0;
$lng = isset($input['longitude']) ? (float) $input['longitude'] : 0;

$conn = getDBConnection();

$sql = "INSERT INTO orphanages (ngo_id, orphanage_name, contact_person, phone_number, address, city, latitude, longitude) 
        VALUES (:ngo_id, :name, :contact, :phone, :address, :city, :lat, :lng)";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':ngo_id', $ngoId);
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':contact', $contact);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':address', $address);
oci_bind_by_name($stmt, ':city', $city);
oci_bind_by_name($stmt, ':lat', $lat);
oci_bind_by_name($stmt, ':lng', $lng);

if (@oci_execute($stmt)) {
    sendSuccess([], 'Orphanage added successfully.');
} else {
    $e = oci_error($stmt);
    sendError('Failed to add orphanage: ' . $e['message'], 500);
}

oci_free_statement($stmt);
