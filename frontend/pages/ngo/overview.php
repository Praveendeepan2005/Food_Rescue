<?php
/**
 * pages/ngo/overview.php — NGO Dashboard Overview
 */
require_once __DIR__ . '/../../includes/api_call.php';

$userId = $sessionUser['user_id'];
$activePage = 'overview';
$pageTitle = 'NGO Dashboard | Food Link';

$data = apiCall("/ngo/get_ngo_dashboard.php?ngo_id={$userId}", [], 'GET');
$stats = $data['data']['stats'] ?? [];
$charts = $data['data']['charts'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">NGO Dashboard</h1>
                <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">Welcome back,
                    <?= htmlspecialchars($sessionUser['name']) ?>!
                </p>
            </div>
            <a href="/pages/ngo/available.php" class="btn btn-primary">
                <i class="fa-solid fa-box-open"></i> View Available Food
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid reveal">
            <div class="stat-card">
                <span class="label">Available Donations</span>
                <span class="value" style="color:#f59e0b;">
                    <span class="count-up" data-target="<?= $stats['available_donations_count'] ?? 0 ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Global Available</span>
            </div>
            <div class="stat-card">
                <span class="label">Accepted Donations</span>
                <span class="value" style="color:#0d9488;">
                    <span class="count-up"
                        data-target="<?= ($stats['pending_pickups'] ?? 0) + ($stats['active_deliveries'] ?? 0) ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">In Progress mission</span>
            </div>
            <div class="stat-card">
                <span class="label">Completed Donations</span>
                <span class="value" style="color:#2E7D32;">
                    <span class="count-up" data-target="<?= $stats['completed_deliveries'] ?? 0 ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Successfully delivered</span>
            </div>
            <div class="stat-card">
                <span class="label">Total Volunteers</span>
                <span class="value" style="color:#8b5cf6;">
                    <span class="count-up" data-target="<?= $stats['available_volunteers'] ?? 0 ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Active in Platform</span>
            </div>
        </div>

        <!-- Dashboard Charts/Stats -->
        <div class="reveal reveal-delay-1" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">
            <div class="glass-card">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-chart-line" style="color:#2E7D32;"></i> Weekly Activity
                </h3>
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
            <div class="glass-card" style="display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h3 style="font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> Action & Impact
                        </h3>
                        <div
                            style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3); padding:4px 12px; border-radius:20px; color:#d97706; font-weight:800; font-size:0.85rem; display:flex; align-items:center; gap:6px; box-shadow:0 4px 6px -1px rgba(245,158,11,0.1);">
                            <i class="fa-solid fa-trophy"></i>
                            <span class="count-up" data-target="<?= $stats['points'] ?? 0 ?>">0</span> pts
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                        <div
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
                            <div style="font-size:1.75rem; font-weight:800; color:#0f172a; margin-bottom:4px;"
                                class="count-up" data-target="<?= $stats['assignments'] ?? 0 ?>">0</div>
                            <div
                                style="font-size:0.75rem; color:#64748b; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                                Vols Assigned</div>
                        </div>
                        <div
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
                            <div style="font-size:1.75rem; font-weight:800; color:#0f172a; margin-bottom:4px;"
                                class="count-up" data-target="<?= $stats['completed_deliveries'] ?? 0 ?>">0</div>
                            <div
                                style="font-size:0.75rem; color:#64748b; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">
                                Missions Done</div>
                        </div>
                    </div>

                    <!-- Priority Breakdown -->
                    <div style="margin-bottom:16px;">
                        <div
                            style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px; margin-bottom:10px;">
                            <i class="fa-solid fa-flag" style="color:#f59e0b; margin-right:4px;"></i> Priority Breakdown
                        </div>
                        <?php
                        $pri = $stats['priority'] ?? ['High' => 0, 'Medium' => 0, 'Normal' => 0];
                        $priTotal = max(1, array_sum($pri));
                        $priDefs = [
                            ['label' => 'High', 'key' => 'High', 'color' => '#ef4444'],
                            ['label' => 'Medium', 'key' => 'Medium', 'color' => '#f59e0b'],
                            ['label' => 'Normal', 'key' => 'Normal', 'color' => '#2E7D32'],
                        ];
                        foreach ($priDefs as $pd):
                            $pct = round($pri[$pd['key']] / $priTotal * 100);
                            ?>
                            <div style="margin-bottom:8px;">
                                <div
                                    style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                    <span style="color:#374151; font-weight:600;"><?= $pd['label'] ?></span>
                                    <span style="color:#9CA3AF;"><?= $pri[$pd['key']] ?> tasks</span>
                                </div>
                                <div style="background:#f1f5f9; border-radius:99px; height:6px; overflow:hidden;">
                                    <div style="height:6px; border-radius:99px; background:<?= $pd['color'] ?>; width:0%;
                                    transition: width 1.2s cubic-bezier(0.4,0,0.2,1);" data-width="<?= $pct ?>%"
                                        class="anim-bar">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Active vs Completed Ratio -->
                    <?php
                    $activeN = ($stats['pending_pickups'] ?? 0) + ($stats['active_deliveries'] ?? 0);
                    $completedN = $stats['completed_deliveries'] ?? 0;
                    $grandTotal = max(1, $activeN + $completedN);
                    $activePct = round($activeN / $grandTotal * 100);
                    ?>
                    <div style="margin-bottom:4px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:6px;">
                            <span style="color:#374151; font-weight:700;"><i class="fa-solid fa-chart-pie"
                                    style="color:#2E7D32; margin-right:4px;"></i>Active vs Completed</span>
                            <span style="color:#9CA3AF;"><?= $activeN ?> / <?= $completedN ?></span>
                        </div>
                        <div
                            style="background:#f1f5f9; border-radius:99px; height:10px; overflow:hidden; display:flex;">
                            <div style="height:10px; background:#f59e0b; width:0%; transition: width 1.4s ease;"
                                data-width="<?= $activePct ?>%" class="anim-bar" title="Active: <?= $activeN ?>"></div>
                            <div style="height:10px; background:#2E7D32; flex:1;" title="Completed: <?= $completedN ?>">
                            </div>
                        </div>
                        <div style="display:flex; gap:16px; margin-top:6px;">
                            <span style="font-size:0.68rem; color:#6B7280; display:flex; align-items:center; gap:4px;">
                                <span
                                    style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                                In Progress
                            </span>
                            <span style="font-size:0.68rem; color:#6B7280; display:flex; align-items:center; gap:4px;">
                                <span
                                    style="width:8px;height:8px;border-radius:50%;background:#2E7D32;display:inline-block;"></span>
                                Completed
                            </span>
                        </div>
                    </div>
                </div>


                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <a href="/pages/ngo/available.php" class="btn btn-primary"
                        style="justify-content:center;padding:12px;font-size:0.85rem;text-align:center;">
                        <i class="fa-solid fa-box-open"></i> View Donations
                    </a>
                    <a href="/pages/ngo/volunteers.php" class="btn btn-outline"
                        style="justify-content:center;padding:12px;font-size:0.85rem;text-align:center;">
                        <i class="fa-solid fa-users"></i> Manage Volunteers
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Donations Table -->
        <div class="table-card" style="margin-top:32px;">
            <div class="table-header">
                <div>
                    <h3 style="font-size:1.1rem;font-weight:700;color:#111827;">
                        <?= empty($data['recent_donations']) || !($data['recent_donations'][0]['IS_GLOBAL'] ?? false) ? 'Recent Internal Donations' : 'Available Opportunities Nearby' ?>
                    </h3>
                    <p style="color:#6B7280;font-size:0.85rem;margin-top:4px;">
                        <?= empty($data['recent_donations']) || !($data['recent_donations'][0]['IS_GLOBAL'] ?? false) ? 'Latest actions within your NGO' : 'Recent community donations waiting for an NGO' ?>
                    </p>
                </div>
                <a href="/pages/ngo/accepted.php" class="btn btn-outline" style="font-size:0.8rem;">View All
                    Accepted</a>
            </div>

            <?php if (empty($data['recent_donations'])): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-box-open" style="font-size:2.5rem;margin-bottom:16px;display:block;"></i>
                    No recent activities found for your NGO.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>Donor Location</th>
                            <th>Expiry Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recent_donations'] as $row): ?>
                            <tr>
                                <td style="color:#9CA3AF;">#<?= htmlspecialchars($row['ALERT_ID']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['FOOD_TYPE']) ?></strong>
                                    <?php if ($row['IS_GLOBAL'] ?? false): ?>
                                        <span
                                            style="font-size:0.7rem; background:#fef3c7; color:#d97706; padding:2px 6px; border-radius:4px; margin-left:8px; font-weight:700;">NEW</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['QUANTITY']) ?></td>
                                <td><i class="fa-solid fa-location-dot" style="color:#9CA3AF;margin-right:4px;"></i>
                                    <?= htmlspecialchars($row['CITY']) ?></td>
                                <td style="color:#6B7280;font-size:0.8rem;"><?= htmlspecialchars($row['EXP_TIME']) ?></td>
                                <td><span
                                        class="badge badge-<?= strtolower($row['STATUS']) ?>"><?= htmlspecialchars($row['STATUS']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Animate progress bars
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.anim-bar').forEach(bar => {
            const target = bar.getAttribute('data-width');
            setTimeout(() => { bar.style.width = target; }, 300);
        });
    });

    // Weekly chart — tiny inline script just for Chart.js rendering
    const wData = <?= json_encode($charts['weekly'] ?? ['labels' => [], 'assigned' => [], 'delivered' => []]) ?>;
    const ctx = document.getElementById('weeklyChart');
    if (ctx && wData.labels.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: wData.labels,
                datasets: [
                    { label: 'Assigned', data: wData.assigned, backgroundColor: 'rgba(46,125,50,0.7)', borderRadius: 4 },
                    { label: 'Delivered', data: wData.delivered, backgroundColor: 'rgba(13,148,136,0.7)', borderRadius: 4 }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }

    // --- Count-up Animation logic ---
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.count-up');
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    if (target === 0) {
                        counter.innerText = '0';
                        obs.unobserve(counter);
                        return;
                    }

                    let count = 0;
                    const updateCount = () => {
                        const increment = target / 30; // 30 frames
                        if (count < target) {
                            count += increment;
                            if (count > target) count = target;
                            counter.innerText = Math.ceil(count).toLocaleString();
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };
                    updateCount();
                    obs.unobserve(counter);
                }
            });
        }, { threshold: 0.1 });
        counters.forEach(counter => observer.observe(counter));
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>