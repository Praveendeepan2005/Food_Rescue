<?php
// =============================================================
// auth/login.php
// User Login API
//
// Method : POST
// URL    : http://localhost/food-rescue-api/auth/login.php
//
// Request Body (JSON):
// {
//   "email"        : "john@example.com",
//   "password"     : "SecurePass@1",
//   "device_token" : "FCM_DEVICE_TOKEN_HERE"   (optional)
// }
//
// Response:
// {
//   "success" : true,
//   "message" : "Login successful",
//   "data"    : {
//     "user_id"   : 1,
//     "name"      : "John Doe",
//     "email"     : "john@example.com",
//     "role"      : "DONOR",
//     "phone"     : "9876543210",
//     "latitude"  : 12.9716,
//     "longitude" : 77.5946
//   }
// }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

// -----------------------------------------------------------
// 1. Parse and validate input
// -----------------------------------------------------------
$input = getRequestBody();
requireFields($input, ['email', 'password']);

$email        = trim($input['email']);
$plainPass    = trim($input['password']);
$deviceToken  = isset($input['device_token']) ? trim($input['device_token']) : null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email address format.', 422);
}

// -----------------------------------------------------------
// 2. Connect to Oracle DB
// -----------------------------------------------------------
$conn = getDBConnection();

// -----------------------------------------------------------
// 3. Fetch user record by email
//    We retrieve the hashed password to verify with password_verify()
//    Only expose non-sensitive fields in the response later
// -----------------------------------------------------------
$sql = '
    SELECT user_id, name, email, password, role, phone, latitude, longitude, device_token
    FROM   users
    WHERE  email = :email
';

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':email', $email);

if (!oci_execute($stmt)) {
    $err = oci_error($stmt);
    error_log('Login fetch error: ' . $err['message']);
    sendError('Login failed. Please try again.', 500);
}

$user = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

// -----------------------------------------------------------
// 4. Verify user exists and password is correct
// -----------------------------------------------------------
// Do NOT distinguish between "user not found" and "wrong password"
// — this prevents user enumeration attacks
if (!$user || !password_verify($plainPass, $user['PASSWORD'])) {
    sendError('Invalid email or password.', 401);
}

// -----------------------------------------------------------
// 5. Update device_token if provided (for FCM notifications)
// -----------------------------------------------------------
if ($deviceToken !== null && $deviceToken !== $user['DEVICE_TOKEN']) {
    $updateSql  = 'UPDATE users SET device_token = :token WHERE user_id = :id';
    $updateStmt = oci_parse($conn, $updateSql);
    oci_bind_by_name($updateStmt, ':token', $deviceToken);
    $userId = (int)$user['USER_ID'];
    oci_bind_by_name($updateStmt, ':id', $userId);

    if (!oci_execute($updateStmt, OCI_NO_AUTO_COMMIT)) {
        $err = oci_error($updateStmt);
        error_log('Device token update error: ' . $err['message']);
        // Non-critical: do not block login if this fails
    } else {
        oci_commit($conn);
    }
    oci_free_statement($updateStmt);
}

// -----------------------------------------------------------
// 6. Return user data (NEVER include password in response)
// -----------------------------------------------------------
sendSuccess([
    'user_id'   => (int)$user['USER_ID'],
    'name'      => $user['NAME'],
    'email'     => $user['EMAIL'],
    'role'      => $user['ROLE'],
    'phone'     => $user['PHONE'],
    'latitude'  => $user['LATITUDE']  !== null ? (float)$user['LATITUDE']  : null,
    'longitude' => $user['LONGITUDE'] !== null ? (float)$user['LONGITUDE'] : null,
], 'Login successful. Welcome back, ' . $user['NAME'] . '!');
