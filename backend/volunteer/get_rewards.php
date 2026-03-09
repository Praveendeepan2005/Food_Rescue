<?php
// =============================================================
// volunteer/get_rewards.php
// Fetch volunteer points, rewards history, and daily goal status
// Method: GET | ?volunteer_id=X
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('GET');

if (!isset($_GET['volunteer_id']))
    sendError('volunteer_id is required', 422);
$volId = (int) $_GET['volunteer_id'];
$conn = getDBConnection();

// 1. Get total points
$s1 = oci_parse($conn, "SELECT total_points, current_streak FROM users WHERE user_id = :id");
oci_bind_by_name($s1, ':id', $volId);
oci_execute($s1);
$userStats = oci_fetch_assoc($s1);

// 2. Get Daily Goal Status
$s2 = oci_parse($conn, "SELECT rescues_completed, daily_target FROM daily_goals WHERE user_id = :id AND goal_date = TRUNC(SYSDATE)");
oci_bind_by_name($s2, ':id', $volId);
oci_execute($s2);
$dailyGoal = oci_fetch_assoc($s2) ?: ['RESCUES_COMPLETED' => 0, 'DAILY_TARGET' => 3];

// 3. Get Reward History (Recent 5)
$s3 = oci_parse($conn, "SELECT points_awarded, reason, TO_CHAR(awarded_at, 'DD Mon') as awarded_at 
                        FROM reward_history WHERE user_id = :id ORDER BY awarded_at DESC FETCH FIRST 5 ROWS ONLY");
oci_bind_by_name($s3, ':id', $volId);
oci_execute($s3);
$history = [];
while ($row = oci_fetch_assoc($s3))
    $history[] = $row;

sendSuccess([
    'points' => (int) ($userStats['TOTAL_POINTS'] ?? 0),
    'streak' => (int) ($userStats['CURRENT_STREAK'] ?? 0),
    'daily_goal' => [
        'completed' => (int) $dailyGoal['RESCUES_COMPLETED'],
        'target' => (int) $dailyGoal['DAILY_TARGET'],
        'percentage' => round(((int) $dailyGoal['RESCUES_COMPLETED'] / (int) $dailyGoal['DAILY_TARGET']) * 100)
    ],
    'history' => $history
]);
