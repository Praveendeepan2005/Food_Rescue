<?php
// =============================================================
// ngo/submit_rating.php
// NGO rates a volunteer after delivery completion
// Method: POST | { alert_id, volunteer_id, rating }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['alert_id', 'volunteer_id', 'rating']);

$alertId = (int) $input['alert_id'];
$volId = (int) $input['volunteer_id'];
$rating = (float) $input['rating'];

if ($rating < 1 || $rating > 5) {
    sendError('Rating must be between 1 and 5', 422);
}

$conn = getDBConnection();

// 1. Update the claim with the rating
$sqlRating = "UPDATE claims SET rating = :rating WHERE alert_id = :aid AND volunteer_id = :vid AND status = 'COMPLETED'";
$stmtRating = oci_parse($conn, $sqlRating);
oci_bind_by_name($stmtRating, ':rating', $rating);
oci_bind_by_name($stmtRating, ':aid', $alertId);
oci_bind_by_name($stmtRating, ':vid', $volId);

if (oci_execute($stmtRating, OCI_NO_AUTO_COMMIT)) {
    // 2. Recalculate average rating for the volunteer
    $sqlAvg = "UPDATE users u SET average_rating = (
                   SELECT AVG(rating) FROM claims WHERE volunteer_id = :vid AND rating > 0
               ) WHERE user_id = :vid";
    $stmtAvg = oci_parse($conn, $sqlAvg);
    oci_bind_by_name($stmtAvg, ':vid', $volId);
    oci_execute($stmtAvg, OCI_NO_AUTO_COMMIT);

    oci_commit($conn);
    sendSuccess(null, "Volunteer rated successfully!");
} else {
    oci_rollback($conn);
    sendError('Failed to submit rating. Mission must be completed before rating.', 500);
}
