<?php
/**
 * pages/donor/overview.php — Donor Dashboard Overview
 * Included by dashboard.php — uses $sessionUser from auth_guard.php
 */
require_once __DIR__ . '/../../includes/api_call.php';

$userId = $sessionUser['user_id'];
$activePage = 'overview';
$pageTitle = 'Dashboard | Food Rescue';

// Fetch data from backend
$data = apiCall("/donor/get_donor_dashboard.php?donor_id={$userId}", [], 'GET');
$stats = $data['data'] ?? ['total_donations' => 0, 'active_donations' => 0, 'completed_donations' => 0, 'expired_donations' => 0, 'meals_donated' => 0, 'recent_donations' => []];
$recent = $stats['recent_donations'] ?? [];
$chartLabels = json_encode($stats['chart_labels'] ?? []);
$chartValues = json_encode($stats['chart_data'] ?? []);

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <!-- Page Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Donor Dashboard</h1>
                <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">
                    Welcome back, <?= htmlspecialchars($sessionUser['name']) ?>! Here's your impact summary.
                </p>
            </div>
            <a href="/pages/donor/donate.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Create Donation
            </a>
        </div>

        <!-- Impact Section -->
        <div
            style="background:#DCFCE7;border:1px solid #4CAF50;color:#1B5E20;padding:16px 20px;border-radius:6px;margin:24px 0;">
            <i class="fa-solid fa-hand-holding-heart" style="margin-right:8px;"></i> You have helped provide <strong
                style="font-size:1.1rem;"><span class="count-up" data-target="<?= $stats['meals_donated'] ?>">0</span> meals</strong> to people in
            need.
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="label">Total Donations Made</span>
                <span class="value">
                    <span class="count-up" data-target="<?= $stats['total_donations'] ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">All time</span>
            </div>
            <div class="stat-card">
                <span class="label">Active Donations</span>
                <span class="value" style="color:#f59e0b;">
                    <span class="count-up" data-target="<?= $stats['active_donations'] ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">In progress</span>
            </div>
            <div class="stat-card">
                <span class="label">Completed Donations</span>
                <span class="value" style="color:#2E7D32;">
                    <span class="count-up" data-target="<?= $stats['completed_donations'] ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Successfully delivered</span>
            </div>
            <div class="stat-card">
                <span class="label">Meals Donated</span>
                <span class="value" style="color:#0d9488;">
                    <span class="count-up" data-target="<?= $stats['meals_donated'] ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Estimated impact</span>
            </div>
        </div>

        <!-- Line Graph Analytics -->
        <div style="background:#fff; border:1px solid #E5E7EB; border-radius:8px; padding:24px; margin-top:24px;">
            <h3 style="font-size:1.15rem;font-weight:700;color:#111827;margin-bottom:16px;">Donation Activity (Year to
                Date)</h3>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="impactGraph"></canvas>
            </div>
        </div>

        <!-- Recent Donations Table -->
        <div class="table-card" style="margin-top:8px;">
            <div class="table-header">
                <div>
                    <h3>Recent Donations</h3>
                    <p>Your last 5 food donation entries</p>
                </div>
                <a href="/pages/donor/my_donations.php" class="btn btn-outline" style="font-size:0.8rem;">View All</a>
            </div>

            <?php if (empty($recent)): ?>
                <div style="padding:48px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-box-open" style="font-size:2rem;margin-bottom:12px;display:block;"></i>
                    No donations yet. <a href="/pages/donor/donate.php" style="color:#2E7D32;font-weight:500;">Make your
                        first donation →</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $row): ?>
                            <?php $s = strtolower($row['STATUS']); ?>
                            <tr>
                                <td style="color:#9CA3AF;">#<?= htmlspecialchars($row['ALERT_ID']) ?></td>
                                <td><strong>
                                        <?= htmlspecialchars($row['FOOD_TYPE']) ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($row['QUANTITY']) ?>
                                </td>
                                <td><span class="badge badge-<?= $s ?>">
                                        <?= htmlspecialchars($row['STATUS']) ?>
                                    </span></td>
                                <td style="color:#6B7280;">
                                    <?= htmlspecialchars($row['CREATED_AT']) ?>
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
    document.addEventListener('DOMContentLoaded', () => {
        // --- Count-up Animation logic ---
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

        // --- Line Graph Logic ---
        const ctx = document.getElementById('impactGraph');
        if (!ctx) return;
        const context = ctx.getContext('2d');

        const gradient = context.createLinearGradient(0, 0, 0, 250);
        gradient.addColorStop(0, 'rgba(46, 125, 50, 0.2)');
        gradient.addColorStop(1, 'rgba(46, 125, 50, 0)');

        new Chart(context, {
            type: 'line',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    label: 'Food Donations',
                    data: <?= $chartValues ?>,
                    borderColor: '#2E7D32',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: '#2E7D32',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: "'Inter', sans-serif", size: 12 },
                        bodyFont: { family: "'Inter', sans-serif", size: 13, weight: 'bold' },
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#9CA3AF',
                            font: { size: 10, family: "'Inter', sans-serif" }
                        },
                        grid: { color: '#F3F4F6', drawBorder: false }
                    },
                    x: {
                        ticks: {
                            color: '#6B7280',
                            font: { size: 10, family: "'Inter', sans-serif" }
                        },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>