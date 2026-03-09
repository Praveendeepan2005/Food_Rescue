<?php
/**
 * pages/volunteer/pickup-details.php — Pickup Details
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'assignments';
$pageTitle = 'Pickup Details | Food Rescue';
$userId = $sessionUser['user_id'];
$alertId = (int) ($_GET['alert_id'] ?? 0);
$claimId = (int) ($_GET['claim_id'] ?? 0);

if (!$alertId) {
    header('Location: /pages/volunteer/assignments.php');
    exit();
}

// Handle status action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $payload = ['volunteer_id' => $userId, 'claim_id' => $claimId, 'alert_id' => $alertId];
    $eps = [
        'start_pickup' => '/volunteer/start_pickup.php',
        'mark_pickedup' => '/volunteer/update_pickup_status.php',
        'start_delivery' => '/volunteer/start_delivery.php',
        'mark_delivered' => '/volunteer/mark_delivered.php',
    ];
    if (isset($eps[$action])) {
        $res = apiCall($eps[$action], $payload);
        $_SESSION['flash'] = !empty($res['success'])
            ? ['type' => 'success', 'msg' => 'Status updated successfully!']
            : ['type' => 'error', 'msg' => $res['message'] ?? 'Action failed.'];
    }
    header("Location: /pages/volunteer/pickup-details.php?claim_id={$claimId}&alert_id={$alertId}");
    exit();
}

$res = apiCall("/volunteer/get_delivery_details.php?alert_id={$alertId}", [], 'GET');
$d = $res['data'] ?? [];

include __DIR__ . '/../../includes/header.php';

$status = strtoupper($d['DELIVERY_STATUS'] ?? $d['STATUS'] ?? 'PENDING');
$statusFlow = [
    'PENDING' => ['next' => 'start_pickup', 'label' => 'Start Pickup', 'icon' => 'fa-motorcycle', 'color' => '#7c3aed'],
    'ACTIVE' => ['next' => 'start_pickup', 'label' => 'Start Pickup', 'icon' => 'fa-motorcycle', 'color' => '#7c3aed'],
    'ASSIGNED' => ['next' => 'start_pickup', 'label' => 'Start Pickup', 'icon' => 'fa-motorcycle', 'color' => '#7c3aed'],
    'PICKUP_STARTED' => ['next' => 'mark_pickedup', 'label' => 'Mark Picked Up', 'icon' => 'fa-box-open', 'color' => '#d97706'],
    'ON_THE_WAY' => ['next' => 'mark_pickedup', 'label' => 'Mark Picked Up', 'icon' => 'fa-box-open', 'color' => '#d97706'],
    'FOOD_PICKED' => ['next' => 'start_delivery', 'label' => 'Start Delivery', 'icon' => 'fa-truck-fast', 'color' => '#1d4ed8'],
    'DELIVERING' => ['next' => 'mark_delivered', 'label' => 'Mark Delivered', 'icon' => 'fa-circle-check', 'color' => '#2E7D32'],
];
$action = $statusFlow[$status] ?? null;
?>
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    @media(max-width:768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px;
        opacity: 0;
        transform: translateY(16px);
        animation: cardUp .5s ease both;
    }

    .info-card:nth-child(1) {
        animation-delay: .05s;
    }

    .info-card:nth-child(2) {
        animation-delay: .15s;
    }

    .info-card:nth-child(3) {
        animation-delay: .25s;
    }

    .info-card:nth-child(4) {
        animation-delay: .35s;
    }

    @keyframes cardUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .info-card-title {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6B7280;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F3F4F6;
    }

    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }

    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .info-label {
        font-size: .72rem;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 2px;
    }

    .info-val {
        font-size: .9rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .action-card {
        background: linear-gradient(135deg, #1a3a2a, #2E7D32);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .06);
    }

    .action-card h3 {
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 6px;
    }

    .action-card p {
        color: rgba(255, 255, 255, .7);
        font-size: .82rem;
        margin: 0 0 18px;
    }

    .prog-track {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 24px;
    }

    .prog-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .prog-node::after {
        content: '';
        position: absolute;
        top: 16px;
        left: 50%;
        right: -50%;
        height: 2px;
        background: #E5E7EB;
        z-index: -1;
    }

    .prog-node:last-child::after {
        display: none;
    }

    .prog-node.done::after {
        background: #2E7D32;
    }

    .pn-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #E5E7EB;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        font-size: .85rem;
        margin-bottom: 6px;
        transition: transform .3s;
    }

    .pn-dot.done {
        background: #2E7D32;
        border-color: #2E7D32;
        color: #fff;
    }

    .pn-dot.current {
        background: #8b5cf6;
        border-color: #8b5cf6;
        color: #fff;
        animation: pulseDot 1.5s ease infinite;
    }

    @keyframes pulseDot {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(139, 92, 246, .5);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(139, 92, 246, 0);
        }
    }

    .pn-label {
        font-size: .62rem;
        font-weight: 700;
        text-align: center;
        color: #6B7280;
        text-transform: uppercase;
        max-width: 56px;
        line-height: 1.2;
    }

    .pn-label.done {
        color: #2E7D32;
    }

    .pn-label.current {
        color: #8b5cf6;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding:32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <!-- Page Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <a href="/pages/volunteer/assignments.php"
                    style="font-size:.82rem;color:#6B7280;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Assignments
                </a>
                <h1 style="font-size:1.5rem;font-weight:800;color:#111827;margin:0;">
                    <i class="fa-solid fa-route" style="color:#2E7D32;margin-right:8px;"></i>Pickup Details
                </h1>
            </div>
            <span
                style="background:#dcfce7;color:#16a34a;padding:4px 14px;border-radius:99px;font-size:.78rem;font-weight:700;">
                <?= $status ?>
            </span>
        </div>

        <?php if (empty($d)): ?>
            <div
                style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:60px;text-align:center;color:#9CA3AF;">
                <i class="fa-solid fa-circle-exclamation" style="font-size:2.5rem;display:block;margin-bottom:16px;"></i>
                Pickup details not found.
            </div>
        <?php else: ?>

            <!-- Progress Track -->
            <?php
            $progSteps = [
                ['icon' => 'fa-building', 'label' => 'Assigned', 'statuses' => ['ASSIGNED', 'ACTIVE', 'PENDING']],
                ['icon' => 'fa-motorcycle', 'label' => 'On The Way', 'statuses' => ['PICKUP_STARTED', 'ON_THE_WAY']],
                ['icon' => 'fa-box-open', 'label' => 'Picked Up', 'statuses' => ['FOOD_PICKED', 'PICKED_UP']],
                ['icon' => 'fa-truck-fast', 'label' => 'Delivering', 'statuses' => ['DELIVERING', 'ON_DELIVERY']],
                ['icon' => 'fa-circle-check', 'label' => 'Delivered', 'statuses' => ['COMPLETED', 'DELIVERED']],
            ];
            $statusOrder = ['PENDING', 'ACTIVE', 'ASSIGNED', 'PICKUP_STARTED', 'ON_THE_WAY', 'FOOD_PICKED', 'PICKED_UP', 'DELIVERING', 'ON_DELIVERY', 'COMPLETED', 'DELIVERED'];
            $curRank = array_search($status, $statusOrder) ?: 0;
            ?>
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:24px 28px;margin-bottom:24px;">
                <div style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:16px;">Delivery Progress</div>
                <div class="prog-track">
                    <?php foreach ($progSteps as $ps):
                        $maxRank = max(array_map(fn($s) => array_search($s, $statusOrder) ?: 0, $ps['statuses']));
                        $isCurrent = in_array($status, $ps['statuses']);
                        $isDone = !$isCurrent && $curRank > $maxRank;
                        $cls = $isCurrent ? 'current' : ($isDone ? 'done' : '');
                        ?>
                        <div class="prog-node <?= $isDone ? 'done' : '' ?>">
                            <div class="pn-dot <?= $cls ?>">
                                <?php if ($isDone): ?><i class="fa-solid fa-check" style="font-size:.75rem;"></i>
                                <?php else: ?><i class="fa-solid <?= $ps['icon'] ?>"
                                        style="font-size:.75rem;<?= $cls ? '' : 'color:#D1D5DB;' ?>"></i>
                                <?php endif; ?>
                            </div>
                            <div class="pn-label <?= $cls ?>">
                                <?= $ps['label'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Card (only if action available) -->
            <?php if ($action): ?>
                <div class="action-card">
                    <h3><i class="fa-solid fa-bolt"></i> Next Action</h3>
                    <p>Update the pickup status to keep things moving</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $action['next'] ?>">
                        <button type="submit"
                            style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#fff;color:<?= $action['color'] ?>;border:none;border-radius:8px;font-size:.95rem;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                            <i class="fa-solid <?= $action['icon'] ?>"></i>
                            <?= $action['label'] ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Info Cards -->
            <div class="detail-grid">
                <!-- Donation Info -->
                <div class="info-card">
                    <div class="info-card-title">
                        <div
                            style="width:28px;height:28px;border-radius:7px;background:#f59e0b18;color:#d97706;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-bowl-food"></i>
                        </div>
                        Donation Information
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#f59e0b18;color:#d97706;"><i
                                class="fa-solid fa-utensils"></i></div>
                        <div>
                            <div class="info-label">Food Name</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['FOOD_TYPE'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#8b5cf618;color:#7c3aed;"><i
                                class="fa-solid fa-scale-balanced"></i></div>
                        <div>
                            <div class="info-label">Quantity</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['QUANTITY'] ?? '—') ?> servings
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#0d948818;color:#0d9488;"><i class="fa-solid fa-tag"></i>
                        </div>
                        <div>
                            <div class="info-label">Category</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['CATEGORY'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#ef444418;color:#dc2626;"><i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div class="info-label">Expiry Time</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['EXPIRY_TIME'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($d['SPECIAL_INSTRUCTIONS'])): ?>
                        <div class="info-row">
                            <div class="info-icon" style="background:#6B728018;color:#374151;"><i
                                    class="fa-solid fa-note-sticky"></i></div>
                            <div>
                                <div class="info-label">Instructions</div>
                                <div class="info-val">
                                    <?= htmlspecialchars($d['SPECIAL_INSTRUCTIONS']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Donor Info -->
                <div class="info-card">
                    <div class="info-card-title">
                        <div
                            style="width:28px;height:28px;border-radius:7px;background:#1d4ed818;color:#1d4ed8;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        Donor Information
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#1d4ed818;color:#1d4ed8;"><i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <div class="info-label">Donor Name</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['DONOR_NAME'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#1d4ed818;color:#1d4ed8;"><i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <div class="info-label">Phone Number</div>
                            <div class="info-val">
                                <a href="tel:<?= htmlspecialchars($d['DONOR_PHONE'] ?? '') ?>"
                                    style="color:#1d4ed8;text-decoration:none;">
                                    <?= htmlspecialchars($d['DONOR_PHONE'] ?? $d['CONTACT_NUMBER'] ?? '—') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#1d4ed818;color:#1d4ed8;"><i
                                class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <div class="info-label">Pickup Address</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['PICKUP_ADDRESS'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#1d4ed818;color:#1d4ed8;"><i class="fa-solid fa-city"></i>
                        </div>
                        <div>
                            <div class="info-label">City</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['CITY'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NGO Info -->
                <div class="info-card">
                    <div class="info-card-title">
                        <div
                            style="width:28px;height:28px;border-radius:7px;background:#2E7D3218;color:#2E7D32;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        NGO Information
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#2E7D3218;color:#2E7D32;"><i
                                class="fa-solid fa-building"></i></div>
                        <div>
                            <div class="info-label">NGO Name</div>
                            <div class="info-val">
                                <?= htmlspecialchars($d['NGO_NAME'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background:#2E7D3218;color:#2E7D32;"><i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <div class="info-label">Contact Number</div>
                            <div class="info-val">
                                <a href="tel:<?= htmlspecialchars($d['NGO_PHONE'] ?? '') ?>"
                                    style="color:#2E7D32;text-decoration:none;">
                                    <?= htmlspecialchars($d['NGO_PHONE'] ?? '—') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($d['NGO_ADDRESS'])): ?>
                        <div class="info-row">
                            <div class="info-icon" style="background:#2E7D3218;color:#2E7D32;"><i
                                    class="fa-solid fa-map-pin"></i></div>
                            <div>
                                <div class="info-label">NGO Address</div>
                                <div class="info-val">
                                    <?= htmlspecialchars($d['NGO_ADDRESS']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Action Buttons -->
                <div class="info-card" style="background:#F8FAFC;">
                    <div class="info-card-title">
                        <div
                            style="width:28px;height:28px;border-radius:7px;background:#8b5cf618;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        Pickup Actions
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php
                        $actions = [
                            'start_pickup' => ['Start Pickup', 'fa-motorcycle', '#7c3aed', 'PENDING,ACTIVE,ASSIGNED'],
                            'mark_pickedup' => ['Mark Picked Up', 'fa-box-open', '#d97706', 'PICKUP_STARTED,ON_THE_WAY'],
                            'mark_delivered' => ['Mark Delivered', 'fa-circle-check', '#2E7D32', 'FOOD_PICKED,PICKED_UP,DELIVERING,ON_DELIVERY'],
                        ];
                        foreach ($actions as $aKey => [$aLabel, $aIcon, $aColor, $validStatuses]):
                            $isValid = in_array($status, explode(',', $validStatuses));
                            ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?= $aKey ?>">
                                <button type="submit" <?= $isValid ? '' : 'disabled' ?>
                                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:8px;font-size:.875rem;font-weight:700;cursor:
                            <?= $isValid ? 'pointer' : 'not-allowed' ?>;
                            background:
                            <?= $isValid ? $aColor : '#F3F4F6' ?>;color:
                            <?= $isValid ? '#fff' : '#9CA3AF' ?>;border:none;opacity:
                            <?= $isValid ? '1' : '.6' ?>;">
                                    <i class="fa-solid <?= $aIcon ?>"></i>
                                    <?= $aLabel ?>
                                </button>
                            </form>
                        <?php endforeach; ?>
                        <a href="/pages/volunteer/assignments.php"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:8px;font-size:.875rem;font-weight:600;background:#fff;color:#374151;border:1px solid #E5E7EB;text-decoration:none;">
                            <i class="fa-solid fa-arrow-left"></i> Back to All Assignments
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>