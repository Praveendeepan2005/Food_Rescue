<?php
// =============================================================
// test_connection.php
// Diagnostic page — verifies PHP, OCI8, and Oracle DB connectivity
// URL: http://localhost/food-rescue-api/test_connection.php
// ⚠️  DELETE or restrict this file before going to production!
// =============================================================
header('Content-Type: text/html; charset=utf-8');

$checks = [];

// 1. PHP Version
$checks['PHP Version'] = [
    'ok'    => version_compare(PHP_VERSION, '8.0.0', '>='),
    'value' => PHP_VERSION,
];

// 2. OCI8 Extension
$checks['OCI8 Extension'] = [
    'ok'    => extension_loaded('oci8'),
    'value' => extension_loaded('oci8') ? 'Loaded ✅' : 'NOT loaded ❌',
];

// 3. cURL Extension (for FCM)
$checks['cURL Extension'] = [
    'ok'    => extension_loaded('curl'),
    'value' => extension_loaded('curl') ? 'Loaded ✅' : 'NOT loaded ❌',
];

// 4. Oracle DB Connection Test
$dbUser   = 'foodrescue';
$dbPass   = 'FoodRescue2025';
$dbDsn    = 'localhost:1521/XEPDB1';
$conn     = null;
$dbMsg    = '';
$dbTables = [];

if (extension_loaded('oci8')) {
    $conn = @oci_connect($dbUser, $dbPass, $dbDsn, 'AL32UTF8');
    if ($conn) {
        $dbMsg = 'Connected to Oracle XE 21c ✅';
        // Check tables
        $stmt = oci_parse($conn, "SELECT table_name FROM user_tables WHERE table_name IN ('USERS','FOOD_ALERTS','CLAIMS') ORDER BY table_name");
        oci_execute($stmt);
        while ($row = oci_fetch_assoc($stmt)) {
            $dbTables[] = $row['TABLE_NAME'];
        }
        oci_free_statement($stmt);
    } else {
        $e     = oci_error();
        $dbMsg = 'Connection FAILED ❌ → ' . ($e['message'] ?? 'Unknown error');
    }
} else {
    $dbMsg = 'OCI8 not loaded — cannot test DB connection';
}

$checks['Oracle DB Connection'] = [
    'ok'    => $conn !== null,
    'value' => $dbMsg,
];

// Table check
$expectedTables = ['CLAIMS', 'FOOD_ALERTS', 'USERS'];
$missingTables  = array_diff($expectedTables, $dbTables);
$checks['Database Tables'] = [
    'ok'    => empty($missingTables),
    'value' => empty($missingTables)
        ? 'All 3 tables found: ' . implode(', ', $dbTables) . ' ✅'
        : 'Missing: ' . implode(', ', $missingTables) . ' ❌  (Run oracle_setup.sql)',
];

// Row counts
$rowCounts = [];
if ($conn) {
    foreach (['USERS', 'FOOD_ALERTS', 'CLAIMS'] as $tbl) {
        $s = oci_parse($conn, "SELECT COUNT(*) AS cnt FROM {$tbl}");
        oci_execute($s);
        $r = oci_fetch_assoc($s);
        $rowCounts[$tbl] = (int)$r['CNT'];
        oci_free_statement($s);
    }
    oci_close($conn);
}

$allOk = !in_array(false, array_column($checks, 'ok'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Rescue API – System Check</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Inter',sans-serif; background:#0f172a; color:#e2e8f0; min-height:100vh; padding:40px 20px; }
  .container { max-width:760px; margin:0 auto; }
  .header { text-align:center; margin-bottom:40px; }
  .header h1 { font-size:2rem; font-weight:700; background:linear-gradient(135deg,#10b981,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:8px; }
  .header p  { color:#64748b; font-size:.95rem; }
  .banner    { border-radius:12px; padding:16px 20px; text-align:center; font-weight:600; font-size:1.1rem; margin-bottom:32px; }
  .banner.ok  { background:rgba(16,185,129,.15); border:1px solid #10b981; color:#10b981; }
  .banner.err { background:rgba(239,68,68,.15);  border:1px solid #ef4444; color:#ef4444; }
  .card      { background:#1e293b; border-radius:12px; padding:24px; margin-bottom:20px; border:1px solid #334155; }
  .card h2   { font-size:1rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px; }
  .check-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #1e3a5f; }
  .check-row:last-child { border-bottom:none; }
  .check-label { font-weight:500; color:#cbd5e1; }
  .check-value { font-size:.875rem; color:#94a3b8; max-width:60%; text-align:right; word-break:break-all; }
  .badge { display:inline-block; padding:3px 10px; border-radius:9999px; font-size:.75rem; font-weight:600; }
  .badge.ok  { background:rgba(16,185,129,.2); color:#10b981; }
  .badge.err { background:rgba(239,68,68,.2);  color:#ef4444; }
  .table-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:4px; }
  .tbl-card  { background:#0f172a; border-radius:8px; padding:16px; text-align:center; border:1px solid #334155; }
  .tbl-name  { font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
  .tbl-count { font-size:2rem; font-weight:700; color:#3b82f6; }
  .apis      { display:grid; gap:8px; }
  .api-row   { display:flex; align-items:center; gap:12px; padding:10px 14px; background:#0f172a; border-radius:8px; border:1px solid #334155; }
  .method    { font-size:.7rem; font-weight:700; padding:3px 8px; border-radius:4px; min-width:44px; text-align:center; }
  .method.get  { background:rgba(16,185,129,.2); color:#10b981; }
  .method.post { background:rgba(59,130,246,.2); color:#60a5fa; }
  .api-path  { font-family:monospace; font-size:.85rem; color:#e2e8f0; }
  .api-desc  { font-size:.8rem; color:#64748b; margin-left:auto; }
  .warn      { background:rgba(245,158,11,.1); border:1px solid #f59e0b; border-radius:8px; padding:12px 16px; color:#fbbf24; font-size:.85rem; margin-top:20px; text-align:center; }
  a { color:#3b82f6; text-decoration:none; }
  a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>🍱 Food Rescue API</h1>
    <p>System Diagnostic · <?= date('Y-m-d H:i:s') ?></p>
  </div>

  <div class="banner <?= $allOk ? 'ok' : 'err' ?>">
    <?= $allOk ? '✅ All systems operational — API is ready!' : '⚠️ One or more checks failed — see details below' ?>
  </div>

  <!-- Environment Checks -->
  <div class="card">
    <h2>Environment Checks</h2>
    <?php foreach ($checks as $label => $check): ?>
    <div class="check-row">
      <div class="check-label"><?= htmlspecialchars($label) ?></div>
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="check-value"><?= htmlspecialchars($check['value']) ?></div>
        <span class="badge <?= $check['ok'] ? 'ok' : 'err' ?>"><?= $check['ok'] ? 'PASS' : 'FAIL' ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Table Row Counts -->
  <?php if (!empty($rowCounts)): ?>
  <div class="card">
    <h2>Database · Row Counts</h2>
    <div class="table-grid">
      <?php foreach ($rowCounts as $tbl => $cnt): ?>
      <div class="tbl-card">
        <div class="tbl-name"><?= $tbl ?></div>
        <div class="tbl-count"><?= $cnt ?></div>
        <div style="font-size:.75rem;color:#64748b;margin-top:4px;">rows</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- API Endpoints -->
  <div class="card">
    <h2>Available API Endpoints</h2>
    <div class="apis">
      <div class="api-row"><span class="method post">POST</span><span class="api-path">/auth/register.php</span><span class="api-desc">Register user</span></div>
      <div class="api-row"><span class="method post">POST</span><span class="api-path">/auth/login.php</span><span class="api-desc">User login</span></div>
      <div class="api-row"><span class="method post">POST</span><span class="api-path">/alerts/create_alert.php</span><span class="api-desc">Create food alert (DONOR)</span></div>
      <div class="api-row"><span class="method get">GET</span><span class="api-path">/alerts/get_nearby_alerts.php</span><span class="api-desc">Nearby alerts (lat/lon/radius)</span></div>
      <div class="api-row"><span class="method post">POST</span><span class="api-path">/alerts/claim_alert.php</span><span class="api-desc">Claim alert (NGO/VOLUNTEER)</span></div>
      <div class="api-row"><span class="method post">POST</span><span class="api-path">/alerts/update_status.php</span><span class="api-desc">Mark COMPLETED / CANCELLED</span></div>
    </div>
  </div>

  <?php if (!$conn && !extension_loaded('oci8')): ?>
  <!-- OCI8 Setup Help -->
  <div class="card">
    <h2>⚙️ OCI8 Setup Steps</h2>
    <ol style="padding-left:20px;line-height:2;color:#94a3b8;font-size:.9rem;">
      <li>Download <a href="https://pecl.php.net/package/oci8" target="_blank">OCI8 DLL from PECL</a> (PHP 8.2, x64)</li>
      <li>Copy <code>php_oci8_19.dll</code> → <code>C:\xampp\php\ext\</code></li>
      <li>In <code>php.ini</code> ensure: <code>extension=oci8_19</code> (no semicolon)</li>
      <li>Add Oracle Instant Client path to Windows <strong>System PATH</strong></li>
      <li>Restart Apache from XAMPP Control Panel</li>
    </ol>
  </div>
  <?php endif; ?>

  <div class="warn">
    ⚠️ <strong>Security Notice:</strong> Delete or password-protect
    <code>test_connection.php</code> before deploying to production.
  </div>
</div>
</body>
</html>
