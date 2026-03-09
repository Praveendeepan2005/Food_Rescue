<?php
// =============================================================
// volunteer/update_pickup_status.php
// Volunteer updates their pickup status
// Method: POST | { claim_id, status }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['claim_id', 'status']);

$claimId = (int) $input['claim_id'];
$status = strtoupper(trim($input['status']));
// Valid: ON_THE_WAY, PICKED_UP, DELIVERED, COMPLETED

$conn = getDBConnection();

// Update claim
$sql = "UPDATE claims SET status = :status";
if ($status === 'FOOD_PICKED_UP' || $status === 'PICKED_UP')
    $sql .= ", picked_up_at = SYSDATE";
if ($status === 'DELIVERED' || $status === 'COMPLETED')
    $sql .= ", completed_at = SYSDATE";
$sql .= " WHERE claim_id = :id";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':status', $status);
oci_bind_by_name($stmt, ':id', $claimId);

if (@oci_execute($stmt)) {
    // Synchronize food_alerts status
    $alertSyncSql = "UPDATE food_alerts SET status = :status WHERE alert_id = (SELECT alert_id FROM claims WHERE claim_id = :id)";
    $as = oci_parse($conn, $alertSyncSql);
    oci_bind_by_name($as, ':status', $status);
    oci_bind_by_name($as, ':id', $claimId);
    oci_execute($as);

    // ════════════════════════════════════════════════════════════
    // Award points when mission is DELIVERED or COMPLETED
    // ════════════════════════════════════════════════════════════
    if ($status === 'DELIVERED' || $status === 'COMPLETED') {
        // ... (Award logic below)
        if ($status === 'DELIVERED' || $status === 'COMPLETED') {
            // Check if already awarded
            $checkPts = oci_parse($conn, "SELECT count(*) as cnt FROM reward_history WHERE claim_id = :cid");
            oci_bind_by_name($checkPts, ':cid', $claimId);
            oci_execute($checkPts);
            $ptRow = oci_fetch_assoc($checkPts);

            if ((int) $ptRow['CNT'] == 0) {
                // Get volunteer_id
                $volSql = "SELECT volunteer_id FROM claims WHERE claim_id = :id";
                $volStmt = oci_parse($conn, $volSql);
                oci_bind_by_name($volStmt, ':id', $claimId);
                oci_execute($volStmt);
                if ($volRow = oci_fetch_assoc($volStmt)) {
                    $vId = $volRow['VOLUNTEER_ID'];

                    // Award 100 Points
                    $ptsSql = "UPDATE users SET total_points = total_points + 100 WHERE user_id = :vid";
                    $ptsStmt = oci_parse($conn, $ptsSql);
                    oci_bind_by_name($ptsStmt, ':vid', $vId);
                    oci_execute($ptsStmt);

                    // Log History
                    $histSql = "INSERT INTO reward_history (user_id, points_awarded, reason, claim_id) VALUES (:vid, 100, 'Food Rescue Mission Completed', :cid)";
                    $histStmt = oci_parse($conn, $histSql);
                    oci_bind_by_name($histStmt, ':vid', $vId);
                    oci_bind_by_name($histStmt, ':cid', $claimId);
                    oci_execute($histStmt);

                    // Update Daily Goal
                    $goalSql = "
                        MERGE INTO daily_goals dg
                        USING (SELECT :vid as uid, TRUNC(SYSDATE) as gdate FROM dual) src
                        ON (dg.user_id = src.uid AND dg.goal_date = src.gdate)
                        WHEN MATCHED THEN UPDATE SET rescues_completed = rescues_completed + 1
                        WHEN NOT MATCHED THEN INSERT (user_id, goal_date, rescues_completed) VALUES (src.uid, src.gdate, 1)
                    ";
                    $goalStmt = oci_parse($conn, $goalSql);
                    oci_bind_by_name($goalStmt, ':vid', $vId);
                    oci_execute($goalStmt);
                }
            }
        }
    }

    if ($status === 'DELIVERED') {
        // After delivery, we can mark it COMPLETED to move to history
        $upFinal = oci_parse($conn, "UPDATE food_alerts SET status = 'COMPLETED' WHERE alert_id = (SELECT alert_id FROM claims WHERE claim_id = :id)");
        oci_bind_by_name($upFinal, ':id', $claimId);
        oci_execute($upFinal, OCI_NO_AUTO_COMMIT);

        $upClaimFinal = oci_parse($conn, "UPDATE claims SET status = 'COMPLETED' WHERE claim_id = :id");
        oci_bind_by_name($upClaimFinal, ':id', $claimId);
        oci_execute($upClaimFinal, OCI_NO_AUTO_COMMIT);
    }

    oci_commit($conn);
    sendSuccess(null, "Pickup marked as $status");

} else {
    sendError('Failed to update status.', 500);
}
