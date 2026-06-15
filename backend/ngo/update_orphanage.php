<?php
// =============================================================
// ngo/update_orphanage.php
// NGO updates an existing orphanage/recipient location
// Method: POST | { ngo_id, orphanage_id, name, contact_person, phone_number, address, city, latitude, longitude }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['ngo_id', 'orphanage_id', 'name', 'address', 'city']);

$ngoId = (int) $input['ngo_id'];
$orpId = (int) $input['orphanage_id'];
$name = $input['name'];
$contact = $input['contact_person'] ?? '';
$phone = $input['phone_number'] ?? '';
$address = $input['address'];
$city = $input['city'];
$lat = (float) ($input['latitude'] ?? 0);
$lng = (float) ($input['longitude'] ?? 0);

$conn = getDBConnection();

$sql = "UPDATE orphanages 
        SET orphanage_name = :name,
            contact_person = :contact,
            phone_number = :phone,
            address = :address,
            city = :city,
            latitude = :lat,
            longitude = :lng
        WHERE orphanage_id = :oid AND ngo_id = :nid";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':contact', $contact);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':address', $address);
oci_bind_by_name($stmt, ':city', $city);
oci_bind_by_name($stmt, ':lat', $lat);
oci_bind_by_name($stmt, ':lng', $lng);
oci_bind_by_name($stmt, ':oid', $orpId);
oci_bind_by_name($stmt, ':nid', $ngoId);

if (oci_execute($stmt)) {
    sendSuccess(null, 'Orphanage updated successfully!');
} else {
    $e = oci_error($stmt);
    sendError('Failed to update orphanage: ' . $e['message'], 500);
}
