<?php
/**
 * pages/volunteer/profile.php — Volunteer Profile
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'profile';
$pageTitle = 'My Profile | Food Rescue';
$userId = $sessionUser['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    $act = $_POST['_action'];
    if ($act === 'update_profile') {
        $res = apiCall('/volunteer/update_profile.php', [
            'user_id' => $userId,
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'availability' => trim($_POST['availability'] ?? 'FLEXIBLE'),
        ]);
        if (!empty($res['success'])) {
            $_SESSION['user']['name'] = $_POST['name'];
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $res['message'] ?? 'Update failed.'];
        }
    }
    header('Location: /pages/volunteer/profile.php');
    exit();
}

$pData = apiCall("/volunteer/get_profile.php?user_id={$userId}", [], 'GET');
$profile = $pData['data']['profile'] ?? [];
$completed = $profile['COMPLETED'] ?? 0;
$active = $profile['ACTIVE'] ?? 0;
$meals = $profile['MEALS'] ?? 0;
$points = $profile['POINTS'] ?? 0;

include __DIR__ . '/../../includes/header.php';
?>
<style>
    .prof-hero {
        background: linear-gradient(135deg, #4c1d95, #7c3aed, #8b5cf6);
        border-radius: 14px;
        padding: 36px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .prof-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .prof-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .2);
        border: 3px solid rgba(255, 255, 255, .5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        margin: 0 auto 14px;
        animation: avatarPop .6s cubic-bezier(.34, 1.56, .64, 1) both;
    }

    @keyframes avatarPop {
        from {
            transform: scale(0.5);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .prof-name {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .prof-role {
        color: rgba(255, 255, 255, .7);
        font-size: .875rem;
        margin: 0 0 20px;
    }

    .prof-stats {
        display: flex;
        justify-content: center;
        gap: 32px;
        flex-wrap: wrap;
    }

    .prof-stat {
        text-align: center;
    }

    .prof-stat-val {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 800;
        display: block;
    }

    .prof-stat-lbl {
        color: rgba(255, 255, 255, .65);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .prof-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media(max-width:768px) {
        .prof-layout {
            grid-template-columns: 1fr;
        }
    }

    .form-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 28px;
        opacity: 0;
        transform: translateY(16px);
        animation: cardUp .5s .1s ease both;
    }

    .form-card.right {
        animation-delay: .2s;
    }

    @keyframes cardUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-card-title {
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #F3F4F6;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: .9rem;
        color: #111827;
        background: #fff;
        transition: border-color .2s, box-shadow .2s;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .12);
    }

    .form-group input:disabled {
        background: #F8FAFC;
        color: #9CA3AF;
        cursor: not-allowed;
    }

    .btn-save {
        width: 100%;
        padding: 12px;
        background: #8b5cf6;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: .95rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background .2s, transform .15s;
    }

    .btn-save:hover {
        background: #7c3aed;
        transform: translateY(-2px);
    }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: .78rem;
        font-weight: 700;
    }

    .achievement-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 12px;
    }

    .achievement-item {
        background: #F8FAFC;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        padding: 14px;
        text-align: center;
    }

    .ach-icon {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 6px;
    }

    .ach-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6B7280;
    }

    .ach-val {
        font-size: 1.1rem;
        font-weight: 800;
        color: #111827;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding:32px;">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <!-- Hero Card -->
        <div class="prof-hero">
            <div class="prof-avatar">
                <?= strtoupper(substr($profile['NAME'] ?? 'V', 0, 1)) ?>
            </div>
            <div class="prof-name">
                <?= htmlspecialchars($profile['NAME'] ?? 'Volunteer') ?>
            </div>
            <div class="prof-role">
                <span class="badge-chip" style="background:rgba(255,255,255,.2);color:#fff;">
                    <i class="fa-solid fa-truck"></i> Volunteer · Member since
                    <?= $profile['JOINED'] ?? 'N/A' ?>
                </span>
            </div>
            <div class="prof-stats">
                <div class="prof-stat">
                    <span class="prof-stat-val">
                        <?= $completed ?>
                    </span>
                    <span class="prof-stat-lbl">Missions Done</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-val">
                        <?= $active ?>
                    </span>
                    <span class="prof-stat-lbl">Active Tasks</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-val">~
                        <?= number_format($meals) ?>
                    </span>
                    <span class="prof-stat-lbl">Meals Rescued</span>
                </div>
                <div class="prof-stat">
                    <span class="prof-stat-val">
                        <?= number_format($points) ?>
                    </span>
                    <span class="prof-stat-lbl">Points Earned</span>
                </div>
            </div>
        </div>

        <div class="prof-layout">
            <!-- Edit Profile Form -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-pen-to-square" style="color:#8b5cf6;"></i>
                    Edit Profile
                </div>
                <form method="POST">
                    <input type="hidden" name="_action" value="update_profile">
                    <div class="form-group">
                        <label>Full Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars($profile['NAME'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($profile['EMAIL'] ?? '') ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($profile['PHONE'] ?? '') ?>"
                            placeholder="Enter phone number">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($profile['CITY'] ?? '') ?>"
                            placeholder="Your city">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($profile['ADDRESS'] ?? '') ?>"
                            placeholder="Your address">
                    </div>
                    <div class="form-group">
                        <label>Availability Status</label>
                        <select name="availability">
                            <?php foreach (['FLEXIBLE', 'WEEKDAYS', 'WEEKENDS', 'MORNINGS', 'EVENINGS', 'FULL_TIME'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($profile['AVAILABILITY'] ?? '') === $opt ? 'selected' : '' ?>
                                    >
                                    <?= ucfirst(strtolower($opt)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Right Side: Achievements + Account Info -->
            <div>
                <!-- Achievements -->
                <div class="form-card right" style="margin-bottom:24px;">
                    <div class="form-card-title">
                        <i class="fa-solid fa-trophy" style="color:#d97706;"></i>
                        Your Achievements
                    </div>
                    <div class="achievement-grid">
                        <div class="achievement-item">
                            <span class="ach-icon">🏆</span>
                            <div class="ach-val">
                                <?= $completed ?>
                            </div>
                            <div class="ach-label">Missions</div>
                        </div>
                        <div class="achievement-item">
                            <span class="ach-icon">🍱</span>
                            <div class="ach-val">~
                                <?= $meals ?>
                            </div>
                            <div class="ach-label">Meals</div>
                        </div>
                        <div class="achievement-item">
                            <span class="ach-icon">⭐</span>
                            <div class="ach-val">
                                <?= $points ?>
                            </div>
                            <div class="ach-label">Points</div>
                        </div>
                        <div class="achievement-item">
                            <span class="ach-icon">🚴</span>
                            <div class="ach-val">
                                <?= $active ?>
                            </div>
                            <div class="ach-label">Active</div>
                        </div>
                    </div>

                    <!-- Points bar -->
                    <div style="margin-top:16px;">
                        <div
                            style="display:flex;justify-content:space-between;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:6px;">
                            <span>Progress to Next Badge</span>
                            <span>
                                <?= $points ?> / 500 pts
                            </span>
                        </div>
                        <div style="background:#F1F5F9;border-radius:99px;height:8px;overflow:hidden;">
                            <div style="height:8px;background:linear-gradient(90deg,#8b5cf6,#d97706);border-radius:99px;width:0%;transition:width 1.2s ease;"
                                data-width="<?= min(100, round($points / 500 * 100)) ?>%" class="anim-bar"></div>
                        </div>
                        <div style="font-size:.7rem;color:#9CA3AF;margin-top:4px;">
                            <?= max(0, 500 - $points) ?> pts more to reach Gold Badge 🥇
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="form-card right">
                    <div class="form-card-title">
                        <i class="fa-solid fa-shield-halved" style="color:#2E7D32;"></i>
                        Account Information
                    </div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #F3F4F6;">
                            <span style="font-size:.8rem;color:#6B7280;">Email</span>
                            <span style="font-size:.8rem;font-weight:600;color:#111827;">
                                <?= htmlspecialchars($profile['EMAIL'] ?? '—') ?>
                            </span>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #F3F4F6;">
                            <span style="font-size:.8rem;color:#6B7280;">Account Status</span>
                            <span
                                style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:99px;font-size:.72rem;font-weight:700;">
                                <?= $profile['STATUS'] ?? 'ACTIVE' ?>
                            </span>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #F3F4F6;">
                            <span style="font-size:.8rem;color:#6B7280;">Member Since</span>
                            <span style="font-size:.8rem;font-weight:600;color:#111827;">
                                <?= $profile['JOINED'] ?? '—' ?>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;">
                            <span style="font-size:.8rem;color:#6B7280;">Availability</span>
                            <span
                                style="background:#ede9fe;color:#7c3aed;padding:2px 10px;border-radius:99px;font-size:.72rem;font-weight:700;">
                                <?= $profile['AVAILABILITY'] ?? 'FLEXIBLE' ?>
                            </span>
                        </div>
                    </div>
                    <div style="margin-top:20px;display:flex;gap:10px;">
                        <a href="/logout.php"
                            style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:8px;background:#FEF2F2;color:#dc2626;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid #FECACA;">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.anim-bar').forEach(b => {
            const w = b.getAttribute('data-width');
            setTimeout(() => { b.style.width = w; }, 400);
        });
    });
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>