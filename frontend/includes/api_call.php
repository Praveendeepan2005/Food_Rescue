<?php
/**
 * api_call.php — Helper to call backend API from PHP frontend
 * Allows server-side PHP pages to communicate with backend JSON APIs.
 */

/**
 * Call the backend API with GET or POST.
 *
 * @param string $endpoint  e.g. '/auth/login.php'
 * @param array  $data      POST body array (empty = GET request)
 * @param string $method    'GET' or 'POST'
 * @return array            Decoded JSON response
 */
function apiCall(string $endpoint, array $data = [], string $method = 'POST'): array
{
    $baseUrl = 'http://localhost:8080/food-rescue-api';
    $url = $baseUrl . $endpoint;

    $options = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 3,
        ]
    ];

    if ($method === 'POST' && !empty($data)) {
        $options['http']['content'] = json_encode($data);
    } elseif ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return ['success' => false, 'message' => 'Could not connect to API server. Is the backend running?'];
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => 'Invalid response from server.'];
    }

    return $decoded;
}
