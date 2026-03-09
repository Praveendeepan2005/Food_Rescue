<?php
require_once __DIR__ . '/frontend/includes/api_call.php';

$ngoId = 24; // Vasanthi (from debug output)
$res = apiCall("/ngo/get_ngo_dashboard.php?ngo_id=$ngoId", [], 'GET');

echo "RESPONSE FOR NGO $ngoId:\n";
echo json_encode($res, JSON_PRETTY_PRINT);
