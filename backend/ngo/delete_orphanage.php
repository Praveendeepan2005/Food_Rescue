<?php
// =============================================================
// ngo/delete_orphanage.php
// Delete an orphanage location
// Method: POST | { orphanage_id, ngo_id }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['orphanage_id', 'ngo_id']);

$orphanageId = (int) $input['orphanage_id'];
$ngoId = (int) $input['ngo_id'];

$conn = getDBConnection();

$sql = "DELETE FROM orphanages WHERE orphanage_id = :oid AND ngo_id = :nid";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':oid', $orphanageId);
oci_bind_by_name($stmt, ':nid', $ngoId);

if (@oci_execute($stmt)) {
    sendSuccess([], 'Orphanage deleted successfully.');
} else {
    $e = oci_error($stmt);
    sendError('Failed to delete orphanage: ' . $e['message'], 500);
}

oci_free_statement($stmt);
