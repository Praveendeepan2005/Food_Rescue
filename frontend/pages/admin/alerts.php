<?php
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';
if ($sessionUser['role'] !== 'ADMIN') {
    header('Location: /dashboard.php');
    exit();
}
$activePage = 'alerts';
$pageTitle = 'Manage Alerts | Admin | Food Rescue';
$data = apiCall('/admin/manage_alerts.php', [], 'GET');
$alerts = $data['data']['alerts'] ?? [];
include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">All Alerts</h1>
            <p style="color:#6B7280;font-size:0.875rem;">Platform-wide food rescue alerts</p>
        </div>
        <div class="table-card">
            <?php if (empty($alerts)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">No alerts found.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Food Type</th>
                            <th>Qty</th>
                            <th>Donor</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td style="color:#9CA3AF;">#<?= $a['ALERT_ID'] ?></td>
                                <td><strong><?= htmlspecialchars($a['FOOD_TYPE'] ?? '—') ?></strong></td>
                                <td><?= htmlspecialchars($a['QUANTITY'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($a['DONOR_NAME'] ?? '—') ?></td>
                                <td><span
                                        class="badge badge-<?= strtolower($a['STATUS'] ?? '') ?>"><?= htmlspecialchars($a['STATUS'] ?? '—') ?></span>
                                </td>
                                <td style="color:#6B7280;font-size:0.8rem;"><?= htmlspecialchars($a['CREATED_AT'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>