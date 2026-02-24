<?php
// =============================================================
// config/db.php
// Oracle Database Connection Configuration
// Uses OCI8 PHP extension to connect to Oracle XE 21c
// =============================================================

// -----------------------------------------------------------
// Database Credentials
// Update these to match your Oracle XE installation
// -----------------------------------------------------------
define('DB_USERNAME', 'system');          // Oracle username (default: system or your schema user)
define('DB_PASSWORD', 'oracle');          // Oracle password (set during XE installation)
define('DB_CONNECTION_STRING', 'localhost:1521/XEPDB1'); // Oracle XE 21c plug-in DB: XEPDB1
                                                          // For older XE: use 'localhost:1521/XE'

// Firebase Cloud Messaging Server Key
// Get this from Firebase Console → Project Settings → Cloud Messaging
define('FCM_SERVER_KEY', 'YOUR_FCM_SERVER_KEY_HERE');
define('FCM_API_URL',    'https://fcm.googleapis.com/fcm/send');

// -----------------------------------------------------------
// getDBConnection()
// Returns a persistent Oracle OCI8 connection resource.
// Shared across requests for performance.
// -----------------------------------------------------------
function getDBConnection() {
    // oci_pconnect() = persistent connection (reuses existing connection per process)
    // Use oci_connect() if you prefer non-persistent connections
    $conn = oci_pconnect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING, 'AL32UTF8');

    if (!$conn) {
        // Fetch the OCI error detail
        $e = oci_error();
        // Log the error server-side (never expose DB credentials in response)
        error_log('Oracle Connection Error: ' . $e['message']);
        // Send a clean JSON error and stop execution
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please try again later.',
            'error'   => $e['message'] // Remove this line in production
        ]);
        exit;
    }

    return $conn;
}
