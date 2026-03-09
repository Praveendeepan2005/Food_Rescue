<?php
// =============================================================
// auth/register.php
// User Registration API
//
// Method : POST
// URL    : http://localhost/food-rescue-api/auth/register.php
//
// Request Body (JSON):
// {
//   "name"     : "John Doe",
//   "email"    : "john@example.com",
//   "password" : "SecurePass@1",
//   "role"     : "DONOR",
//   "phone"    : "9876543210",
//   "latitude" : 12.9716,
//   "longitude": 77.5946
// }
// =============================================================

// Include helper files
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

// Set CORS headers and enforce POST
setCORSHeaders();
requireMethod('POST');

// -----------------------------------------------------------
// 1. Parse and validate input
// -----------------------------------------------------------
$input = getRequestBody();
requireFields($input, ['name', 'email', 'password', 'role']);

$name = trim($input['name']);
$email = trim($input['email']);
$password = trim($input['password']);
$role = strtoupper(trim($input['role']));
$phone = isset($input['phone']) ? trim($input['phone']) : null;
$city = isset($input['city']) ? trim($input['city']) : null;
$latitude = isset($input['latitude']) ? (float) $input['latitude'] : null;
$longitude = isset($input['longitude']) ? (float) $input['longitude'] : null;
$ngoId = isset($input['belongs_to_ngo_id']) ? (int) $input['belongs_to_ngo_id'] : null;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email address format.', 422);
}

// Validate role
$allowedRoles = ['DONOR', 'NGO', 'VOLUNTEER'];
if (!in_array($role, $allowedRoles)) {
    sendError('Invalid role. Must be DONOR, NGO, or VOLUNTEER.', 422);
}

// Validate password strength (min 6 chars)
if (strlen($password) < 6) {
    sendError('Password must be at least 6 characters long.', 422);
}

// -----------------------------------------------------------
// 2. Connect to Oracle DB
// -----------------------------------------------------------
$conn = getDBConnection();

// -----------------------------------------------------------
// 3. Check if email already exists (prevent duplicate accounts)
// -----------------------------------------------------------
$checkSql = 'SELECT COUNT(*) AS cnt FROM users WHERE email = :email';
$checkStmt = oci_parse($conn, $checkSql);
oci_bind_by_name($checkStmt, ':email', $email);

if (!oci_execute($checkStmt)) {
    $err = oci_error($checkStmt);
    error_log('Register check error: ' . $err['message']);
    sendError('Registration failed. Please try again.', 500);
}

$row = oci_fetch_assoc($checkStmt);
oci_free_statement($checkStmt);

if ((int) $row['CNT'] > 0) {
    sendError('An account with this email already exists.', 409);
}

// -----------------------------------------------------------
// 4. Hash password using bcrypt (PHP native password_hash)
// -----------------------------------------------------------
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// -----------------------------------------------------------
// 5. Insert new user using prepared OCI8 statement
// -----------------------------------------------------------
$insertSql = '
    INSERT INTO users (name, email, password, role, phone, city, latitude, longitude, belongs_to_ngo_id)
    VALUES (:name, :email, :password, :role, :phone, :city, :latitude, :longitude, :ngo_id)
';

$stmt = oci_parse($conn, $insertSql);

// Bind all parameters (prevents SQL injection)
oci_bind_by_name($stmt, ':name', $name);
oci_bind_by_name($stmt, ':email', $email);
oci_bind_by_name($stmt, ':password', $hashedPassword);
oci_bind_by_name($stmt, ':role', $role);
oci_bind_by_name($stmt, ':phone', $phone);
oci_bind_by_name($stmt, ':city', $city);
oci_bind_by_name($stmt, ':latitude', $latitude);
oci_bind_by_name($stmt, ':longitude', $longitude);
oci_bind_by_name($stmt, ':ngo_id', $ngoId);


// Execute with OCI_NO_AUTO_COMMIT so we control the transaction
if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($stmt);
    error_log('Register insert error: ' . $err['message']);
    oci_rollback($conn);
    sendError('Registration failed. Please try again.', 500);
}

// Commit transaction
oci_commit($conn);
oci_free_statement($stmt);

// -----------------------------------------------------------
// 6. Fetch the newly created user_id (for response)
// -----------------------------------------------------------
$fetchSql = 'SELECT user_id, created_at FROM users WHERE email = :email';
$fetchStmt = oci_parse($conn, $fetchSql);
oci_bind_by_name($fetchStmt, ':email', $email);
oci_execute($fetchStmt);
$newUser = oci_fetch_assoc($fetchStmt);
oci_free_statement($fetchStmt);

// -----------------------------------------------------------
// 7. Return success response
// -----------------------------------------------------------
sendSuccess([
    'user_id' => (int) $newUser['USER_ID'],
    'name' => $name,
    'email' => $email,
    'role' => $role,
    'created_at' => $newUser['CREATED_AT'],
], 'Registration successful. Welcome to Food Rescue!', 201);
