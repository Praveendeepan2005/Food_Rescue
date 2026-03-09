<?php
/**
 * pages/donor/profile.php — Donor Profile Management with Analytics & Rewards
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'DONOR') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'profile';
$pageTitle = 'Profile | Food Rescue';
$user = $sessionUser;
$userId = $sessionUser['user_id'];

// Mock save logic for profile form
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = "Profile changes saved successfully.";
}

// Fetch stats for analytics
$data = apiCall("/donor/get_donor_dashboard.php?donor_id={$userId}", [], 'GET');
$stats = $data['data'] ?? ['total_donations' => 0, 'completed_donations' => 0, 'meals_donated' => 0];

// Points calculation & Tier logic (Rewards system)
$completed = $stats['completed_donations'] ?? 0;
$meals = $stats['meals_donated'] ?? 0;
$pointsScore = $completed * 100; // E.g., 100 points per completed rescue

$tier = "Bronze Donor";
$tierColor = "#CD7F32"; // Bronze color
$nextTier = 500;

if ($pointsScore >= 500) {
    $tier = "Silver Donor";
    $tierColor = "#9CA3AF";
    $nextTier = 1000;
}
if ($pointsScore >= 1000) {
    $tier = "Gold Donor";
    $tierColor = "#F59E0B";
    $nextTier = 2500;
}
if ($pointsScore >= 2500) {
    $tier = "Platinum Donor";
    $tierColor = "#8B5CF6";
    $nextTier = 5000;
}

$progressPct = min(100, ($pointsScore / $nextTier) * 100);

include __DIR__ . '/../../includes/header.php';
?>
<style>
    .profile-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 32px;
        align-items: flex-start;
    }

    @media(max-width: 900px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }

    .rewards-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 32px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        text-align: center;
    }

    .tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        color: #fff;
        margin-bottom: 20px;
    }

    .points-display {
        font-size: 3rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .points-label {
        font-size: 0.875rem;
        color: #6B7280;
        font-weight: 500;
        margin-top: 8px;
    }

    .progress-container {
        height: 8px;
        background: #F3F4F6;
        border-radius: 10px;
        margin-top: 24px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: #2E7D32;
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    .analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 24px;
    }

    .analytics-box {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 20px 16px;
        text-align: center;
    }

    .analytics-box h4 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #2E7D32;
        margin-bottom: 4px;
    }

    .analytics-box p {
        font-size: 0.85rem;
        color: #6B7280;
        font-weight: 600;
        margin: 0;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding: 32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.75rem;font-weight:700;color:#111827;">Profile Management</h1>
            <p style="color:#6B7280;font-size:0.9rem;margin-top:6px;">Manage your donor account details securely.</p>
        </div>

        <?php if ($success): ?>
            <div
                style="background:#DCFCE7;border-left:4px solid #4CAF50;color:#1B5E20;padding:12px 16px;border-radius:6px;margin-bottom:24px;font-size:0.875rem;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Left Side: Profile Form -->
            <form method="POST" class="glass-card" style="padding:32px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '+') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address / Location</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Organization Name (Optional)</label>
                    <input type="text" name="organization" value="<?= htmlspecialchars($user['organization'] ?? '') ?>">
                </div>

                <hr style="border:0;border-top:1px solid #E5E7EB;margin:32px 0 24px 0;">

                <h3 style="font-size:1.1rem;color:#111827;margin-bottom:16px;">Security</h3>
                <div class="form-group"
                    style="display:flex;align-items:center;justify-content:space-between;background:#F9FAFB;padding:20px;border:1px solid #E5E7EB;border-radius:8px;">
                    <div>
                        <strong style="display:block;font-size:0.95rem;color:#111827;">Account Password</strong>
                        <span style="font-size:0.85rem;color:#6B7280;margin-top:2px;display:block;">Securely update your
                            access credentials</span>
                    </div>
                    <button type="button" class="btn btn-outline" style="font-size:0.875rem;padding:8px 16px;"><i
                            class="fa-solid fa-lock"></i> Change Password</button>
                </div>

                <div style="display:flex;gap:12px;margin-top:32px;">
                    <button type="submit" class="btn btn-primary" style="padding:12px 32px;"><i
                            class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                    <button type="reset" class="btn btn-outline" style="padding:12px 32px;">Cancel</button>
                </div>
            </form>

            <!-- Right Side: Analytics & Rewards -->
            <div>
                <!-- Points Card -->
                <div class="rewards-card">
                    <h3 style="font-size:1.15rem;font-weight:700;color:#111827;margin-bottom:24px;">Rewards Program</h3>

                    <div class="tier-badge" style="background:<?= $tierColor ?>;">
                        <i class="fa-solid fa-medal"></i> <?= $tier ?>
                    </div>

                    <div class="points-display"><?= number_format($pointsScore) ?></div>
                    <div class="points-label">Total Impact Points earned</div>

                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $progressPct ?>%;"></div>
                    </div>

                    <p style="font-size:0.8rem; color:#6B7280; margin-top:16px; font-weight:500;">
                        <i class="fa-solid fa-arrow-up" style="color:#2E7D32;margin-right:4px;"></i>
                        Earn <?= number_format($nextTier - $pointsScore) ?> more points to unlock the next tier!
                    </p>
                </div>

                <!-- Impact Analytics Card -->
                <div class="rewards-card" style="text-align:left;">
                    <h3 style="font-size:1.15rem;font-weight:700;color:#111827;text-align:center;">Your Global Impact
                    </h3>

                    <div class="analytics-grid">
                        <div class="analytics-box">
                            <h4><?= number_format($completed) ?></h4>
                            <p>Successful Stops</p>
                        </div>
                        <div class="analytics-box">
                            <h4><?= number_format($meals) ?></h4>
                            <p>Meals Delivered</p>
                        </div>
                    </div>

                    <p
                        style="text-align:center; font-size:0.85rem; color:#6B7280; margin-top:20px; padding:0 12px; line-height:1.5;">
                        Every completed food donation earns exactly <strong>100 Impact Points</strong>. Keep donating
                        surplus food to expand your community footprint!
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>