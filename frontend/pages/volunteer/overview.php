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
$pageTitle = 'Volunteer Dashboard | Food Rescue';
$userId = $sessionUser['user_id'];

$data = apiCall("/volunteer/get_vol_dashboard.php?volunteer_id={$userId}", [], 'GET');
$stats = $data['data']['stats'] ?? ['assigned' => 0, 'pending' => 0, 'completed' => 0, 'meals' => 0];
$recent = $data['data']['recent'] ?? [];

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
            <a href="/pages/volunteer/assignments.php" class="btn-xs btn-green"
                style="font-size:.875rem;padding:10px 20px;">
                <i class="fa-solid fa-truck"></i> My Assignments
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="vol-stats">
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
        <div class="pipe-wrap">
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
                    foreach ($pwSteps as $s): ?>
                        <div style="width:60px;height:60px;border-radius:50%;background:<?= $s[2] ?>;border:2px solid <?= $s[1] ?>40;color:<?= $s[1] ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;transition:transform .3s;"
                            onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
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
        <div class="task-card">
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
                                    <a href="/pages/volunteer/assignments.php" class="btn-xs btn-purple">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
                        <div style="display:flex;align-items:center;gap:14px;padding:10px 0;border-bottom:1px solid #F3F4F6;">
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
                        </div>
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
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
    });
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>