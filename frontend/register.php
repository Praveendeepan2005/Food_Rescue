<?php
/**
 * register.php — Pure PHP Registration Page
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (isset($_SESSION['fr_user'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once __DIR__ . '/includes/api_call.php';

$error = '';
$success = '';
$post = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $role = strtoupper(trim($_POST['role'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $orgName = trim($_POST['org_name'] ?? '');
    $ngoId = trim($_POST['belongs_to_ngo_id'] ?? '');
    $avail = trim($_POST['availability'] ?? 'anytime');

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'VOLUNTEER' && empty($ngoId)) {
        $error = 'NGO Code is required for volunteers.';
    } else {
        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'phone' => $phone ?: null,
            'city' => $city ?: null,
        ];
        if ($role === 'NGO' && $orgName)
            $payload['org_name'] = $orgName;
        if ($role === 'VOLUNTEER')
            $payload['belongs_to_ngo_id'] = (int) $ngoId;
        if ($role === 'VOLUNTEER')
            $payload['availability'] = $avail;

        $res = apiCall('/auth/register.php', $payload);
        if (!empty($res['success'])) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Account created! Please log in.'];
            header('Location: /login.php');
            exit();
        } else {
            $error = $res['message'] ?? 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register | Food Link';
include __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background: #F5F7F6;
    }

    .auth-page {
        display: flex;
        min-height: 100vh;
    }

    .auth-left {
        flex: 1;
        background: #2E7D32;
        padding: 64px 56px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .auth-left::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        pointer-events: none;
    }

    .auth-left::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: -60px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        pointer-events: none;
    }

    .auth-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1;
        position: relative;
    }

    .auth-brand-icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .auth-brand-text .name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #fff;
        display: block;
    }

    .auth-brand-text .sub {
        font-size: 0.7rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.65);
    }

    .auth-hero-text {
        z-index: 1;
        position: relative;
        margin: 40px 0;
    }

    .auth-hero-text h2 {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 16px;
        color: #fff;
    }

    .auth-hero-text p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.75);
        line-height: 1.7;
        max-width: 360px;
    }

    .auth-features {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        z-index: 1;
        position: relative;
    }

    .auth-feat-pill {
        background: rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .auth-left-footer {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.55);
        z-index: 1;
        position: relative;
    }

    .auth-right {
        flex: 1;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        background: #fff;
        padding: 40px;
        overflow-y: auto;
    }

    .auth-form-box {
        width: 100%;
        max-width: 440px;
        padding-top: 20px;
    }

    .role-tabs {
        display: flex;
        gap: 4px;
        background: #F5F7F6;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 4px;
        margin-bottom: 24px;
    }

    .role-tab {
        flex: 1;
        padding: 10px 6px;
        border: none;
        background: transparent;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.2s;
    }

    .role-tab.active {
        background: #2E7D32;
        color: #fff;
        font-weight: 600;
    }

    .auth-form-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .auth-form-header h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .auth-form-header p {
        color: #6B7280;
        font-size: 0.875rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .form-field {
        margin-bottom: 14px;
    }

    .form-field label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #111827;
        margin-bottom: 6px;
    }

    .input-wrap {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 0.875rem;
        pointer-events: none;
    }

    .auth-input {
        width: 100%;
        padding: 10px 12px 10px 38px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        color: #111827;
        font-size: 0.875rem;
        transition: border-color 0.2s;
        outline: none;
        font-family: inherit;
    }

    .auth-input:focus {
        border-color: #2E7D32;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.08);
    }

    .auth-input::placeholder {
        color: #9CA3AF;
    }

    .auth-input select {
        appearance: none;
    }

    .auth-submit {
        width: 100%;
        padding: 12px;
        background: #2E7D32;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        font-family: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .auth-submit:hover {
        background: #1B5E20;
    }

    .auth-switch {
        text-align: center;
        margin-top: 16px;
        font-size: 0.875rem;
        color: #6B7280;
    }

    .auth-switch a {
        color: #2E7D32;
        font-weight: 600;
        text-decoration: none;
    }

    .auth-switch a:hover {
        text-decoration: underline;
    }

    .alert-error {
        background: #FEE2E2;
        border-left: 4px solid #D32F2F;
        color: #B91C1C;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    @media(max-width:900px) {
        .auth-left {
            display: none;
        }

        .auth-right {
            padding: 24px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="auth-page">
    <div class="auth-left">
        <a href="/index.php" class="auth-brand" style="text-decoration: none;">
            <div class="auth-brand-icon"><i class="fa-solid fa-leaf" style="color:#4ADE80;"></i></div>
            <div class="auth-brand-text">
                <span class="name">Food Link</span>
                <span class="sub">Platform</span>
            </div>
        </a>
        <div class="auth-hero-text">
            <h2>Join the Mission.<br>Make a Difference.</h2>
            <p>Register as a Donor, NGO, or Volunteer and become part of the network that's revolutionizing surplus food
                redistribution.</p>
        </div>
        <div class="auth-features">
            <div class="auth-feat-pill"><i class="fa-solid fa-hand-holding-heart"
                    style="color:rgba(255,255,255,0.8);"></i> Donors Welcome</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-building" style="color:rgba(255,255,255,0.8);"></i> NGO
                Partners</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-person-running" style="color:rgba(255,255,255,0.8);"></i>
                Volunteers</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-map-location-dot"
                    style="color:rgba(255,255,255,0.8);"></i> Hyper-local</div>
        </div>
        <div class="auth-left-footer">© 2025 Food Link Platform. All rights reserved.</div>
    </div>

    <div class="auth-right">
        <div class="auth-form-box">
            <form method="POST" id="reg-form" autocomplete="off">
                <input type="hidden" name="role" id="selected-role" value="DONOR">

                <div class="role-tabs">
                    <button type="button" class="role-tab active" onclick="setRole('DONOR',this)"><i
                            class="fa-solid fa-hand-holding-heart"></i> Donor</button>
                    <button type="button" class="role-tab" onclick="setRole('NGO',this)"><i
                            class="fa-solid fa-building"></i> NGO</button>
                    <button type="button" class="role-tab" onclick="setRole('VOLUNTEER',this)"><i
                            class="fa-solid fa-person-running"></i> Volunteer</button>
                </div>

                <div class="auth-form-header">
                    <h1>Create Account</h1>
                    <p>Join the Food Link network today</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Hidden autofill blockers -->
                <div style="position:absolute;top:-9999px;left:-9999px;">
                    <input type="text" name="fake_u"><input type="password" name="fake_p">
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Full Name <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input class="auth-input" type="text" name="name" required placeholder="e.g. Priya Raj"
                                autocomplete="off" value="<?= htmlspecialchars($post['name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Phone</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input class="auth-input" type="tel" name="phone" placeholder="9876543210"
                                value="<?= htmlspecialchars($post['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-field">
                    <label>City <span style="color:#D32F2F">*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-city"></i></span>
                        <input class="auth-input" type="text" name="city" required placeholder="e.g. Chennai"
                            value="<?= htmlspecialchars($post['city'] ?? '') ?>">
                    </div>
                </div>

                <!-- NGO org name -->
                <div class="form-field" id="field-org" style="display:none;">
                    <label>NGO / Organization Name</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-building"></i></span>
                        <input class="auth-input" type="text" name="org_name" placeholder="e.g. Hope Foundation"
                            value="<?= htmlspecialchars($post['org_name'] ?? '') ?>">
                    </div>
                </div>

                <!-- Volunteer NGO code -->
                <div class="form-field" id="field-ngo" style="display:none;">
                    <label>NGO Code (ID) <span style="color:#D32F2F">*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-link"></i></span>
                        <input class="auth-input" type="number" name="belongs_to_ngo_id"
                            placeholder="Enter NGO ID from your organization"
                            value="<?= htmlspecialchars($post['belongs_to_ngo_id'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-field">
                    <label>Email Address <span style="color:#D32F2F">*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input class="auth-input" type="email" name="email" required placeholder="name@example.com"
                            autocomplete="off" readonly onfocus="this.removeAttribute('readonly')"
                            value="<?= htmlspecialchars($post['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label>Password <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" name="password" id="pwd" required
                                placeholder="Min 6 characters" autocomplete="new-password" readonly
                                onfocus="this.removeAttribute('readonly')">
                            <button type="button" onclick="togglePwd('pwd','e1')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:0;"><i
                                    class="fa-regular fa-eye" id="e1"></i></button>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Confirm Password <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" name="confirm_password" id="cpwd" required
                                placeholder="Re-enter password" autocomplete="off" readonly
                                onfocus="this.removeAttribute('readonly')">
                            <button type="button" onclick="togglePwd('cpwd','e2')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:0;"><i
                                    class="fa-regular fa-eye" id="e2"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Volunteer availability -->
                <div class="form-field" id="field-avail" style="display:none;">
                    <label>Availability</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-calendar-days"></i></span>
                        <select class="auth-input" name="availability" style="appearance:none;">
                            <option value="weekdays">Weekdays</option>
                            <option value="weekends">Weekends</option>
                            <option value="anytime" selected>Anytime</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fa-solid fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-switch">
                Already have an account? <a href="/login.php">Log in here</a><br>
                <a href="/index.php" style="color:#9CA3AF;font-size:0.8rem;font-weight:400;text-decoration:none;">← Back
                    to Home</a>
            </div>
        </div>
    </div>
</div>

<script>
    function setRole(role, btn) {
        document.getElementById('selected-role').value = role;
        document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('field-org').style.display = role === 'NGO' ? 'block' : 'none';
        document.getElementById('field-ngo').style.display = role === 'VOLUNTEER' ? 'block' : 'none';
        document.getElementById('field-avail').style.display = role === 'VOLUNTEER' ? 'block' : 'none';
    }
    function togglePwd(fieldId, iconId) {
        const f = document.getElementById(fieldId);
        const i = document.getElementById(iconId);
        if (f.type === 'password') { f.type = 'text'; i.className = 'fa-regular fa-eye-slash'; }
        else { f.type = 'password'; i.className = 'fa-regular fa-eye'; }
    }
</script>

<?php include __DIR__ . '/includes/simple_footer.php'; ?>