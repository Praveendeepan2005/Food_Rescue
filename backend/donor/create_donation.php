<?php
// =============================================================
// donor/create_donation.php
// Create a new Food Donation (enhanced fields)
// Method: POST
// Body: { donor_id, food_name, category, quantity, preparation_time,
//         expiry_time, pickup_address, contact_number, special_instructions }
// =============================================================

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

setCORSHeaders();
requireMethod('POST');

$input = getRequestBody();
requireFields($input, ['donor_id', 'food_name', 'category', 'quantity', 'expiry_time', 'pickup_address', 'contact_number', 'city', 'priority']);

$donorId = (int) $input['donor_id'];
$foodName = trim($input['food_name']);
$category = strtoupper(trim($input['category'])); // VEG / NON_VEG / PACKED
$quantity = trim($input['quantity']);
$prepTime = trim($input['preparation_time'] ?? '');
$expiryTime = trim($input['expiry_time']);        // YYYY-MM-DD HH:MM:SS
$priority = trim($input['priority'] ?? 'Normal');
$pickupAddress = trim($input['pickup_address']);
$contactNumber = trim($input['contact_number']);
$city = trim($input['city']);
$specialNotes = trim($input['special_instructions'] ?? '');

// Validate expiry_time
if (!DateTime::createFromFormat('Y-m-d H:i:s', $expiryTime)) {
    sendError('Invalid expiry_time format. Use: YYYY-MM-DD HH:MM:SS', 422);
}
if (strtotime($expiryTime) <= time()) {
    sendError('expiry_time must be a future date and time.', 422);
}

// Validate category
$allowedCategories = ['VEG', 'NON_VEG', 'PACKED'];
if (!in_array($category, $allowedCategories)) {
    sendError('category must be one of: VEG, NON_VEG, PACKED', 422);
}

$conn = getDBConnection();

// Verify donor exists and has DONOR role
$chk = oci_parse($conn, "SELECT role FROM users WHERE user_id = :id");
oci_bind_by_name($chk, ':id', $donorId);
oci_execute($chk);
$donor = oci_fetch_assoc($chk);
oci_free_statement($chk);

if (!$donor)
    sendError('Donor not found.', 404);
if ($donor['ROLE'] !== 'DONOR')
    sendError('Access denied. DONOR role required.', 403);

// ── Geocode the pickup address via Nominatim (OpenStreetMap) ──────────────
$lat = 13.0827;   // fallback: Chennai, Tamil Nadu
$lng = 80.2707;

$geoUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($pickupAddress . ', ' . $city);
$geoCtx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: FoodRescueApp/1.0\r\n",
        'timeout' => 5
    ]
]);
$geoResult = @file_get_contents($geoUrl, false, $geoCtx);
if ($geoResult !== false) {
    $geoData = json_decode($geoResult, true);
    if (!empty($geoData)) {
        $lat = (float) $geoData[0]['lat'];
        $lng = (float) $geoData[0]['lon'];
    }
}

// ── NGO Assignment Logic ───────────────────────────────────────────────
$ngoId = null;
$selectionMode = $input['selection_mode'] ?? 'AUTO'; // AUTO, SPECIFIC, ANY
$manualNgoId = isset($input['target_ngo_id']) ? (int) $input['target_ngo_id'] : 0;

if ($selectionMode === 'SPECIFIC' && $manualNgoId > 0) {
    // Manually selected a specific NGO
    $ngoId = $manualNgoId;
    $status = 'NGO_ASSIGNED';
} elseif ($selectionMode === 'ANY') {
    // Open to everyone - do not assign any specific NGO
    $ngoId = null;
    $status = 'AVAILABLE';
} else {
    // ── Existing: Find Nearest NGO in the same city ────────────────────────
    $findNgoSql = "
        SELECT user_id as ngo_id,
        (6371 * acos(
            p_cos_lat_d * cos(latitude * 0.0174532925) * 
            cos((longitude * 0.0174532925) - p_rad_lng_d) + 
            p_sin_lat_d * sin(latitude * 0.0174532925)
        )) AS distance
        FROM users
        WHERE LOWER(city) = LOWER(:city) AND role = 'NGO' AND status = 'ACTIVE'
        ORDER BY distance ASC
        FETCH FIRST 1 ROWS ONLY
    ";

    $radLatD = $lat * 0.0174532925;
    $radLngD = $lng * 0.0174532925;
    $cosLatD = cos($radLatD);
    $sinLatD = sin($radLatD);

    $ngoStmt = oci_parse($conn, $findNgoSql);
    oci_bind_by_name($ngoStmt, ':city', $city);
    oci_bind_by_name($ngoStmt, ':p_cos_lat_d', $cosLatD);
    oci_bind_by_name($ngoStmt, ':p_rad_lng_d', $radLngD);
    oci_bind_by_name($ngoStmt, ':p_sin_lat_d', $sinLatD);

    oci_execute($ngoStmt);
    $ngoRow = oci_fetch_assoc($ngoStmt);
    if ($ngoRow) {
        $ngoId = (int) $ngoRow['NGO_ID'];
    }
    oci_free_statement($ngoStmt);

    $status = $ngoId ? 'NGO_ASSIGNED' : 'AVAILABLE';
}

// Insert into food_alerts
$insertSql = "
    INSERT INTO food_alerts
        (donor_id, food_type, category, quantity, preparation_time,
         expiry_time, pickup_address, contact_number, special_instructions,
         latitude, longitude, city, status, assigned_ngo_id, priority)
    VALUES
        (:donor_id, :food_name, :category, :quantity, 
         TO_DATE(:prep_time, 'YYYY-MM-DD HH24:MI:SS'),
         TO_DATE(:expiry_time, 'YYYY-MM-DD HH24:MI:SS'),
         :pickup_address, :contact_number, :special_notes,
         :lat, :lng, :city, :status, :ngo_id, :priority)
";

$stmt = oci_parse($conn, $insertSql);
oci_bind_by_name($stmt, ':donor_id', $donorId);
oci_bind_by_name($stmt, ':food_name', $foodName);
oci_bind_by_name($stmt, ':category', $category);
oci_bind_by_name($stmt, ':quantity', $quantity);
oci_bind_by_name($stmt, ':prep_time', $prepTime);
oci_bind_by_name($stmt, ':expiry_time', $expiryTime);
oci_bind_by_name($stmt, ':pickup_address', $pickupAddress);
oci_bind_by_name($stmt, ':contact_number', $contactNumber);
oci_bind_by_name($stmt, ':special_notes', $specialNotes);
oci_bind_by_name($stmt, ':lat', $lat);
oci_bind_by_name($stmt, ':lng', $lng);
oci_bind_by_name($stmt, ':city', $city);
oci_bind_by_name($stmt, ':status', $status);
oci_bind_by_name($stmt, ':ngo_id', $ngoId);
oci_bind_by_name($stmt, ':priority', $priority);

if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
    $err = oci_error($stmt);
    oci_rollback($conn);
    sendError('Failed to create donation: ' . $err['message'], 500);
}

oci_commit($conn);
oci_free_statement($stmt);

sendSuccess([
    'food_name' => $foodName,
    'category' => $category,
    'quantity' => $quantity,
    'expiry_time' => $expiryTime,
    'pickup_address' => $pickupAddress,
    'city' => $city,
    'status' => $status,
    'assigned_ngo_id' => $ngoId
], 'Donation created successfully.' . ($ngoId ? ' Assigned to nearest NGO.' : ' No NGO found in your city yet.'), 201);

