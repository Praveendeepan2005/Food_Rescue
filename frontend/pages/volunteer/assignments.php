<?php
/**
 * pages/volunteer/assignments.php — Assigned Pickups
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'assignments';
$pageTitle = 'Assigned Pickups | Food Rescue';
$userId = $sessionUser['user_id'];

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $claimId = (int) ($_POST['claim_id'] ?? 0);
    $alertId = (int) ($_POST['alert_id'] ?? 0);
    $payload = ['volunteer_id' => $userId, 'claim_id' => $claimId, 'alert_id' => $alertId];

    $endpoints = [
        'start_pickup' => '/volunteer/start_pickup.php',
        'mark_pickedup' => '/volunteer/update_pickup_status.php',
        'start_delivery' => '/volunteer/start_delivery.php',
        'mark_delivered' => '/volunteer/mark_delivered.php',
    ];
    $endpoint = $endpoints[$action] ?? '';
    if ($endpoint) {
        $res = apiCall($endpoint, $payload);
        $_SESSION['flash'] = !empty($res['success'])
            ? ['type' => 'success', 'msg' => 'Status updated!']
            : ['type' => 'error', 'msg' => $res['message'] ?? 'Failed.'];
    }
    header('Location: /pages/volunteer/assignments.php');
    exit();
}

$data = apiCall("/volunteer/get_assigned_deliveries.php?volunteer_id={$userId}", [], 'GET');
$assignments = $data['data']['deliveries'] ?? [];

include __DIR__ . '/../../includes/header.php';

// Status flow definition
$statusFlow = [
    'ACTIVE' => ['next' => 'start_pickup', 'label' => 'Start Pickup', 'icon' => 'fa-motorcycle', 'color' => '#7c3aed'],
    'ASSIGNED' => ['next' => 'start_pickup', 'label' => 'Start Pickup', 'icon' => 'fa-motorcycle', 'color' => '#7c3aed'],
    'PICKUP_STARTED' => ['next' => 'mark_pickedup', 'label' => 'Mark Picked Up', 'icon' => 'fa-box-open', 'color' => '#d97706'],
    'ON_THE_WAY' => ['next' => 'mark_pickedup', 'label' => 'Mark Picked Up', 'icon' => 'fa-box-open', 'color' => '#d97706'],
    'FOOD_PICKED' => ['next' => 'start_delivery', 'label' => 'Start Delivery', 'icon' => 'fa-truck-fast', 'color' => '#1d4ed8'],
    'PICKED_UP' => ['next' => 'start_delivery', 'label' => 'Start Delivery', 'icon' => 'fa-truck-fast', 'color' => '#1d4ed8'],
    'DELIVERING' => ['next' => 'mark_delivered', 'label' => 'Mark Delivered', 'icon' => 'fa-circle-check', 'color' => '#2E7D32'],
    'ON_DELIVERY' => ['next' => 'mark_delivered', 'label' => 'Mark Delivered', 'icon' => 'fa-circle-check', 'color' => '#2E7D32'],
];

$statusBadgeColors = [
    'ASSIGNED' => ['#dbeafe', '#1d4ed8'],
    'ACTIVE' => ['#dbeafe', '#1d4ed8'],
    'PICKUP_STARTED' => ['#fef3c7', '#d97706'],
    'ON_THE_WAY' => ['#fef3c7', '#d97706'],
    'FOOD_PICKED' => ['#ffedd5', '#c2410c'],
    'PICKED_UP' => ['#ffedd5', '#c2410c'],
    'DELIVERING' => ['#ede9fe', '#7c3aed'],
    'ON_DELIVERY' => ['#ede9fe', '#7c3aed'],
    'COMPLETED' => ['#dcfce7', '#16a34a'],
    'DELIVERED' => ['#dcfce7', '#16a34a'],
];
?>
<style>
    .assign-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 14px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: center;
        opacity: 0;
        transform: translateX(-16px);
        animation: slideInLeft .4s ease both;
        transition: box-shadow .2s, border-color .2s;
    }

    .assign-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        border-color: #2E7D32;
    }

    @keyframes slideInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .ac-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .72rem;
        font-weight: 700;
    }

    .ac-meta {
        display: flex;
        gap: 20px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .ac-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .78rem;
        color: #6B7280;
    }

    .ac-meta-item i {
        color: #9CA3AF;
        width: 12px;
    }

    .progress-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #F3F4F6;
        gap: 6px;
    }

    .prog-step {
        flex: 1;
        text-align: center;
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #9CA3AF;
        position: relative;
    }

    .prog-step.done {
        color: #2E7D32;
    }

    .prog-step.active {
        color: #8b5cf6;
    }

    .prog-step::after {
        content: '';
        display: block;
        height: 2px;
        background: #E5E7EB;
        border-radius: 99px;
        margin-top: 4px;
    }

    .prog-step.done::after {
        background: #2E7D32;
    }

    .prog-step.active::after {
        background: #8b5cf6;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding:32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#111827;">
                    <i class="fa-solid fa-truck" style="color:#8b5cf6;margin-right:10px;"></i>Assigned Pickups
                </h1>
                <p style="color:#6B7280;font-size:.875rem;margin-top:4px;">
                    <?= count($assignments) ?> task<?= count($assignments) !== 1 ? 's' : '' ?> assigned — update status
                    as you progress
                </p>
            </div>
            <a href="/pages/volunteer/history.php" class="btn btn-outline" style="font-size:.82rem;">
                <i class="fa-solid fa-clock-rotate-left"></i> View History
            </a>
        </div>

        <!-- Volunteer Guidelines Banner -->
        <div
            style="background: linear-gradient(to right, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; display: flex; gap: 20px; align-items: flex-start;">
            <div
                style="background: #2E7D32; color: #fff; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 8px 0; color: #166534; font-size: 1.1rem; font-weight: 800;">Volunteer Guidelines
                </h3>
                <p style="margin: 0 0 12px 0; color: #15803d; font-size: 0.9rem; line-height: 1.5;">
                    Thank you for rescuing food! Please follow these best practices while completing your assignments:
                </p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <i class="fa-solid fa-box-open" style="color: #2E7D32; margin-top: 3px;"></i>
                        <span style="font-size: 0.85rem; color: #166534;"><strong>Check Quality:</strong> Verify the
                            food matches the description and is safely packaged before picking it up.</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <i class="fa-solid fa-temperature-arrow-up" style="color: #2E7D32; margin-top: 3px;"></i>
                        <span style="font-size: 0.85rem; color: #166534;"><strong>Maintain Temp:</strong> Keep hot food
                            hot and cold food cold during transit if possible.</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: flex-start;">
                        <i class="fa-solid fa-mobile-screen" style="color: #2E7D32; margin-top: 3px;"></i>
                        <span style="font-size: 0.85rem; color: #166534;"><strong>Update Status:</strong> Frequently
                            update the status buttons so the donor and NGO track real-time progress.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Flow Legend -->
        <div
            style="background:#fff;border:1px solid #E5E7EB;border-radius:10px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:.75rem;font-weight:700;color:#374151;margin-right:4px;">Status Flow:</span>
            <?php foreach (['Assigned', 'On The Way', 'Food Picked', 'Delivering', 'Delivered'] as $i => $lbl):
                $sCols = ['#1d4ed8', '#d97706', '#c2410c', '#7c3aed', '#16a34a'];
                ?>
                <span
                    style="font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:99px;background:<?= $sCols[$i] ?>18;color:<?= $sCols[$i] ?>;"><?= $lbl ?></span>
                <?php if ($i < 4): ?><i class="fa-solid fa-chevron-right"
                        style="font-size:.65rem;color:#D1D5DB;"></i><?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (empty($assignments)): ?>
            <div
                style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:80px;text-align:center;color:#9CA3AF;">
                <i class="fa-solid fa-truck-fast"
                    style="font-size:3rem;display:block;margin-bottom:16px;color:#D1D5DB;"></i>
                <p style="font-size:1rem;font-weight:600;margin-bottom:6px;">No active assignments</p>
                <p style="font-size:.875rem;">Your NGO will assign pickup tasks here. Stay tuned!</p>
            </div>
        <?php else: ?>
            <?php foreach ($assignments as $i => $a):
                $status = strtoupper($a['STATUS'] ?? 'ASSIGNED');
                $flow = $statusFlow[$status] ?? null;
                $bc = $statusBadgeColors[$status] ?? ['#F3F4F6', '#374151'];
                $allStatuses = ['ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED', 'DELIVERING', 'COMPLETED'];
                $curIdx = array_search($status, $allStatuses);
                ?>
                <div class="assign-card" style="animation-delay:<?= $i * 0.07 ?>s;">
                    <div>
                        <!-- Title row -->
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                            <div
                                style="width:44px;height:44px;border-radius:10px;background:#8b5cf618;color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <div>
                                <div style="font-size:1rem;font-weight:700;color:#111827;">
                                    <?= htmlspecialchars($a['FOOD_TYPE'] ?? '—') ?>
                                    <span
                                        style="font-size:.75rem;font-weight:400;color:#9CA3AF;margin-left:6px;">#<?= $a['CLAIM_ID'] ?></span>
                                </div>
                                <span class="ac-badge"
                                    style="background:<?= $bc[0] ?>;color:<?= $bc[1] ?>;"><?= $status ?></span>
                            </div>
                        </div>

                        <!-- Meta row -->
                        <div class="ac-meta">
                            <div class="ac-meta-item"><i
                                    class="fa-solid fa-scale-balanced"></i><?= htmlspecialchars($a['QUANTITY'] ?? '—') ?></div>
                            <div class="ac-meta-item"><i
                                    class="fa-solid fa-user"></i><?= htmlspecialchars($a['DONOR_NAME'] ?? '—') ?></div>
                            <div class="ac-meta-item"><i
                                    class="fa-solid fa-location-dot"></i><?= htmlspecialchars(substr($a['PICKUP_ADDRESS'] ?? '—', 0, 50)) ?>
                            </div>
                            <div class="ac-meta-item"><i
                                    class="fa-solid fa-building"></i><?= htmlspecialchars($a['NGO_NAME'] ?? '—') ?></div>
                            <?php if (!empty($a['CITY'])): ?>
                                <div class="ac-meta-item"><i class="fa-solid fa-city"></i><?= htmlspecialchars($a['CITY']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress-row">
                            <?php foreach (['Assigned', 'On Way', 'Picked Up', 'Delivering', 'Done'] as $si => $sl):
                                $cls = ($si < $curIdx) ? 'done' : (($si === $curIdx) ? 'active' : '');
                                ?>
                                <div class="prog-step <?= $cls ?>"><?= $sl ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Action Column -->
                    <div style="display:flex;flex-direction:column;gap:8px;min-width:140px;">
                        <a href="/pages/volunteer/pickup-details.php?claim_id=<?= $a['CLAIM_ID'] ?>&alert_id=<?= $a['ALERT_ID'] ?>"
                            style="display:flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:8px;background:#F3F4F6;color:#374151;font-size:.8rem;font-weight:600;text-decoration:none;border:1px solid #E5E7EB;">
                            <i class="fa-solid fa-eye"></i> View Details
                        </a>
                        <?php if ($flow): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?= $flow['next'] ?>">
                                <input type="hidden" name="claim_id" value="<?= $a['CLAIM_ID'] ?>">
                                <input type="hidden" name="alert_id" value="<?= $a['ALERT_ID'] ?>">
                                <button type="submit"
                                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:8px;background:<?= $flow['color'] ?>;color:#fff;font-size:.8rem;font-weight:700;border:none;cursor:pointer;">
                                    <i class="fa-solid <?= $flow['icon'] ?>"></i> <?= $flow['label'] ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>