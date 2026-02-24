<?php
// =============================================================
// notifications/send_notification.php
// Firebase Cloud Messaging (FCM) Push Notification Helper
//
// This file provides a reusable function to send push
// notifications to any mobile device registered with FCM.
//
// Used by:
//   - create_alert.php  → Notify all NGOs/Volunteers of new food
//   - claim_alert.php   → Notify donor that food was claimed
//   - update_status.php → Notify donor on completion/cancellation
//
// Requirements:
//   - FCM_SERVER_KEY defined in config/db.php
//   - FCM_API_URL defined in config/db.php
//   - PHP cURL extension enabled in php.ini
// =============================================================

require_once __DIR__ . '/../config/db.php';

/**
 * sendPushNotification()
 *
 * Sends a push notification to a single device via Firebase
 * Cloud Messaging (FCM) Legacy HTTP API.
 *
 * @param string $deviceToken  FCM registration token of the recipient device
 * @param string $title        Notification title (shown in OS notification tray)
 * @param string $body         Notification body text
 * @param array  $extraData    Optional key-value pairs for data payload (deep-links, IDs, etc.)
 *
 * @return array               Array with 'success' (bool) and 'response' (FCM response body)
 */
function sendPushNotification(
    string $deviceToken,
    string $title,
    string $body,
    array  $extraData = []
): array {

    // Guard: don't attempt if token is empty
    if (empty(trim($deviceToken))) {
        error_log('FCM Notification skipped: empty device token.');
        return ['success' => false, 'response' => 'Empty device token'];
    }

    // -------------------------------------------------------
    // Build FCM payload
    // "notification" block → shown in the notification tray
    // "data"         block → passed to the app silently (for custom handling)
    // -------------------------------------------------------
    $payload = [
        'to'           => $deviceToken,
        'priority'     => 'high',          // Ensures delivery even in Doze mode

        // Visible notification (OS tray)
        'notification' => [
            'title'        => $title,
            'body'         => $body,
            'sound'        => 'default',   // Play default notification sound
            'badge'        => 1,           // iOS badge count
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // For Flutter apps
        ],

        // Data payload (accessible in app even in background)
        'data' => array_merge([
            'title'     => $title,
            'body'      => $body,
            'timestamp' => date('Y-m-d H:i:s'),
        ], $extraData),
    ];

    $jsonPayload = json_encode($payload);

    // -------------------------------------------------------
    // Send via cURL POST to FCM endpoint
    // -------------------------------------------------------
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => FCM_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,   // Return response as string, don't echo
        CURLOPT_SSL_VERIFYPEER => true,   // Always verify SSL in production
        CURLOPT_TIMEOUT        => 10,     // 10 second timeout
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: key=' . FCM_SERVER_KEY,
        ],
        CURLOPT_POSTFIELDS     => $jsonPayload,
    ]);

    $response    = curl_exec($ch);
    $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError   = curl_error($ch);
    curl_close($ch);

    // -------------------------------------------------------
    // Handle cURL transport errors
    // -------------------------------------------------------
    if ($curlError) {
        error_log('FCM cURL error: ' . $curlError);
        return ['success' => false, 'response' => 'cURL Error: ' . $curlError];
    }

    // -------------------------------------------------------
    // Parse FCM response
    // FCM returns { "success": 1, "failure": 0, ... } on HTTP 200
    // -------------------------------------------------------
    $decoded = json_decode($response, true);

    if ($httpCode !== 200 || (isset($decoded['failure']) && $decoded['failure'] > 0)) {
        $errorReason = $decoded['results'][0]['error'] ?? 'Unknown FCM error';
        error_log("FCM notification failed → Token: {$deviceToken} | Error: {$errorReason}");
        return ['success' => false, 'response' => $errorReason];
    }

    return ['success' => true, 'response' => $decoded];
}


/**
 * sendBulkNotification()
 *
 * Sends the same notification to multiple devices at once
 * using FCM's "registration_ids" (multicast) — up to 1000 tokens.
 *
 * @param array  $deviceTokens  Array of FCM device tokens
 * @param string $title         Notification title
 * @param string $body          Notification body
 * @param array  $extraData     Optional data payload
 *
 * @return array  FCM raw response
 */
function sendBulkNotification(
    array  $deviceTokens,
    string $title,
    string $body,
    array  $extraData = []
): array {

    if (empty($deviceTokens)) {
        return ['success' => false, 'response' => 'No device tokens provided'];
    }

    // FCM supports max 1000 tokens per multicast request
    $chunks  = array_chunk($deviceTokens, 1000);
    $results = [];

    foreach ($chunks as $chunk) {
        $payload = [
            'registration_ids' => $chunk,   // Multicast key (instead of 'to')
            'priority'         => 'high',
            'notification'     => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
            ],
            'data' => array_merge([
                'title'     => $title,
                'body'      => $body,
                'timestamp' => date('Y-m-d H:i:s'),
            ], $extraData),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => FCM_API_URL,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: key=' . FCM_SERVER_KEY,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('FCM Bulk cURL error: ' . $curlError);
            $results[] = ['success' => false, 'error' => $curlError];
        } else {
            $results[] = ['success' => true, 'response' => json_decode($response, true)];
        }
    }

    return $results;
}
