<?php
// =============================================================
// volunteer/mark_delivered.php
// Finalize delivery, update status to 'Delivered' / 'COMPLETED'
// and award reward points.
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id']);

$alertId = (int) $input['alert_id'];
$volId = (int) $input['volunteer_id'];
$status = 'DELIVERED';
$conn = getDBConnection();

// Update food_alerts
$upFA = oci_parse($conn, "UPDATE food_alerts SET delivery_status = :status, status = 'COMPLETED', delivered_time = SYSDATE WHERE alert_id = :aid");
oci_bind_by_name($upFA, ':status', $status);
oci_bind_by_name($upFA, ':aid', $alertId);

if (!oci_execute($upFA, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($upFA);
    oci_rollback($conn);
    sendError('Error updating alert status: ' . $err['message'], 500);
}

// Insert into NGO Inventory (only if not already inserted for this alert)
$getAlertDetails = oci_parse($conn, "SELECT assigned_ngo_id, food_type, quantity, expiry_time FROM food_alerts WHERE alert_id = :aid");
oci_bind_by_name($getAlertDetails, ':aid', $alertId);
oci_execute($getAlertDetails);
if ($det = oci_fetch_assoc($getAlertDetails)) {
    $ngoId = $det['ASSIGNED_NGO_ID'];
    if ($ngoId) {
        // Duplicate guard: only insert if this exact alert hasn't been logged yet
        $checkInv = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM ngo_inventory WHERE ngo_id = :nid AND food_type = :ftype AND TO_CHAR(added_at,'YYYY-MM-DD') = TO_CHAR(SYSDATE,'YYYY-MM-DD')");
        oci_bind_by_name($checkInv, ':nid', $ngoId);
        oci_bind_by_name($checkInv, ':ftype', $det['FOOD_TYPE']);
        oci_execute($checkInv);
        $cRow = oci_fetch_assoc($checkInv);
        if ((int) ($cRow['CNT'] ?? 0) === 0) {
            $invSql = "INSERT INTO ngo_inventory (ngo_id, food_type, quantity, expiry_time, status)
                       VALUES (:nid, :ftype, :qty, :exp, 'AVAILABLE')";
            $invStmt = oci_parse($conn, $invSql);
            oci_bind_by_name($invStmt, ':nid', $ngoId);
            oci_bind_by_name($invStmt, ':ftype', $det['FOOD_TYPE']);
            oci_bind_by_name($invStmt, ':qty', $det['QUANTITY']);
            oci_bind_by_name($invStmt, ':exp', $det['EXPIRY_TIME']);
            if (!oci_execute($invStmt, OCI_NO_AUTO_COMMIT)) {
                $invErr = oci_error($invStmt);
                error_log("INVENTORY INSERT FAILED for alert $alertId: " . $invErr['message']);
            }
        }
    }
}

// Update claims
$upC = oci_parse($conn, "UPDATE claims SET status = 'COMPLETED', completed_at = SYSDATE WHERE alert_id = :aid AND volunteer_id = :vid");
oci_bind_by_name($upC, ':aid', $alertId);
oci_bind_by_name($upC, ':vid', $volId);

if (!oci_execute($upC, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($upC);
    oci_rollback($conn);
    sendError('Error completing claim: ' . $err['message'], 500);
}

// Update deliveries table
$upDel = oci_parse($conn, "UPDATE deliveries SET status = 'DELIVERED', completed_at = SYSDATE WHERE donation_id = :aid AND volunteer_id = :vid");
oci_bind_by_name($upDel, ':aid', $alertId);
oci_bind_by_name($upDel, ':vid', $volId);
oci_execute($upDel, OCI_NO_AUTO_COMMIT);

// Award Points / Rewards
$getClaimIdSql = "SELECT claim_id FROM claims WHERE alert_id = :aid AND volunteer_id = :vid ORDER BY claimed_at DESC FETCH FIRST 1 ROWS ONLY";
$gcStmt = oci_parse($conn, $getClaimIdSql);
oci_bind_by_name($gcStmt, ':aid', $alertId);
oci_bind_by_name($gcStmt, ':vid', $volId);
oci_execute($gcStmt);
if ($cRow = oci_fetch_assoc($gcStmt)) {
    $claimId = $cRow['CLAIM_ID'];

    // Idempotency check
    $checkPts = oci_parse($conn, "SELECT count(*) as cnt FROM reward_history WHERE claim_id = :cid");
    oci_bind_by_name($checkPts, ':cid', $claimId);
    oci_execute($checkPts);
    $ptRow = oci_fetch_assoc($checkPts);

    if ((int) ($ptRow['CNT'] ?? 0) == 0) {
        $ptsStmt = oci_parse($conn, "UPDATE users SET total_points = total_points + 100 WHERE user_id = :vid");
        oci_bind_by_name($ptsStmt, ':vid', $volId);
        oci_execute($ptsStmt, OCI_NO_AUTO_COMMIT);

        $histStmt = oci_parse($conn, "INSERT INTO reward_history (user_id, points_awarded, reason, claim_id) VALUES (:vid, 100, 'Food Rescue Mission Completed', :cid)");
        oci_bind_by_name($histStmt, ':vid', $volId);
        oci_bind_by_name($histStmt, ':cid', $claimId);
        oci_execute($histStmt, OCI_NO_AUTO_COMMIT);

        // 3. Update Daily Goals (Simplified Merge)
        $goalSql = "
            MERGE INTO daily_goals dg
            USING dual src
            ON (dg.user_id = :vid AND dg.goal_date = TRUNC(SYSDATE))
            WHEN MATCHED THEN UPDATE SET rescues_completed = rescues_completed + 1
            WHEN NOT MATCHED THEN INSERT (user_id, goal_date, rescues_completed) VALUES (:vid, TRUNC(SYSDATE), 1)
        ";
        $gs = oci_parse($conn, $goalSql);
        oci_bind_by_name($gs, ':vid', $volId);
        oci_execute($gs, OCI_NO_AUTO_COMMIT);
    }
}

oci_commit($conn);
sendSuccess(null, "Mission completed. Food delivered and points awarded!");
