<?php
/**
 * pages/ngo/profile.php — NGO Profile Management
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'profile';
$pageTitle = 'NGO Profile | Food Rescue';
$userId = $sessionUser['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = apiCall('/ngo/update_profile.php', [
        'user_id' => $userId,
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'ngo_reg_number' => $_POST['ngo_reg_number'] ?? ''
    ]);

    if (!empty($res['success'])) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => $res['message'] ?? 'Profile updated successfully'];
        // Update session name if changed
        if (isset($res['data']['name'])) {
            $_SESSION['user']['name'] = $res['data']['name'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => $res['message'] ?? 'Failed to update profile.'];
    }

    header('Location: /pages/ngo/profile.php');
    exit();
}

// Fetch Current Profile
$data = apiCall("/ngo/get_profile.php?user_id={$userId}", [], 'GET');
$profile = $data['data'] ?? [];

$stats = $profile['stats'] ?? ['points' => 0, 'completed' => 0, 'assignments' => 0];

include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Rewards Animations */
    @keyframes floatUp {
        0% {
            transform: translateY(20px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes pulseGlow {
        0% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(245, 158, 11, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
        }
    }

    .reward-pane {
        animation: floatUp 0.6s ease-out forwards;
    }

    .reward-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .reward-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
        opacity: 0.5;
        pointer-events: none;
    }

    .points-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px auto;
        border: 4px solid rgba(255, 255, 255, 0.2);
        animation: pulseGlow 2s infinite;
    }

    .points-value {
        font-size: 2.5rem;
        font-weight: 900;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .points-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        opacity: 0.9;
    }

    .impact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 24px;
    }

    .impact-box {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        backdrop-filter: blur(10px);
        transition: transform 0.2s ease;
    }

    .impact-box:hover {
        transform: translateY(-3px);
    }

    .impact-box i {
        font-size: 1.5rem;
        margin-bottom: 8px;
        color: #4ade80;
    }

    .impact-val {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .impact-title {
        font-size: 0.75rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Organization Profile</h1>
            <p style="color:#6B7280;font-size:0.875rem;margin-top:4px;">Manage your NGO details and registration info.
            </p>
        </div>

        <style>
            .profile-grid-layout {
                display: flex;
                flex-direction: column;
                gap: 32px;
            }
            @media (min-width: 1024px) {
                .profile-grid-layout {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 48px;
                    align-items: start;
                    max-width: 1100px; /* Keep it compact and professional */
                }
            }
        </style>
        <div class="profile-grid-layout">

            <!-- Left Column: Form -->
            <div class="glass-card" style="width:100%;">
                <form method="POST">
                    <div class="form-group">
                        <label>Organization Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($profile['NAME'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control"
                            value="<?= htmlspecialchars($profile['EMAIL'] ?? '') ?>" disabled
                            style="background:#F9FAFB;cursor:not-allowed;">
                        <small style="color:#6B7280;">Contact support to change email address.</small>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?= htmlspecialchars($profile['PHONE'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Physical Address / Headquarters</label>
                        <textarea name="address" class="form-control" rows="3"
                            placeholder="Street layout, building details..."><?= htmlspecialchars($profile['ADDRESS'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>NGO Registration Number</label>
                        <input type="text" name="ngo_reg_number" class="form-control"
                            value="<?= htmlspecialchars($profile['NGO_REG_NUMBER'] ?? '') ?>"
                            placeholder="Government issued registration no.">
                    </div>

                    <div style="display:flex;gap:12px;margin-top:24px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-outline"
                            onclick="alert('Password changing feature coming soon.');">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Rewards -->
            <div class="reward-pane">
                <div class="reward-card">
                    <div style="text-align:center;">
                        <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:24px;color:#f8fafc;">
                            <i class="fa-solid fa-trophy" style="color:#fde047;margin-right:8px;"></i> IMPACT REWARDS
                        </h2>

                        <div class="points-circle">
                            <div class="points-value">
                                <span class="count-up" data-target="<?= $stats['points'] ?>">0</span>
                            </div>
                            <div class="points-label">Total Points</div>
                        </div>

                        <p style="color:#cbd5e1;font-size:0.9rem;line-height:1.5;">
                            Great job! Your NGO earns 10 pts for assigning volunteers and 50 pts for every successful
                            delivery mission.
                        </p>
                    </div>

                    <div class="impact-grid">
                        <div class="impact-box">
                            <i class="fa-solid fa-people-carry-box" style="color:#38bdf8;"></i>
                            <div class="impact-val"><span class="count-up"
                                    data-target="<?= $stats['assignments'] ?>">0</span></div>
                            <div class="impact-title">Volunteers Assigned</div>
                        </div>
                        <div class="impact-box">
                            <i class="fa-solid fa-truck-fast"></i>
                            <div class="impact-val"><span class="count-up"
                                    data-target="<?= $stats['completed'] ?>">0</span></div>
                            <div class="impact-title">Missions Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Include the count-up logic for animations
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