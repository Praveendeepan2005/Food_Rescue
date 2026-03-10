<?php
/**
 * login.php — Pure PHP Login Page
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['fr_user'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once __DIR__ . '/includes/api_call.php';

$error = '';
$success = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = strtoupper(trim($_POST['role'] ?? 'DONOR'));

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $res = apiCall('/auth/login.php', ['email' => $email, 'password' => $password]);
        if (!empty($res['success']) && !empty($res['data'])) {
            $user = $res['data'];
            // Validate role matches selection (except admin can pick any)
            if ($user['role'] !== $role && $role !== 'ADMIN') {
                $error = 'Incorrect role selected for this account.';
            } else {
                $_SESSION['fr_user'] = $user;
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Welcome back, ' . htmlspecialchars($user['name']) . '!'];
                header('Location: /dashboard.php');
                exit();
            }
        } else {
            $error = $res['message'] ?? 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login | Food Link';
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
        font-size: 2.2rem;
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
        align-items: center;
        justify-content: center;
        background: #fff;
        padding: 40px;
    }

    .auth-form-box {
        width: 100%;
        max-width: 420px;
    }

    .role-tabs {
        display: flex;
        gap: 4px;
        background: #F5F7F6;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 4px;
        margin-bottom: 28px;
    }

    .role-tab {
        flex: 1;
        padding: 10px 8px;
        border: none;
        background: transparent;
        border-radius: 6px;
        font-size: 0.82rem;
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
        margin-bottom: 24px;
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

    .form-field {
        margin-bottom: 16px;
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

    .forgot-link {
        color: #2E7D32;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        float: right;
        margin-bottom: 20px;
    }

    .forgot-link:hover {
        text-decoration: underline;
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
        margin-top: 20px;
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
        margin-bottom: 20px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .admin-badge {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-left: 3px solid #EF4444;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 20px;
        color: #6B7280;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-badge strong {
        color: #111827;
        font-size: 0.875rem;
        display: block;
        margin-bottom: 2px;
    }

    @media(max-width:900px) {
        .auth-left {
            display: none;
        }

        .auth-right {
            padding: 24px;
        }
    }
</style>

<div class="auth-page">
    <!-- Left Panel -->
    <div class="auth-left">
        <a href="/index.php" class="auth-brand" style="text-decoration: none;">
            <div class="auth-brand-icon"><i class="fa-solid fa-leaf" style="color:#4ADE80;"></i></div>
            <div class="auth-brand-text">
                <span class="name">Food Link</span>
                <span class="sub">Platform</span>
            </div>
        </a>
        <div class="auth-hero-text">
            <h2>Rescue Food.<br>Save Lives.</h2>
            <p>Join our network of donors, NGOs and volunteers working together to eliminate food waste and feed those
                in need.</p>
        </div>
        <div class="auth-features">
            <div class="auth-feat-pill"><i class="fa-solid fa-shield-halved" style="color:rgba(255,255,255,0.8);"></i>
                Secure Login</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-bolt" style="color:rgba(255,255,255,0.8);"></i> Real-time
                Alerts</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-location-dot" style="color:rgba(255,255,255,0.8);"></i>
                Live Tracking</div>
            <div class="auth-feat-pill"><i class="fa-solid fa-chart-line" style="color:rgba(255,255,255,0.8);"></i>
                Analytics</div>
        </div>
        <div class="auth-left-footer">© 2025 Food Link Platform. All rights reserved.</div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-right">
        <div class="auth-form-box">

            <!-- Role Tabs -->
            <form method="POST" id="login-form" autocomplete="off">
                <input type="hidden" name="role" id="selected-role" value="DONOR">

                <div class="role-tabs">
                    <button type="button" class="role-tab active" onclick="setRole('DONOR', this)"><i
                            class="fa-solid fa-hand-holding-heart"></i> Donor</button>
                    <button type="button" class="role-tab" onclick="setRole('NGO', this)"><i
                            class="fa-solid fa-building"></i> NGO</button>
                    <button type="button" class="role-tab" onclick="setRole('VOLUNTEER', this)"><i
                            class="fa-solid fa-person-running"></i> Volunteer</button>
                    <button type="button" class="role-tab" onclick="setRole('ADMIN', this)"><i
                            class="fa-solid fa-user-shield"></i> Admin</button>
                </div>

                <div class="auth-form-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to your Food Link account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div id="admin-notice" style="display:none;" class="admin-badge">
                    <i class="fa-solid fa-user-shield" style="font-size:1.25rem;color:#EF4444;"></i>
                    <div><strong>Admin Access Only</strong>Administrators use this portal. Registration is handled
                        internally.</div>
                </div>

                <!-- Hidden fake fields to block autofill -->
                <div style="position:absolute;top:-1000px;left:-1000px;">
                    <input type="text" name="fake_user">
                    <input type="password" name="fake_pass">
                </div>

                <div class="form-field">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                        <input class="auth-input" type="email" id="email" name="email" required
                            placeholder="name@example.com" autocomplete="off" readonly
                            onfocus="this.removeAttribute('readonly')"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input class="auth-input" type="password" id="password" name="password" required
                            placeholder="••••••••" autocomplete="new-password" readonly
                            onfocus="this.removeAttribute('readonly')">
                        <button type="button" onclick="togglePwd()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:0;">
                            <i class="fa-regular fa-eye" id="pwd-eye"></i>
                        </button>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
                    <a href="/forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In
                </button>
            </form>

            <div class="auth-switch" style="margin-top:16px;">
                Don't have an account? <a href="/register.php">Create one now</a><br>
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
        document.getElementById('admin-notice').style.display = (role === 'ADMIN') ? 'flex' : 'none';
    }
    function togglePwd() {
        const f = document.getElementById('password');
        const i = document.getElementById('pwd-eye');
        if (f.type === 'password') { f.type = 'text'; i.className = 'fa-regular fa-eye-slash'; }
        else { f.type = 'password'; i.className = 'fa-regular fa-eye'; }
    }
</script>

<?php include __DIR__ . '/includes/simple_footer.php'; ?>