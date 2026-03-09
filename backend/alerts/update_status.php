<?php
// =============================================================
// alerts/update_status.php
// Update Food Alert / Claim Status API
//
// Method : POST
// URL    : http://localhost/food-rescue-api/alerts/update_status.php
//
// Request Body (JSON):
// {
//   "alert_id"     : 1,
//   "volunteer_id" : 3,
//   "new_status"   : "COMPLETED"
// }
//
// Business Rules:
//   - Marks the CLAIM as COMPLETED
//   - Marks the FOOD_ALERT as COMPLETED
//   - Only the volunteer who claimed the alert may complete it
//   - Validates that the alert is currently in CLAIMED status
//   - Uses a full Oracle transaction (commit / rollback)
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../notifications/send_notification.php';

setCORSHeaders();
requireMethod('POST');

// -----------------------------------------------------------
// 1. Parse and validate input
// -----------------------------------------------------------
$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id', 'new_status']);

$alertId     = (int)$input['alert_id'];
$volunteerId = (int)$input['volunteer_id'];
$newStatus   = strtoupper(trim($input['new_status']));

if ($alertId <= 0 || $volunteerId <= 0) {
    sendError('alert_id and volunteer_id must be positive integers.', 422);
}

// Only COMPLETED and CANCELLED are valid transitions via this endpoint
$validStatuses = ['COMPLETED', 'CANCELLED'];
if (!in_array($newStatus, $validStatuses)) {
    sendError('Invalid new_status. Allowed values: COMPLETED, CANCELLED.', 422);
}

// -----------------------------------------------------------
// 2. Connect to Oracle DB
// -----------------------------------------------------------
$conn = getDBConnection();

// -----------------------------------------------------------
// 3. Validate volunteeer exists
// -----------------------------------------------------------
$userSql  = 'SELECT role, name, device_token FROM users WHERE user_id = :id';
$userStmt = oci_parse($conn, $userSql);
oci_bind_by_name($userStmt, ':id', $volunteerId);

if (!oci_execute($userStmt)) {
    $err = oci_error($userStmt);
    error_log('UpdateStatus - user fetch error: ' . $err['message']);
    sendError('Failed to verify user. Please try again.', 500);
}

$volunteer = oci_fetch_assoc($userStmt);
oci_free_statement($userStmt);

if (!$volunteer) {
    sendError('User not found. Invalid volunteer_id.', 404);
}

// Only NGO / VOLUNTEER can update status
if (!in_array($volunteer['ROLE'], ['NGO', 'VOLUNTEER'])) {
    sendError('Access denied. Only NGO or VOLUNTEER roles can update alert status.', 403);
}

// -----------------------------------------------------------
// 4. Fetch the existing CLAIM for this volunteer on this alert
// -----------------------------------------------------------
$claimSql  = '
    SELECT c.claim_id, c.status AS claim_status,
           fa.status             AS alert_status,
           fa.food_type,         fa.quantity,
           fa.donor_id,
           u.name  AS donor_name,
           u.device_token AS donor_token
    FROM   claims     c
    JOIN   food_alerts fa ON c.alert_id  = fa.alert_id
    JOIN   users       u  ON fa.donor_id = u.user_id
    WHERE  c.alert_id     = :alert_id
      AND  c.volunteer_id = :volunteer_id
';
$claimStmt = oci_parse($conn, $claimSql);
oci_bind_by_name($claimStmt, ':alert_id',     $alertId);
oci_bind_by_name($claimStmt, ':volunteer_id', $volunteerId);

if (!oci_execute($claimStmt)) {
    $err = oci_error($claimStmt);
    error_log('UpdateStatus - claim fetch error: ' . $err['message']);
    sendError('Failed to fetch claim details.', 500);
}

$claim = oci_fetch_assoc($claimStmt);
oci_free_statement($claimStmt);

if (!$claim) {
    sendError('No active claim found for this alert and volunteer combination.', 404);
}

// Prevent redundant updates
if ($claim['CLAIM_STATUS'] === $newStatus) {
    sendError('Claim is already marked as ' . $newStatus . '.', 409);
}

// Only ACTIVE claims can be updated
if ($claim['CLAIM_STATUS'] !== 'ACTIVE') {
    sendError(
        'Cannot update a claim that is already ' . $claim['CLAIM_STATUS'] . '.',
        409
    );
}

// Alert must be in CLAIMED state (not expired, not yet completed)
if ($claim['ALERT_STATUS'] !== 'CLAIMED') {
    sendError(
        'Cannot update alert in current state: ' . $claim['ALERT_STATUS'] . '.',
        409
    );
}

// -----------------------------------------------------------
// 5. BEGIN ORACLE TRANSACTION
//    Step A: UPDATE claims.status
//    Step B: UPDATE food_alerts.status
//    Both must succeed or both roll back
// -----------------------------------------------------------

// Step A — Update the claim record
$claimId         = (int)$claim['CLAIM_ID'];
$updateClaimSql  = 'UPDATE claims SET status = :new_status WHERE claim_id = :claim_id';
$updateClaimStmt = oci_parse($conn, $updateClaimSql);
oci_bind_by_name($updateClaimStmt, ':new_status', $newStatus);
oci_bind_by_name($updateClaimStmt, ':claim_id',   $claimId);

if (!oci_execute($updateClaimStmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($updateClaimStmt);
    error_log('UpdateStatus - claim update error: ' . $err['message']);
    oci_rollback($conn);
    sendError('Failed to update claim status. Transaction rolled back.', 500);
}
oci_free_statement($updateClaimStmt);

// Step B — Update the food_alert record
// COMPLETED claim → alert becomes COMPLETED
// CANCELLED claim → alert reverts to AVAILABLE (someone else can claim it)
$alertNewStatus   = ($newStatus === 'COMPLETED') ? 'COMPLETED' : 'AVAILABLE';
$updateAlertSql   = 'UPDATE food_alerts SET status = :new_status WHERE alert_id = :alert_id';
$updateAlertStmt  = oci_parse($conn, $updateAlertSql);
oci_bind_by_name($updateAlertStmt, ':new_status', $alertNewStatus);
oci_bind_by_name($updateAlertStmt, ':alert_id',   $alertId);

if (!oci_execute($updateAlertStmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($updateAlertStmt);
    error_log('UpdateStatus - alert update error: ' . $err['message']);
    oci_rollback($conn);
    sendError('Failed to update alert status. Transaction rolled back.', 500);
}
oci_free_statement($updateAlertStmt);

// All steps succeeded — COMMIT
oci_commit($conn);

// -----------------------------------------------------------
// 6. Send notification to donor about completion
// -----------------------------------------------------------
if ($newStatus === 'COMPLETED' && !empty($claim['DONOR_TOKEN'])) {
    sendPushNotification(
        $claim['DONOR_TOKEN'],
        '🎉 Food Rescue Complete!',
        $volunteer['NAME'] . ' has successfully collected your '
          . $claim['FOOD_TYPE'] . '. Thank you for your contribution!'
    );
}

if ($newStatus === 'CANCELLED' && !empty($claim['DONOR_TOKEN'])) {
    sendPushNotification(
        $claim['DONOR_TOKEN'],
        '⚠️ Claim Cancelled',
        $volunteer['NAME'] . ' cancelled their claim for '
          . $claim['FOOD_TYPE'] . '. Your alert is now available again.'
    );
}

// -----------------------------------------------------------
// 7. Return success response
// -----------------------------------------------------------
sendSuccess([
    'alert_id'       => $alertId,
    'claim_id'       => $claimId,
    'volunteer_id'   => $volunteerId,
    'volunteer_name' => $volunteer['NAME'],
    'food_type'      => $claim['FOOD_TYPE'],
    'quantity'       => $claim['QUANTITY'],
    'claim_status'   => $newStatus,
    'alert_status'   => $alertNewStatus,
    'updated_at'     => date('Y-m-d H:i:s'),
], 'Status updated successfully. Alert is now marked as ' . $alertNewStatus . '.');
