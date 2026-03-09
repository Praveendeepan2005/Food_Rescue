<?php
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'accepted';
$pageTitle = 'Accepted Donations | Food Rescue';
$ngoId = $sessionUser['user_id'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $alertId = (int) $_POST['alert_id'];
        $action = $_POST['action'];

        if ($action === 'ASSIGN') {
            $volId = (int) $_POST['volunteer_id'];
            $res = apiCall('/ngo/assign_volunteer.php', [
                'alert_id' => $alertId,
                'volunteer_id' => $volId,
                'ngo_id' => $ngoId
            ]);
            $_SESSION['flash'] = $res['success']
                ? ['type' => 'success', 'msg' => 'Volunteer assigned successfully!']
                : ['type' => 'error', 'msg' => $res['message'] ?? 'Failed to assign volunteer.'];
        } else {
            // PICKUP or COMPLETE
            $status = ($action === 'PICKUP') ? 'FOOD_PICKED' : 'COMPLETED';
            $res = apiCall('/ngo/update_donation_status.php', [
                'alert_id' => $alertId,
                'status' => $status
            ]);
            $_SESSION['flash'] = $res['success']
                ? ['type' => 'success', 'msg' => "Donation status updated to $status."]
                : ['type' => 'error', 'msg' => $res['message'] ?? 'Failed to update status.'];
        }
    }
    header('Location: /pages/ngo/accepted.php');
    exit();
}

// Fetch Donations
$data = apiCall("/ngo/get_accepted_donations.php?ngo_id={$ngoId}", [], 'GET');
$items = $data['data']['donations'] ?? [];

// Fetch Volunteers for the assignment modal
$volData = apiCall("/ngo/get_volunteers.php?ngo_id={$ngoId}", [], 'GET');
$volunteers = $volData['data']['volunteers'] ?? [];

// Fetch Dashboard Analytics for Monthly Chart
$dashData = apiCall("/ngo/get_ngo_dashboard.php?ngo_id={$ngoId}", [], 'GET');
$charts = $dashData['data']['charts'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Accepted Donations</h1>
                <p style="color:#6B7280;font-size:0.875rem;">Manage logistics and delivery workflows for rescuded food.
                </p>
            </div>
            <a href="/pages/ngo/available.php" class="btn btn-outline">
                <i class="fa-solid fa-plus"></i> Accept More
            </a>
        </div>

        <div class="glass-card" style="margin-bottom: 32px; padding: 24px;">
            <h3
                style="font-size:1.1rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;color:#111827;">
                <i class="fa-solid fa-chart-line" style="color:#0d9488;"></i> Monthly Fulfillment Analytics
            </h3>
            <canvas id="monthlyChart" height="80"></canvas>
        </div>

        <div class="table-card">
            <?php if (empty($items)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-truck-ramp-box"
                        style="font-size:2.5rem;margin-bottom:16px;display:block;color:#D1D5DB;"></i>
                    No donations in progress. <a href="/pages/ngo/available.php"
                        style="color:#2E7D32;font-weight:500;">Check available →</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Type</th>
                            <th>Qty</th>
                            <th>Donor Name</th>
                            <th>Location</th>
                            <th>Volunteer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $r):
                            $status = strtoupper($r['DELIVERY_STATUS'] ?? $r['STATUS'] ?? 'ACCEPTED');
                            ?>
                            <tr>
                                <td style="color:#9CA3AF;">#<?= $r['ALERT_ID'] ?></td>
                                <td><strong><?= htmlspecialchars($r['FOOD_TYPE'] ?? '—') ?></strong></td>
                                <td><?= htmlspecialchars($r['QUANTITY'] ?? '—') ?> Servings</td>
                                <td><?= htmlspecialchars($r['DONOR_NAME'] ?? '—') ?></td>
                                <td><i class="fa-solid fa-location-dot" style="color:#9CA3AF;margin-right:4px;"></i>
                                    <?= htmlspecialchars($r['CITY'] ?? '—') ?></td>
                                <td>
                                    <?php if ($r['VOLUNTEER_NAME']): ?>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div
                                                style="width:24px;height:24px;border-radius:50%;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.6rem;">
                                                <?= strtoupper(substr($r['VOLUNTEER_NAME'], 0, 1)) ?>
                                            </div>
                                            <span><?= htmlspecialchars($r['VOLUNTEER_NAME']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#f59e0b;font-weight:600;font-size:0.85rem;">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($status) ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:8px;">
                                        <?php if (!$r['VOLUNTEER_NAME']): ?>
                                            <button
                                                onclick="openAssignModal(<?= $r['ALERT_ID'] ?>, '<?= htmlspecialchars($r['FOOD_TYPE'] ?? 'Food') ?>')"
                                                class="btn btn-primary"
                                                style="padding:4px 8px;font-size:0.75rem;background:#8b5cf6;">
                                                Assign Vol
                                            </button>
                                        <?php else: ?>
                                            <a href="/pages/ngo/track-delivery.php?alert_id=<?= $r['ALERT_ID'] ?>"
                                                class="btn btn-outline"
                                                style="padding:4px 8px;font-size:0.75rem;color:#2E7D32;border-color:#2E7D32;">
                                                <i class="fa-solid fa-map"></i> Track
                                            </a>
                                            <?php if ($status === 'ASSIGNED' || $status === 'VOLUNTEER_ASSIGNED'): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="alert_id" value="<?= $r['ALERT_ID'] ?>">
                                                    <input type="hidden" name="action" value="PICKUP">
                                                    <button type="submit" class="btn btn-primary"
                                                        style="padding:4px 8px;font-size:0.75rem;background:#f59e0b;">
                                                        Mark Picked Up
                                                    </button>
                                                </form>
                                            <?php elseif ($status === 'FOOD_PICKED' || $status === 'PICKED_UP'): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="alert_id" value="<?= $r['ALERT_ID'] ?>">
                                                    <input type="hidden" name="action" value="COMPLETE">
                                                    <button type="submit" class="btn btn-primary"
                                                        style="padding:4px 8px;font-size:0.75rem;background:#2E7D32;">
                                                        Complete mission
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:#9CA3AF;font-size:0.75rem;display:flex;align-items:center;">Done <i
                                                        class="fa-solid fa-circle-check" style="margin-left:4px;"></i></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Assign Volunteer Modal -->
<div id="assignModal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(17,24,39,0.7);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div
        style="background:#fff;border-radius:12px;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;">
        <div
            style="padding:20px 24px;border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:center;background:#8b5cf6;color:#fff;">
            <h3 style="font-size:1.1rem;font-weight:700;margin:0;">Assign Volunteer</h3>
            <button onclick="closeAssignModal()"
                style="background:none;border:none;font-size:1.5rem;color:#fff;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" style="padding:24px;">
            <input type="hidden" name="action" value="ASSIGN">
            <input type="hidden" id="modal-alert-id" name="alert_id" value="">

            <p style="color:#6B7280;font-size:0.875rem;margin-bottom:20px;">
                Selecting a volunteer for: <strong id="modal-food-name" style="color:#111827;"></strong>
            </p>

            <div class="form-group">
                <label style="display:block;margin-bottom:8px;font-weight:600;font-size:0.85rem;">Select Active
                    Volunteer</label>
                <select name="volunteer_id" required
                    style="width:100%;padding:10px;border:1px solid #E5E7EB;border-radius:6px;outline:none;">
                    <?php if (empty($volunteers)): ?>
                        <option value="" disabled>No volunteers registered</option>
                    <?php else: ?>
                        <?php foreach ($volunteers as $v): ?>
                            <option value="<?= $v['USER_ID'] ?>">
                                <?= htmlspecialchars($v['NAME']) ?> (<?= htmlspecialchars($v['CITY'] ?? 'Anywhere') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="margin-top:28px;display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;background:#8b5cf6;">Confirm
                    Assignment</button>
                <button type="button" onclick="closeAssignModal()" class="btn btn-outline"
                    style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal(id, food) {
        document.getElementById('modal-alert-id').value = id;
        document.getElementById('modal-food-name').innerText = food;
        document.getElementById('assignModal').style.display = 'flex';
    }
    function closeAssignModal() {
        document.getElementById('assignModal').style.display = 'none';
    }

    // Initialize Monthly Analytics Line Chart
    document.addEventListener('DOMContentLoaded', () => {
        const mData = <?= json_encode($charts['monthly'] ?? ['labels' => [], 'donations' => [], 'deliveries' => []]) ?>;
        const ctx = document.getElementById('monthlyChart');
        if (ctx && mData.labels.length) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: mData.labels,
                    datasets: [
                        {
                            label: 'Total Donations Handled',
                            data: mData.donations,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Successful Deliveries',
                            data: mData.deliveries,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>