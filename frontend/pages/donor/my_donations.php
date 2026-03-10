<?php
/**
 * pages/donor/my_donations.php — Donor's donation history
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'DONOR') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'my_donations';
$pageTitle = 'My Donations | Food Link';
$userId = $sessionUser['user_id'];

$data = apiCall("/donor/get_my_donations.php?donor_id={$userId}", [], 'GET');
$donations = $data['data']['donations'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">My Donations</h1>
                <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">All your food donation records</p>
            </div>
            <a href="/pages/donor/donate.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Donation</a>
        </div>

        <div
            style="margin-bottom:32px;border-radius:12px;overflow:hidden;position:relative;height:240px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);">
            <img src="https://images.unsplash.com/photo-1593113616828-6f22bca04804?q=80&w=1200&auto=format&fit=crop"
                alt="Real volunteers packing food donations"
                style="width:100%;height:100%;object-fit:cover;display:block;">
            <div
                style="position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(to right, rgba(17,24,39,0.7) 0%, rgba(17,24,39,0.2) 100%);display:flex;align-items:center;padding:40px;">
                <div style="max-width:650px;">
                    <span
                        style="display:inline-block;background:#2E7D32;color:#fff;font-size:0.75rem;font-weight:700;padding:4px 12px;border-radius:20px;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px;">Real
                        Impact</span>
                    <h2 style="color:#fff;font-size:1.75rem;font-weight:800;margin-bottom:12px;line-height:1.2;">See
                        Your Donations in Action</h2>
                    <p style="color:#E5E7EB;font-size:1rem;line-height:1.6;font-weight:400;">This is exactly what your
                        contributions look like on the ground. Real volunteers and NGOs continuously packing and
                        distributing surplus food from donors directly to the communities that need it most.</p>
                </div>
            </div>
        </div>

        <div class="table-card">
            <?php if (empty($donations)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-box-open" style="font-size:2.5rem;margin-bottom:16px;display:block;"></i>
                    No donations yet. <a href="/pages/donor/donate.php" style="color:#2E7D32;font-weight:500;">Create your
                        first one →</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Quantity</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $d):
                            $s = strtolower($d['STATUS']); ?>
                            <td style="color:#9CA3AF;">#<?= $d['ALERT_ID'] ?></td>
                            <td><strong><?= htmlspecialchars($d['FOOD_TYPE']) ?></strong></td>
                            <td><?= htmlspecialchars($d['QUANTITY']) ?></td>
                            <td><?= htmlspecialchars($d['CITY'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= $s ?>"><?= htmlspecialchars($d['STATUS']) ?></span></td>
                            <td style="color:#6B7280;font-size:0.8rem;"><?= htmlspecialchars($d['CREATED_AT']) ?></td>
                            <td style="display:flex;gap:8px;">
                                <button class="btn btn-outline" style="padding:4px 8px;font-size:0.75rem;"
                                    onclick="openDonationDetails(this)" data-id="<?= htmlspecialchars($d['ALERT_ID']) ?>"
                                    data-food="<?= htmlspecialchars($d['FOOD_TYPE']) ?>"
                                    data-qty="<?= htmlspecialchars($d['QUANTITY']) ?>"
                                    data-status="<?= htmlspecialchars($d['STATUS']) ?>"
                                    data-date="<?= htmlspecialchars($d['CREATED_AT']) ?>"
                                    data-address="<?= htmlspecialchars($d['CITY'] ?? 'Not Specified') ?>">
                                    View Details
                                </button>
                                <?php if ($s === 'pending'): ?>
                                    <button class="btn btn-outline"
                                        style="padding:4px 8px;font-size:0.75rem;color:#D32F2F;border-color:#D32F2F;">Cancel
                                        Donation</button>
                                <?php endif; ?>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Donation Details Modal -->
<div id="detailsModal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(17,24,39,0.7);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div
        style="background:#fff;border-radius:12px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">

        <!-- Modal Header -->
        <div
            style="padding:24px;border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 style="font-size:1.5rem;font-weight:700;color:#111827;">Donation #<span id="mod-id"></span></h2>
                <span id="mod-status" class="badge" style="margin-top:8px;display:inline-block;"></span>
            </div>
            <button onclick="closeDonationDetails()"
                style="background:none;border:none;font-size:1.5rem;color:#6B7280;cursor:pointer;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding:24px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;">
                <div>
                    <h4
                        style="font-size:0.85rem;text-transform:uppercase;color:#6B7280;font-weight:700;margin-bottom:4px;">
                        Food Type</h4>
                    <p id="mod-food" style="font-size:1.1rem;font-weight:600;color:#111827;"></p>
                </div>
                <div>
                    <h4
                        style="font-size:0.85rem;text-transform:uppercase;color:#6B7280;font-weight:700;margin-bottom:4px;">
                        Quantity / Volume</h4>
                    <p id="mod-qty" style="font-size:1.1rem;font-weight:600;color:#111827;"></p>
                </div>
                <div>
                    <h4
                        style="font-size:0.85rem;text-transform:uppercase;color:#6B7280;font-weight:700;margin-bottom:4px;">
                        Registered Date</h4>
                    <p id="mod-date" style="font-size:1rem;color:#111827;"></p>
                </div>
                <div>
                    <h4
                        style="font-size:0.85rem;text-transform:uppercase;color:#6B7280;font-weight:700;margin-bottom:4px;">
                        Pickup Location</h4>
                    <p id="mod-address" style="font-size:1rem;color:#111827;"></p>
                </div>
            </div>

            <div
                style="background:#F9FAFB;border-radius:8px;padding:24px;border:1px solid #E5E7EB;text-align:center;margin-top:16px;">
                <i class="fa-solid fa-hand-holding-heart"
                    style="font-size:2rem;color:#2E7D32;margin-bottom:12px;display:block;"></i>
                <h4 style="font-size:1.1rem;font-weight:700;color:#111827;margin-bottom:8px;">Thank You for Your Impact
                </h4>
                <p style="font-size:0.9rem;color:#6B7280;line-height:1.5;margin:0;">
                    Every box of surplus food managed through this portal directly combats community hunger and
                    minimizes
                    landfill waste.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
    function openDonationDetails(btn) {
        const dId = btn.getAttribute('data-id');
        const dFood = btn.getAttribute('data-food');
        const dQty = btn.getAttribute('data-qty');
        const dStatus = btn.getAttribute('data-status');
        const dDate = btn.getAttribute('data-date');
        const dAddr = btn.getAttribute('data-address');

        document.getElementById('mod-id').innerText = dId;
        document.getElementById('mod-food').innerText = dFood;
        document.getElementById('mod-qty').innerText = dQty;
        document.getElementById('mod-date').innerText = dDate;
        document.getElementById('mod-address').innerText = dAddr;

        const badge = document.getElementById('mod-status');
        badge.innerText = dStatus.toUpperCase();
        badge.className = 'badge badge-' + dStatus.toLowerCase();

        const modal = document.getElementById('detailsModal');
        modal.style.display = 'flex';
    }

    function closeDonationDetails() {
        document.getElementById('detailsModal').style.display = 'none';
    }
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>