<?php
// =============================================================
// utils/response.php
// Central JSON Response Helper
// All API endpoints use these helpers for consistent output
// =============================================================

// Suppress HTML error output to ensure clean JSON responses
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Send a standardized JSON success response and terminate.
 *
 * @param mixed  $data    Data payload (array, object, or null)
 * @param string $message Human-readable success message
 * @param int    $code    HTTP status code (default 200)
 */
function sendSuccess($data = null, string $message = 'Success', int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    // Only include 'data' key when there is actual data to send
    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a standardized JSON error response and terminate.
 *
 * @param string $message Human-readable error message
 * @param int    $code    HTTP status code (default 400)
 * @param mixed  $errors  Optional detailed field-level errors
 */
function sendError(string $message = 'An error occurred', int $code = 400, $errors = null): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Enforce that the request method matches the expected one.
 * Sends 405 Method Not Allowed if it does not.
 *
 * @param string $method Expected HTTP method (GET, POST, PUT, etc.)
 */
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        sendError('Method Not Allowed. Expected: ' . strtoupper($method), 405);
    }
}

/**
 * Parse and return the JSON body from the incoming request.
 * Terminates with 400 if the JSON is malformed or empty.
 *
 * @return array Decoded JSON body as an associative array
 */
function getRequestBody(): array
{
    $raw = file_get_contents('php://input');

    if (empty($raw)) {
        sendError('Request body is empty. Please send a valid JSON payload.', 400);
    }

    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON format: ' . json_last_error_msg(), 400);
    }

    return $data;
}

/**
 * Validate that required fields are present and non-empty in the input array.
 * Sends 422 Unprocessable Entity if any required field is missing.
 *
 * @param array $input    Input data array (usually from getRequestBody())
 * @param array $required List of required field names
 */
function requireFields(array $input, array $required): void
{
    $missing = [];

    foreach ($required as $field) {
        if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        sendError('Missing required fields: ' . implode(', ', $missing), 422, $missing);
    }
}

/**
 * Set common CORS headers to allow cross-origin requests (for Postman / mobile apps).
 * Call this at the very top of every API endpoint.
 */
function setCORSHeaders(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    // Preflight request (OPTIONS) - respond immediately
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
