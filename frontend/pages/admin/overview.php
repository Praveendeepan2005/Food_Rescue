<?php
/**
 * pages/admin/overview.php — Admin Dashboard Overview
 */
require_once __DIR__ . '/../../includes/api_call.php';

$activePage = 'overview';
$pageTitle = 'Admin Dashboard | Food Link';

$data = apiCall('/admin/get_stats.php', [], 'GET');
$cards = $data['data']['top_cards'] ?? [];
$charts = $data['data']['charts'] ?? [];
$recent = $data['data']['recent_activity'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Admin Overview</h1>
            <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">Platform-wide statistics and activity feed.</p>
        </div>

        <!-- Top Cards -->
        <div class="stats-grid reveal">
            <div class="stat-card">
                <span class="label">Total Orphanages</span>
                <span class="value" style="color:#1d4ed8;">
                    <span class="count-up" data-target="<?= $cards['total_orphanages'] ?? 0 ?>">0</span>
                </span>
            </div>
            <div class="stat-card">
                <span class="label">Active Missions</span>
                <span class="value" style="color:#f59e0b;">
                    <span class="count-up" data-target="<?= $cards['active_alerts'] ?? 0 ?>">0</span>
                </span>
            </div>
            <div class="stat-card">
                <span class="label">Completed Rescues</span>
                <span class="value" style="color:#2E7D32;">
                    <span class="count-up" data-target="<?= $cards['completed_rescues'] ?? 0 ?>">0</span>
                </span>
            </div>
            <div class="stat-card">
                <span class="label">Food Distributed</span>
                <span class="value" style="color:#0d9488;">
                    <span class="count-up" data-target="<?= $cards['food_distributed'] ?? 0 ?>">0</span>
                    <small style="font-size:0.8rem; font-weight:600;">Portions</small>
                </span>
            </div>
        </div>

        <!-- Sub-cards: Donors / NGOs / Volunteers -->
        <div class="reveal reveal-delay-1"
            style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="glass-card" style="text-align:center;padding:16px;">
                <i class="fa-solid fa-hand-holding-heart"
                    style="font-size:1.5rem;color:#f59e0b;margin-bottom:8px;display:block;"></i>
                <div style="font-size:1.5rem;font-weight:700;color:#111827;">
                    <span class="count-up" data-target="<?= $cards['total_donors'] ?? 0 ?>">0</span>
                </div>
                <div style="font-size:0.8rem;color:#6B7280;">Donors</div>
            </div>
            <div class="glass-card" style="text-align:center;padding:16px;">
                <i class="fa-solid fa-building"
                    style="font-size:1.5rem;color:#0d9488;margin-bottom:8px;display:block;"></i>
                <div style="font-size:1.5rem;font-weight:700;color:#111827;">
                    <span class="count-up" data-target="<?= $cards['total_ngos'] ?? 0 ?>">0</span>
                </div>
                <div style="font-size:0.8rem;color:#6B7280;">NGOs</div>
            </div>
            <div class="glass-card" style="text-align:center;padding:16px;">
                <i class="fa-solid fa-person-running"
                    style="font-size:1.5rem;color:#8b5cf6;margin-bottom:8px;display:block;"></i>
                <div style="font-size:1.5rem;font-weight:700;color:#111827;">
                    <span class="count-up" data-target="<?= $cards['total_volunteers'] ?? 0 ?>">0</span>
                </div>
                <div style="font-size:0.8rem;color:#6B7280;">Volunteers</div>
            </div>
        </div>

        <!-- Recent Users & Recent Alerts -->
        <div class="reveal reveal-delay-2" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <!-- Recent Users -->
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h3>New Users</h3>
                        <p>Most recently registered</p>
                    </div><a href="/pages/admin/users.php" class="btn btn-outline" style="font-size:0.8rem;">Manage</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($recent['users'] ?? []) as $u): ?>
                            <tr>
                                <td><strong>
                                        <?= htmlspecialchars($u['NAME']) ?>
                                    </strong></td>
                                <td><span class="badge badge-available">
                                        <?= htmlspecialchars($u['ROLE']) ?>
                                    </span></td>
                                <td style="color:#6B7280;font-size:0.8rem;">
                                    <?= htmlspecialchars($u['JOINED']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Alerts -->
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h3>Recent Alerts</h3>
                        <p>Latest food rescue alerts</p>
                    </div><a href="/pages/admin/alerts.php" class="btn btn-outline" style="font-size:0.8rem;">View
                        All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Food Type</th>
                            <th>Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($recent['alerts'] ?? []) as $a): ?>
                            <tr>
                                <td><strong>
                                        <?= htmlspecialchars($a['FOOD_TYPE']) ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($a['QUANTITY']) ?>
                                </td>
                                <td><span class="badge badge-<?= strtolower($a['STATUS']) ?>">
                                        <?= htmlspecialchars($a['STATUS']) ?>
                                    </span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Analytics Chart -->
        <?php if (!empty($charts['user_growth']['labels'])): ?>
            <div class="glass-card" style="margin-top:24px; padding:24px;">
                <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:6px;color:#111827;"><i
                        class="fa-solid fa-chart-line" style="color:#2E7D32;margin-right:8px;"></i>Platform Analytics</h3>
                <p style="color:#6B7280;font-size:0.875rem;margin-bottom:20px;">Trend of user registrations created over the
                    last 6 months</p>
                <div style="height:320px;position:relative;">
                    <canvas id="monthlyAnalyticsChart"></canvas>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('monthlyAnalyticsChart').getContext('2d');

                    // Add gradient fills
                    const gradientDonor = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientDonor.addColorStop(0, 'rgba(245, 158, 11, 0.4)'); // Amber
                    gradientDonor.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

                    const gradientNgo = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientNgo.addColorStop(0, 'rgba(13, 148, 136, 0.4)'); // Teal
                    gradientNgo.addColorStop(1, 'rgba(13, 148, 136, 0.0)');

                    const gradientVol = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientVol.addColorStop(0, 'rgba(139, 92, 246, 0.4)'); // Purple
                    gradientVol.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

                    const data = <?= json_encode($charts['user_growth']) ?>;

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels || [],
                            datasets: [
                                {
                                    label: 'Donors',
                                    data: data.donors || [],
                                    borderColor: '#f59e0b',
                                    backgroundColor: gradientDonor,
                                    borderWidth: 3,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#f59e0b',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'NGOs',
                                    data: data.ngos || [],
                                    borderColor: '#0d9488',
                                    backgroundColor: gradientNgo,
                                    borderWidth: 3,
                                    borderDash: [5, 5],
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#0d9488',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Volunteers',
                                    data: data.volunteers || [],
                                    borderColor: '#8b5cf6',
                                    backgroundColor: gradientVol,
                                    borderWidth: 3,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#8b5cf6',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    fill: true,
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            animation: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        padding: 20,
                                        font: { weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#111827',
                                    padding: 14,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 14 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#6B7280', stepSize: 1 },
                                    grid: { color: '#F3F4F6', drawBorder: false }
                                },
                                x: {
                                    ticks: { color: '#6B7280' },
                                    grid: { display: false, drawBorder: false }
                                }
                            }
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <!-- Role Distribution Chart -->
        <?php if (!empty($charts['role_distribution']['labels'])): ?>
            <div class="glass-card" style="margin-top:24px;">
                <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;">User Role Distribution</h3>
                <div style="max-width:320px;margin:0 auto;">
                    <canvas id="roleChart" height="200"></canvas>
                </div>
            </div>
            <script>
                (function () {
                    const d = <?= json_encode($charts['role_distribution']) ?>;
                    new Chart(document.getElementById('roleChart'), {
                        type: 'doughnut',
                        data: {
                            labels: d.labels,
                            datasets: [{ data: d.data, backgroundColor: ['#f59e0b', '#0d9488', '#8b5cf6'], borderWidth: 0 }]
                        },
                        options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
                    });
                })();
            </script>
        <?php endif; ?>
    </div>
</div>

<script>
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