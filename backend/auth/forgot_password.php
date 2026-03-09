<?php
// =============================================================
// auth/forgot_password.php
// User Password Reset API
//
// Method : POST
// Request Body (JSON):
// {
//   "email"          : "user@example.com",
//   "new_password"   : "NewSecurePass123"
// }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['email', 'new_password']);

$email = trim($input['email']);
$new_password = trim($input['new_password']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email format.', 422);
}

if (strlen($new_password) < 6) {
    sendError('New password must be at least 6 characters long.', 422);
}

$conn = getDBConnection();

// Check if email exists
$checkSql = 'SELECT COUNT(*) AS cnt FROM users WHERE email = :email';
$checkStmt = oci_parse($conn, $checkSql);
oci_bind_by_name($checkStmt, ':email', $email);

if (!oci_execute($checkStmt)) {
    sendError('Database error.', 500);
}

$row = oci_fetch_assoc($checkStmt);
oci_free_statement($checkStmt);

if ((int) $row['CNT'] === 0) {
    sendError('No account found with this email address.', 404);
}

// Hash new password and update
$hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

$updateSql = 'UPDATE users SET password = :password WHERE email = :email';
$updateStmt = oci_parse($conn, $updateSql);

// Bind securely
oci_bind_by_name($updateStmt, ':password', $hashedPassword);
oci_bind_by_name($updateStmt, ':email', $email);

if (!oci_execute($updateStmt, OCI_NO_AUTO_COMMIT)) {
    oci_rollback($conn);
    sendError('Failed to reset password. Please try again.', 500);
}

// Commit changes
oci_commit($conn);
oci_free_statement($updateStmt);

sendSuccess([], 'Password has been successfully updated.', 200);
