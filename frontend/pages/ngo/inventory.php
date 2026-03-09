<?php
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';
if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}
$activePage = 'inventory';
$pageTitle = 'Inventory | Food Rescue';
$userId = $sessionUser['user_id'];
$data = apiCall("/ngo/get_inventory.php?ngo_id={$userId}", [], 'GET');
$items = $data['data']['inventory'] ?? [];
include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Inventory</h1>
            <p style="color:#6B7280;font-size:0.875rem;">Current food inventory available at your NGO</p>
        </div>
        <div class="table-card">
            <?php if (empty($items)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-warehouse"
                        style="font-size:2.5rem;margin-bottom:16px;display:block;color:#D1D5DB;"></i>
                    Inventory is empty.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Added At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><strong>
                                        <?= htmlspecialchars($item['FOOD_TYPE'] ?? '—') ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($item['QUANTITY'] ?? '—') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item['STATUS'] ?? '—') ?>
                                </td>
                                <td style="color:#6B7280;font-size:0.8rem;">
                                    <?= htmlspecialchars($item['ADDED_AT'] ?? '—') ?>
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