<?php
/**
 * pages/donor/history.php — Donor's donation history (completed/expired)
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'DONOR') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'history';
$pageTitle = 'Donation History | Food Rescue';
$userId = $sessionUser['user_id'];

// Get all donations and filter locally for simplicity just to show completed/expired.
$data = apiCall("/donor/get_my_donations.php?donor_id={$userId}", [], 'GET');
$allDonations = $data['data']['donations'] ?? [];

$history = array_filter($allDonations, function ($d) {
    return in_array(strtolower($d['STATUS']), ['completed', 'delivered', 'expired']);
});

// Analytics Calculations
$totalHistory = count($history);
$completedCount = 0;
$expiredCount = 0;
$totalQuantityEstimate = 0;
$monthsMap = [];

foreach ($history as $d) {
    $status = strtolower($d['STATUS']);
    if (in_array($status, ['completed', 'delivered'])) {
        $completedCount++;
        // Simple extraction for quantity estimate (e.g. "10kg" -> 10)
        preg_match('/(\d+)/', $d['QUANTITY'], $matches);
        if (!empty($matches))
            $totalQuantityEstimate += (int) $matches[0];
    } elseif ($status === 'expired') {
        $expiredCount++;
    }

    // Capture months for average
    $dateParts = explode(' ', $d['CREATED_AT']);
    if (count($dateParts) >= 2) {
        $monthKey = $dateParts[1] . ' ' . $dateParts[2]; // e.g. "Mar 2024"
        $monthsMap[$monthKey] = true;
    }
}

$monthsActive = max(count($monthsMap), 1);
$avgMonthly = $completedCount / $monthsActive;
$successRate = $totalHistory > 0 ? ($completedCount / $totalHistory) * 100 : 0;
$impactScore = $completedCount * 12; // Example: approx 12 people helped per donation

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Donation History</h1>
                <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">Past contributions and expired entries.</p>
            </div>
        </div>

        <!-- History Analytics -->
        <div class="stats-grid" style="margin-bottom:32px;">
            <div class="stat-card">
                <span class="label">Lifetime Contributions</span>
                <span class="value" style="color:#2E7D32;">
                    <span class="count-up" data-target="<?= $completedCount ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Successful handovers</span>
            </div>
            <div class="stat-card">
                <span class="label">Average Monthly Impact</span>
                <span class="value" style="color:#0d9488;">
                    <span class="count-up" data-target="<?= round($avgMonthly, 1) ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Donations per month</span>
            </div>
            <div class="stat-card">
                <span class="label">Efficiency Score</span>
                <span class="value" style="color:#f59e0b;">
                    <span class="count-up" data-target="<?= round($successRate) ?>">0</span>%
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Completed vs Expired</span>
            </div>
            <div class="stat-card">
                <span class="label">Estimated Impact Reach</span>
                <span class="value" style="color:#111827;">
                    <span class="count-up" data-target="<?= $impactScore ?>">0</span>
                </span>
                <span style="font-size:0.8rem;color:#6B7280;margin-top:6px;">Lives touched</span>
            </div>
        </div>

        <div class="table-card">
            <?php if (empty($history)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size:2.5rem;margin-bottom:16px;display:block;"></i>
                    No completed or expired donations in history.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>NGO Assigned</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $d):
                            $s = strtolower($d['STATUS']); ?>
                            <tr>
                                <td style="color:#9CA3AF;">#
                                    <?= $d['ALERT_ID'] ?>
                                </td>
                                <td><strong>
                                        <?= htmlspecialchars($d['FOOD_TYPE']) ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($d['QUANTITY']) ?>
                                </td>
                                <td style="color:#6B7280;">
                                    <?= htmlspecialchars($d['ASSIGNED_NGO'] ?? '—') ?>
                                </td>
                                <td style="color:#6B7280;font-size:0.8rem;">
                                    <?= htmlspecialchars($d['CREATED_AT']) ?>
                                </td>
                                <td><span class="badge badge-<?= $s ?>">
                                        <?= htmlspecialchars($d['STATUS']) ?>
                                    </span></td>
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
        // Count-up Animation logic
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
                    const duration = 1500; // ms
                    const startTime = performance.now();

                    const updateCount = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const current = progress * target;

                        counter.innerText = (target % 1 === 0)
                            ? Math.ceil(current).toLocaleString()
                            : current.toFixed(1);

                        if (progress < 1) {
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };
                    requestAnimationFrame(updateCount);
                    obs.unobserve(counter);
                }
            });
        }, { threshold: 0.1 });
        counters.forEach(counter => observer.observe(counter));
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>