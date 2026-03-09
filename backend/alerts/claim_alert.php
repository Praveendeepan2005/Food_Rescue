<?php
// =============================================================
// alerts/claim_alert.php
// Claim a Food Alert API (NGO / VOLUNTEER only)
//
// Method : POST
// URL    : http://localhost/food-rescue-api/alerts/claim_alert.php
//
// Request Body (JSON):
// {
//   "alert_id"     : 1,
//   "volunteer_id" : 3
// }
//
// Business Rules:
//   - Only NGO or VOLUNTEER roles can claim an alert
//   - The alert must be AVAILABLE (not already claimed or expired)
//   - A user cannot claim the same alert twice (UNIQUE constraint)
//   - Uses an Oracle transaction → both INSERT into CLAIMS
//     and UPDATE of FOOD_ALERTS must succeed, or both roll back
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
requireFields($input, ['alert_id', 'volunteer_id']);

$alertId     = (int)$input['alert_id'];
$volunteerId = (int)$input['volunteer_id'];

if ($alertId <= 0 || $volunteerId <= 0) {
    sendError('alert_id and volunteer_id must be positive integers.', 422);
}

// -----------------------------------------------------------
// 2. Connect to Oracle DB
// -----------------------------------------------------------
$conn = getDBConnection();

// -----------------------------------------------------------
// 3. Validate the claimant's role (must be NGO or VOLUNTEER)
// -----------------------------------------------------------
$userSql  = 'SELECT role, name, device_token FROM users WHERE user_id = :id';
$userStmt = oci_parse($conn, $userSql);
oci_bind_by_name($userStmt, ':id', $volunteerId);

if (!oci_execute($userStmt)) {
    $err = oci_error($userStmt);
    error_log('Claim - user fetch error: ' . $err['message']);
    sendError('Failed to verify user. Please try again.', 500);
}

$volunteer = oci_fetch_assoc($userStmt);
oci_free_statement($userStmt);

if (!$volunteer) {
    sendError('User not found. Invalid volunteer_id.', 404);
}

$allowedClaimRoles = ['NGO', 'VOLUNTEER'];
if (!in_array($volunteer['ROLE'], $allowedClaimRoles)) {
    sendError('Access denied. Only NGO or VOLUNTEER roles can claim food alerts.', 403);
}

// -----------------------------------------------------------
// 4. Validate the alert exists and is AVAILABLE
// -----------------------------------------------------------
$alertSql  = '
    SELECT fa.alert_id, fa.status, fa.food_type, fa.quantity,
           fa.donor_id, u.name AS donor_name, u.device_token AS donor_token
    FROM   food_alerts fa
    JOIN   users       u  ON fa.donor_id = u.user_id
    WHERE  fa.alert_id = :alert_id
';
$alertStmt = oci_parse($conn, $alertSql);
oci_bind_by_name($alertStmt, ':alert_id', $alertId);

if (!oci_execute($alertStmt)) {
    $err = oci_error($alertStmt);
    error_log('Claim - alert fetch error: ' . $err['message']);
    sendError('Failed to fetch alert details. Please try again.', 500);
}

$alert = oci_fetch_assoc($alertStmt);
oci_free_statement($alertStmt);

if (!$alert) {
    sendError('Food alert not found. Invalid alert_id.', 404);
}

if ($alert['STATUS'] !== 'AVAILABLE') {
    sendError(
        'This food alert is no longer available. Current status: ' . $alert['STATUS'],
        409  // 409 Conflict
    );
}

// -----------------------------------------------------------
// 5. Check for double-claiming (same user, same alert)
//    The DB has a UNIQUE constraint, but we give a cleaner error here
// -----------------------------------------------------------
$dupSql  = 'SELECT COUNT(*) AS cnt FROM claims WHERE alert_id = :alert_id AND volunteer_id = :vol_id';
$dupStmt = oci_parse($conn, $dupSql);
oci_bind_by_name($dupStmt, ':alert_id', $alertId);
oci_bind_by_name($dupStmt, ':vol_id',   $volunteerId);
oci_execute($dupStmt);
$dupRow = oci_fetch_assoc($dupStmt);
oci_free_statement($dupStmt);

if ((int)$dupRow['CNT'] > 0) {
    sendError('You have already claimed this food alert.', 409);
}

// -----------------------------------------------------------
// 6. BEGIN ORACLE TRANSACTION
//    Step A: INSERT into CLAIMS
//    Step B: UPDATE FOOD_ALERTS status to CLAIMED
//    Both must succeed — if either fails, ROLLBACK everything
// -----------------------------------------------------------

// Step A — Insert claim record
$insertClaimSql  = "
    INSERT INTO claims (alert_id, volunteer_id, status)
    VALUES (:alert_id, :volunteer_id, 'ACTIVE')
";
$insertClaimStmt = oci_parse($conn, $insertClaimSql);
oci_bind_by_name($insertClaimStmt, ':alert_id',     $alertId);
oci_bind_by_name($insertClaimStmt, ':volunteer_id', $volunteerId);

if (!oci_execute($insertClaimStmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($insertClaimStmt);
    error_log('Claim insert error: ' . $err['message']);
    oci_rollback($conn);
    // Check for ORA-00001 (unique constraint violation = double claim race condition)
    if (strpos($err['message'], 'ORA-00001') !== false) {
        sendError('This alert was already claimed. Please try another one.', 409);
    }
    sendError('Failed to claim alert. Please try again.', 500);
}
oci_free_statement($insertClaimStmt);

// Step B — Update alert status to CLAIMED
$updateAlertSql  = "UPDATE food_alerts SET status = 'CLAIMED' WHERE alert_id = :alert_id AND status = 'AVAILABLE'";
$updateAlertStmt = oci_parse($conn, $updateAlertSql);
oci_bind_by_name($updateAlertStmt, ':alert_id', $alertId);

if (!oci_execute($updateAlertStmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($updateAlertStmt);
    error_log('Claim - alert update error: ' . $err['message']);
    oci_rollback($conn);
    sendError('Failed to update alert status. Claim rolled back.', 500);
}

// Verify that the UPDATE actually affected 1 row
// (if 0 rows affected, the alert was concurrently claimed — race condition)
$rowsAffected = oci_num_rows($updateAlertStmt);
oci_free_statement($updateAlertStmt);

if ($rowsAffected === 0) {
    oci_rollback($conn);
    sendError('Alert was just claimed by someone else. Please try another nearby alert.', 409);
}

// All steps succeeded — COMMIT the transaction
oci_commit($conn);

// -----------------------------------------------------------
// 7. Notify the DONOR that their food has been claimed
// -----------------------------------------------------------
if (!empty($alert['DONOR_TOKEN'])) {
    sendPushNotification(
        $alert['DONOR_TOKEN'],
        '✅ Your Food Has Been Claimed!',
        $volunteer['NAME'] . ' (' . $volunteer['ROLE'] . ') has claimed your '
          . $alert['FOOD_TYPE'] . ' (' . $alert['QUANTITY'] . ').'
    );
}

// -----------------------------------------------------------
// 8. Return success response
// -----------------------------------------------------------
sendSuccess([
    'alert_id'       => $alertId,
    'volunteer_id'   => $volunteerId,
    'volunteer_name' => $volunteer['NAME'],
    'volunteer_role' => $volunteer['ROLE'],
    'food_type'      => $alert['FOOD_TYPE'],
    'quantity'       => $alert['QUANTITY'],
    'donor_name'     => $alert['DONOR_NAME'],
    'alert_status'   => 'CLAIMED',
    'claim_status'   => 'ACTIVE',
    'claimed_at'     => date('Y-m-d H:i:s'),
], 'Food alert claimed successfully. Please coordinate with the donor for pickup.');
