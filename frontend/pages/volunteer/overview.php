<?php
/**
 * pages/volunteer/overview.php — Volunteer Dashboard
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'overview';
$pageTitle = 'Volunteer Dashboard | Food Link';
$userId = $sessionUser['user_id'];

$data = apiCall("/volunteer/get_vol_dashboard.php?volunteer_id={$userId}", [], 'GET');
$stats = $data['data']['stats'] ?? ['assigned' => 0, 'pending' => 0, 'completed' => 0, 'meals' => 0];
$recent = $data['data']['recent'] ?? [];

$liveData = apiCall("/volunteer/get_live_tracking.php?volunteer_id={$userId}", [], 'GET');
$liveDeliveries = $liveData['data']['deliveries'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>
<style>
    /* ── Stats Grid ─────────────────────────── */
    .vol-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    @media(max-width:900px) {
        .vol-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .vol-stat {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 22px 24px;
        position: relative;
        overflow: hidden;
        opacity: 0;
        transform: translateY(20px);
        animation: statIn 0.5s ease both;
    }

    .vol-stat:nth-child(1) {
        animation-delay: .05s
    }

    .vol-stat:nth-child(2) {
        animation-delay: .15s
    }

    .vol-stat:nth-child(3) {
        animation-delay: .25s
    }

    .vol-stat:nth-child(4) {
        animation-delay: .35s
    }

    @keyframes statIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .vol-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 12px 12px 0 0;
    }

    .vol-stat.s-purple::before {
        background: #8b5cf6;
    }

    .vol-stat.s-amber::before {
        background: #f59e0b;
    }

    .vol-stat.s-green::before {
        background: #2E7D32;
    }

    .vol-stat.s-teal::before {
        background: #0d9488;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 14px;
    }

    .stat-val {
        font-size: 2rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-lbl {
        font-size: 0.78rem;
        font-weight: 600;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .stat-sub {
        font-size: 0.72rem;
        color: #9CA3AF;
        margin-top: 6px;
    }

    /* ── Pipeline ───────────────────────────── */
    .pipe-wrap {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px 28px;
        margin-bottom: 28px;
    }

    .pipe-header {
        font-size: .95rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .pipe-sub {
        font-size: .78rem;
        color: #6B7280;
        margin-bottom: 22px;
    }

    /* ── Today's Tasks Table ────────────────── */
    .task-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 28px;
    }

    .task-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #F3F4F6;
    }

    .task-card-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .task-card-header p {
        font-size: .78rem;
        color: #6B7280;
        margin: 3px 0 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        background: #F8FAFC;
        padding: 10px 16px;
        text-align: left;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6B7280;
        letter-spacing: .4px;
    }

    tbody tr {
        border-top: 1px solid #F3F4F6;
        transition: background .15s;
    }

    tbody tr:hover {
        background: #FAFAFA;
    }

    tbody td {
        padding: 12px 16px;
        font-size: .875rem;
        color: #374151;
        vertical-align: middle;
    }

    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-active,
    .badge-assigned {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-on_the_way,
    .badge-pickup_started {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-food_picked,
    .badge-picked_up {
        background: #ffedd5;
        color: #c2410c;
    }

    .badge-delivering {
        background: #ede9fe;
        color: #7c3aed;
    }

    .badge-completed,
    .badge-delivered {
        background: #dcfce7;
        color: #16a34a;
    }

    .badge-pending {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-xs {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-green {
        background: #2E7D32;
        color: #fff;
    }

    .btn-purple {
        background: #8b5cf6;
        color: #fff;
        transition: all 0.2s;
    }

    .btn-purple:hover {
        background: #7c3aed;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
    }

    .btn-outline-sm {
        background: transparent;
        border: 1px solid #E5E7EB;
        color: #374151;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding:32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <!-- Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:#111827;">
                    Welcome, <?= htmlspecialchars($sessionUser['name']) ?>! 👋
                </h1>
                <p style="color:#6B7280;font-size:.875rem;margin-top:4px;">
                    <?= date('l, d F Y') ?> · Your volunteer dashboard
                </p>
            </div>
            <div style="display:flex; gap:12px;">
                <a href="/pages/volunteer/delivery-map.php" class="btn-xs"
                    style="font-size:.875rem;padding:10px 20px; background:#8b5cf6; color:#fff;">
                    <i class="fa-solid fa-route"></i> Live Map
                </a>
                <a href="/pages/volunteer/assignments.php" class="btn-xs btn-green"
                    style="font-size:.875rem;padding:10px 20px;">
                    <i class="fa-solid fa-truck"></i> My Assignments
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="vol-stats reveal">
            <div class="vol-stat s-purple">
                <div class="stat-icon" style="background:rgba(139,92,246,.12);color:#7c3aed;">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="stat-val"><span class="count-up" data-target="<?= $stats['assigned'] ?>">0</span></div>
                <div class="stat-lbl">Assigned Pickups</div>
                <div class="stat-sub">Currently in progress</div>
            </div>
            <div class="vol-stat s-amber">
                <div class="stat-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="stat-val"><span class="count-up" data-target="<?= $stats['pending'] ?>">0</span></div>
                <div class="stat-lbl">Pending Actions</div>
                <div class="stat-sub">Awaiting your response</div>
            </div>
            <div class="vol-stat s-green">
                <div class="stat-icon" style="background:rgba(46,125,50,.12);color:#2E7D32;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-val"><span class="count-up" data-target="<?= $stats['completed'] ?>">0</span></div>
                <div class="stat-lbl">Completed Pickups</div>
                <div class="stat-sub">Successfully delivered</div>
            </div>
            <div class="vol-stat s-teal">
                <div class="stat-icon" style="background:rgba(13,148,136,.12);color:#0d9488;">
                    <i class="fa-solid fa-bowl-food"></i>
                </div>
                <div class="stat-val"><span class="count-up" data-target="<?= $stats['meals'] ?>">0</span></div>
                <div class="stat-lbl">Meals Rescued</div>
                <div class="stat-sub">Estimated impact</div>
            </div>
        </div>

        <!-- Delivery Pipeline -->
        <div class="pipe-wrap reveal reveal-delay-2">
            <div class="pipe-header"><i class="fa-solid fa-route" style="color:#2E7D32;"></i> Volunteer Workflow</div>
            <div class="pipe-sub">Your step-by-step delivery journey</div>
            <div style="position:relative;padding:0 16px;">
                <div
                    style="position:absolute;top:30px;left:46px;right:46px;height:3px;background:#E5E7EB;border-radius:99px;">
                </div>
                <div
                    style="position:absolute;top:30px;left:46px;height:3px;width:0;background:linear-gradient(90deg,#8b5cf6,#2E7D32);border-radius:99px;animation:fillFlow 2.5s ease forwards;">
                </div>
                <div style="display:flex;justify-content:space-between;position:relative;z-index:2;">
                    <?php
                    $pwSteps = [
                        ['fa-building', '#8b5cf6', 'rgba(139,92,246,.12)', 'NGO Assigns'],
                        ['fa-bell', '#d97706', 'rgba(245,158,11,.12)', 'You Accept'],
                        ['fa-motorcycle', '#1d4ed8', 'rgba(59,130,246,.12)', 'Head to Donor'],
                        ['fa-box-open', '#c2410c', 'rgba(234,88,12,.12)', 'Collect Food'],
                        ['fa-people-group', '#2E7D32', 'rgba(46,125,50,.12)', 'Delivered!'],
                    ];
                    foreach ($pwSteps as $loop_index => $s): ?>
                        <div class="hover-scale reveal reveal-delay-<?= ($loop_index + 1) ?>"
                            style="width:60px;height:60px;border-radius:50%;background:<?= $s[2] ?>;border:2px solid <?= $s[1] ?>40;color:<?= $s[1] ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;transition:transform .3s;">
                            <i class="fa-solid <?= $s[0] ?>"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;padding:10px 16px 4px;">
                <?php foreach ($pwSteps as $s): ?>
                    <div
                        style="width:60px;text-align:center;font-size:.67rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.3px;line-height:1.3;">
                        <?= $s[3] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Today's Pickup Tasks -->
        <div class="task-card reveal reveal-delay-3">
            <div class="task-card-header">
                <div>
                    <h3><i class="fa-solid fa-list-check" style="color:#2E7D32;margin-right:8px;"></i>Today's Pickup
                        Tasks</h3>
                    <p>Your most recent delivery assignments</p>
                </div>
                <a href="/pages/volunteer/assignments.php" class="btn-xs btn-outline-sm">View All</a>
            </div>
            <?php if (empty($recent)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-truck"
                        style="font-size:2.5rem;display:block;margin-bottom:14px;color:#D1D5DB;"></i>
                    No assignments yet. Check back later!
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>Pickup Location</th>
                            <th>Donor</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r):
                            $st = strtolower($r['STATUS'] ?? '');
                            ?>
                            <tr>
                                <td style="color:#9CA3AF;font-size:.8rem;">#<?= htmlspecialchars($r['CLAIM_ID'] ?? '—') ?></td>
                                <td><strong><?= htmlspecialchars($r['FOOD_TYPE'] ?? '—') ?></strong></td>
                                <td><?= htmlspecialchars($r['QUANTITY'] ?? '—') ?></td>
                                <td
                                    style="color:#6B7280;font-size:.8rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($r['PICKUP_ADDRESS'] ?? '—') ?>
                                </td>
                                <td><?= htmlspecialchars($r['DONOR_NAME'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= $st ?>"><?= strtoupper($r['STATUS'] ?? '') ?></span></td>
                                <td>
                                    <a href="/pages/volunteer/pickup-details.php?claim_id=<?= $r['CLAIM_ID'] ?>&alert_id=<?= $r['ALERT_ID'] ?>"
                                        class="btn-xs btn-purple">
                                        <i class="fa-solid fa-circle-info"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Live Tracking Preview -->
        <div class="task-card reveal reveal-delay-4" style="margin-bottom:28px;">
            <div class="task-card-header">
                <div>
                    <h3><i class="fa-solid fa-map-location-dot" style="color:#8b5cf6;margin-right:8px;"></i>Live Mission
                        Map</h3>
                    <p>Visual overview of your active pickup and delivery routes</p>
                </div>
                <a href="/pages/volunteer/delivery-map.php" class="btn-xs btn-outline-sm">Open Full Map</a>
            </div>
            <div style="padding:12px;">
                <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
                <div id="quickMap" style="height:280px; border-radius:8px; border:1px solid #E5E7EB; z-index:1;"></div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recent)): ?>
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:24px;">
                <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:16px;">
                    <i class="fa-solid fa-chart-line" style="color:#8b5cf6;margin-right:8px;"></i>Recent Pickup Activity
                </h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach (array_slice($recent, 0, 4) as $i => $r):
                        $icons = ['fa-truck-fast', 'fa-box-open', 'fa-circle-check', 'fa-route'];
                        $colors = ['#8b5cf6', '#d97706', '#2E7D32', '#1d4ed8'];
                        $ic = $icons[$i % 4];
                        $col = $colors[$i % 4];
                        ?>
                        <a href="/pages/volunteer/pickup-details.php?claim_id=<?= $r['CLAIM_ID'] ?>&alert_id=<?= $r['ALERT_ID'] ?>"
                            style="display:flex;align-items:center;gap:14px;padding:12px;text-decoration:none;border-bottom:1px solid #F3F4F6;transition:background 0.2s;border-radius:8px;"
                            onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='transparent'">
                            <div
                                style="width:38px;height:38px;border-radius:10px;background:<?= $col ?>18;color:<?= $col ?>;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                                <i class="fa-solid <?= $ic ?>"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:.875rem;font-weight:600;color:#111827;">
                                    <?= htmlspecialchars($r['FOOD_TYPE'] ?? '') ?>
                                </div>
                                <div style="font-size:.75rem;color:#9CA3AF;">
                                    <?= htmlspecialchars($r['PICKUP_ADDRESS'] ?? '—') ?>
                                </div>
                            </div>
                            <span class="badge badge-<?= strtolower($r['STATUS'] ?? '') ?>"><?= $r['STATUS'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @keyframes fillFlow {
        to {
            width: calc(100% - 60px);
        }
    }
</style>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Count-up animation
        document.querySelectorAll('.count-up').forEach(el => {
            const target = +el.getAttribute('data-target');
            if (!target) return;
            let count = 0;
            const step = target / 40;
            const tick = () => {
                count = Math.min(count + step, target);
                el.textContent = Math.ceil(count).toLocaleString();
                if (count < target) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });

        // Quick Map Preview
        const qMap = L.map('quickMap', { zoomControl: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(qMap);
        const markers = L.featureGroup();
        const deliveries = <?= json_encode($liveDeliveries) ?>;

        const dIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [20, 32], iconAnchor: [10, 32]
        });
        const nIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [20, 32], iconAnchor: [10, 32]
        });

        const fIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-grey.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [16, 26], iconAnchor: [8, 26]
        });

        deliveries.forEach(d => {
            const st = (d.STATUS || '').toUpperCase();
            const isFinished = ['COMPLETED', 'DELIVERED'].includes(st);
            const dL = parseFloat(d.DONOR_LAT || 11.0168);
            const dLn = parseFloat(d.DONOR_LNG || 76.9558);
            const nL = parseFloat(d.NGO_LAT || dL + 0.01);
            const nLn = parseFloat(d.NGO_LNG || dLn + 0.01);

            if (isFinished) {
                L.marker([nL, nLn], { icon: fIcon }).addTo(markers);
            } else {
                L.marker([dL, dLn], { icon: dIcon }).addTo(markers);
                L.marker([nL, nLn], { icon: nIcon }).addTo(markers);
                const col = ['FOOD_PICKED', 'PICKED_UP', 'DELIVERING'].includes(st) ? '#2E7D32' : '#8b5cf6';
                L.polyline([[dL, dLn], [nL, nLn]], { color: col, weight: 2, opacity: 0.5, dashArray: '4,6' }).addTo(markers);
            }
        });

        markers.addTo(qMap);
        if (deliveries.length > 0) {
            qMap.fitBounds(markers.getBounds(), { padding: [20, 20] });
        } else {
            qMap.setView([11.0168, 76.9558], 12);
        }
    });
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>