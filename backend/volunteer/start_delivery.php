<?php
// =============================================================
// volunteer/start_delivery.php
// Update status to 'On Delivery' when volunteer starts moving to NGO
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id']);

$alertId = (int) $input['alert_id'];
$volId = (int) $input['volunteer_id'];
$status = 'ON_DELIVERY';

$conn = getDBConnection();

// Update food_alerts
$upFA = oci_parse($conn, "UPDATE food_alerts SET delivery_status = :status, status = :status, delivery_start_time = SYSDATE WHERE alert_id = :aid");
oci_bind_by_name($upFA, ':status', $status);
oci_bind_by_name($upFA, ':aid', $alertId);

if (!oci_execute($upFA, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($upFA);
    oci_rollback($conn);
    sendError('Error updating alert status: ' . $err['message'], 500);
}

// Update claims
$upC = oci_parse($conn, "UPDATE claims SET status = :status, on_delivery_at = SYSDATE WHERE alert_id = :aid AND volunteer_id = :vid");
oci_bind_by_name($upC, ':status', $status);
oci_bind_by_name($upC, ':aid', $alertId);
oci_bind_by_name($upC, ':vid', $volId);

if (!oci_execute($upC, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($upC);
    oci_rollback($conn);
    sendError('Error updating claim status: ' . $err['message'], 500);
}

oci_commit($conn);
sendSuccess(null, "Delivery started successfully. Status updated to 'On Delivery'");
