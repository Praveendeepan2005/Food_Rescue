<?php
// =============================================================
// donor/cancel_donation.php
// Cancel a PENDING/AVAILABLE donation (donor only)
// Method: POST | { donor_id, alert_id }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['donor_id', 'alert_id']);

$donorId = (int) $input['donor_id'];
$alertId = (int) $input['alert_id'];
$conn = getDBConnection();

// Verify ownership and current status
$chk = oci_parse($conn, "SELECT donor_id, status FROM food_alerts WHERE alert_id = :aid");
oci_bind_by_name($chk, ':aid', $alertId);
oci_execute($chk);
$alert = oci_fetch_assoc($chk);
oci_free_statement($chk);

if (!$alert)
    sendError('Donation not found.', 404);
if ((int) $alert['DONOR_ID'] !== $donorId)
    sendError('Access denied.', 403);
if (!in_array($alert['STATUS'], ['AVAILABLE', 'PENDING']))
    sendError('Only AVAILABLE donations can be cancelled.', 409);

$upd = oci_parse($conn, "UPDATE food_alerts SET status = 'EXPIRED' WHERE alert_id = :aid");
oci_bind_by_name($upd, ':aid', $alertId);

if (!oci_execute($upd, OCI_NO_AUTO_COMMIT)) {
    oci_rollback($conn);
    sendError('Failed to cancel donation.', 500);
}
oci_commit($conn);
oci_free_statement($upd);

sendSuccess(['alert_id' => $alertId, 'status' => 'EXPIRED'], 'Donation cancelled successfully.');
