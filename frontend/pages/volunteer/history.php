<?php
/**
 * pages/volunteer/history.php — Pickup History
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'history';
$pageTitle = 'Pickup History | Food Rescue';
$userId = $sessionUser['user_id'];

$data = apiCall("/volunteer/get_delivery_history.php?volunteer_id={$userId}", [], 'GET');
$history = $data['data']['history'] ?? [];

$totalQty = array_sum(array_column($history, 'QUANTITY'));
$totalMissions = count($history);
$totalMeals = $totalMissions * 12;

// Group deliveries by month for the line graph
$months = ['Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0];
foreach ($history as $row) {
    if (!empty($row['DELIVERY_DATE'])) {
        $m = date('M', strtotime($row['DELIVERY_DATE']));
        if (isset($months[$m])) {
            $months[$m]++;
        }
    }
}
$monthlyLabels = array_keys($months);
$monthlyData = array_values($months);

include __DIR__ . '/../../includes/header.php';
?>
<style>
    .hist-hero {
        background: linear-gradient(135deg, #1a3a2a 0%, #2E7D32 70%, #1b5e20 100%);
        border-radius: 14px;
        padding: 32px 36px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .hist-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .hist-hero h1 {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0 0 8px;
    }

    .hist-hero p {
        color: rgba(255, 255, 255, .75);
        font-size: .9rem;
        margin: 0;
    }

    .hero-stats-row {
        display: flex;
        gap: 16px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .hero-stat-pill {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 99px;
        padding: 8px 18px;
        color: #fff;
        font-size: .875rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: fadeUp .5s ease both;
    }

    .hero-stat-pill:nth-child(2) {
        animation-delay: .15s;
    }

    .hero-stat-pill:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hist-table-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        overflow: hidden;
    }

    .hist-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #F3F4F6;
    }

    .hist-table-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
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
        opacity: 0;
        animation: rowIn .4s ease both;
    }

    tbody td {
        padding: 12px 16px;
        font-size: .875rem;
        color: #374151;
        vertical-align: middle;
    }

    @keyframes rowIn {
        to {
            opacity: 1;
        }
    }

    .badge-completed,
    .badge-delivered {
        background: #dcfce7;
        color: #16a34a;
        display: inline-block;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: .72rem;
        font-weight: 700;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding:32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <!-- Hero Banner -->
        <div class="hist-hero">
            <h1><i class="fa-solid fa-clock-rotate-left" style="margin-right:10px;"></i>Pickup History</h1>
            <p>Your complete record of successful food rescue missions</p>
            <div class="hero-stats-row">
                <div class="hero-stat-pill"><span>🏆</span><?= $totalMissions ?> Missions Completed</div>
                <div class="hero-stat-pill"><span>🍱</span>~<?= number_format($totalMeals) ?> Meals Rescued</div>
                <div class="hero-stat-pill"><span>⭐</span><?= $totalMissions * 50 ?> pts Earned</div>
            </div>
        </div>

        <!-- Monthly Analytics Chart -->
        <div class="hist-table-card" style="margin-bottom: 28px; padding: 24px;">
            <div style="font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 16px;">
                <i class="fa-solid fa-chart-line" style="color:#2E7D32; margin-right:8px;"></i>Monthly Deliveries
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- History Table -->
        <div class="hist-table-card">
            <div class="hist-table-header">
                <div>
                    <h3><i class="fa-solid fa-list-check" style="color:#2E7D32;margin-right:8px;"></i>All Completed
                        Pickups</h3>
                    <p style="margin:3px 0 0;font-size:.78rem;color:#6B7280;"><?= $totalMissions ?> rescue missions
                        recorded</p>
                </div>
            </div>

            <?php if (empty($history)): ?>
                <div style="padding:80px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-clock-rotate-left"
                        style="font-size:3rem;display:block;margin-bottom:16px;color:#D1D5DB;"></i>
                    <p style="font-size:1rem;font-weight:600;">No completed pickups yet</p>
                    <p style="font-size:.875rem;">Complete your first pickup assignment to see it here.</p>
                    <a href="/pages/volunteer/assignments.php"
                        style="display:inline-block;margin-top:16px;padding:10px 24px;background:#2E7D32;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:.875rem;">
                        View Assignments
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>Pickup Location</th>
                            <th>NGO</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $i => $r): ?>
                            <tr style="animation-delay:<?= $i * 0.04 ?>s;">
                                <td style="color:#9CA3AF;font-size:.8rem;">#<?= htmlspecialchars($r['CLAIM_ID'] ?? '—') ?></td>
                                <td>
                                    <div style="font-weight:600;color:#111827;"><?= htmlspecialchars($r['FOOD_TYPE'] ?? '—') ?>
                                    </div>
                                    <div style="font-size:.72rem;color:#9CA3AF;"><?= htmlspecialchars($r['CATEGORY'] ?? '') ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($r['QUANTITY'] ?? '—') ?></td>
                                <td
                                    style="color:#6B7280;font-size:.8rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($r['PICKUP_ADDRESS'] ?? '—') ?>
                                </td>
                                <td><?= htmlspecialchars($r['NGO_NAME'] ?? '—') ?></td>
                                <td style="color:#6B7280;font-size:.8rem;"><?= htmlspecialchars($r['DELIVERY_DATE'] ?? '—') ?>
                                </td>
                                <td><span class="badge-completed">DELIVERED ✓</span></td>
                                <td style="font-weight:700;color:#d97706;">+50 pts</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('monthlyChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(46, 125, 50, 0.4)');
        gradient.addColorStop(1, 'rgba(46, 125, 50, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthlyLabels) ?>,
                datasets: [{
                    label: 'Completed Pickups',
                    data: <?= json_encode($monthlyData) ?>,
                    borderColor: '#2E7D32',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2E7D32',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 14 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#9CA3AF' },
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>