<?php
/**
 * pages/ngo/available.php — Available food donations for NGO to accept
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'available';
$pageTitle = 'Available Food | Food Rescue';
$userId = $sessionUser['user_id'];

// Handle Accept action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_alert_id'])) {
    $res = apiCall('/ngo/accept_donation.php', [
        'alert_id' => (int) $_POST['accept_alert_id'],
        'ngo_id' => $userId,
    ]);
    if (!empty($res['success'])) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Donation accepted! An alert has been sent to volunteers.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => $res['message'] ?? 'Failed to accept donation.'];
    }
    header('Location: /pages/ngo/available.php');
    exit();
}

$data = apiCall("/ngo/get_available_donations.php?ngo_id={$userId}", [], 'GET');
$donations = $data['data']['donations'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Available Donations</h1>
            <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">Accept donations available in your area. Act
                fast — food expires!</p>
        </div>

        <?php if (empty($donations)): ?>
            <div class="glass-card" style="text-align:center;padding:60px;color:#9CA3AF;">
                <i class="fa-solid fa-check-circle"
                    style="font-size:2.5rem;color:#4ADE80;margin-bottom:16px;display:block;"></i>
                No donations available right now. Check back soon!
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;">
                <?php foreach ($donations as $d):
                    $urgentBg = strtoupper($d['PRIORITY'] ?? '') === 'HIGH' ? '#FEF2F2' : '#fff';
                    ?>
                    <div class="glass-card" style="background:<?= $urgentBg ?>;position:relative;">
                        <?php if (strtoupper($d['PRIORITY'] ?? '') === 'HIGH'): ?>
                            <span
                                style="position:absolute;top:16px;right:16px;background:#D32F2F;color:#fff;font-size:0.7rem;font-weight:700;padding:3px 8px;border-radius:20px;">URGENT</span>
                        <?php endif; ?>
                        <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">
                            <?= htmlspecialchars($d['FOOD_TYPE']) ?>
                        </h3>
                        <p style="font-size:0.875rem;color:#6B7280;margin-bottom:12px;">
                            <?= htmlspecialchars($d['SPECIAL_INSTRUCTIONS'] ?? $d['DESCRIPTION'] ?? 'No description') ?>
                        </p>
                        <div style="display:flex;gap:16px;font-size:0.8rem;color:#6B7280;margin-bottom:16px;flex-wrap:wrap;">
                            <span><i class="fa-solid fa-bowl-food" style="color:#2E7D32;"></i>
                                <?= htmlspecialchars($d['QUANTITY']) ?> servings
                            </span>
                            <span><i class="fa-solid fa-location-dot" style="color:#2E7D32;"></i>
                                <?= htmlspecialchars($d['CITY'] ?? '—') ?>
                            </span>
                            <span><i class="fa-solid fa-clock" style="color:#f59e0b;"></i> Exp:
                                <?= htmlspecialchars($d['EXPIRY'] ?? $d['EXPIRY_TIME'] ?? '—') ?>
                            </span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="accept_alert_id" value="<?= $d['ALERT_ID'] ?>">
                            <button type="submit" class="btn btn-primary" style="width:100%;">
                                <i class="fa-solid fa-hand-holding"></i> Accept Donation
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>