<?php
// =============================================================
// ngo/update_inventory.php
// Update status of inventory items (Distributed, Expired)
// Method: POST | { inventory_id, status }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['inventory_id', 'status']);

$inventoryId = (int) $input['inventory_id'];
$status = strtoupper(trim($input['status']));

if (!in_array($status, ['DISTRIBUTED', 'EXPIRED', 'AVAILABLE'])) {
    sendError('Invalid status. Use: DISTRIBUTED, EXPIRED, AVAILABLE', 422);
}

$conn = getDBConnection();
$sql = "UPDATE ngo_inventory SET status = :status WHERE inventory_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':status', $status);
oci_bind_by_name($stmt, ':id', $inventoryId);

if (oci_execute($stmt)) {
    sendSuccess(null, "Inventory item marked as $status");
} else {
    sendError('Failed to update inventory status', 500);
}
