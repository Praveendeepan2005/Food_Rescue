<?php
/**
 * forgot_password.php — Pure PHP Password Reset Page
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (empty($email) || empty($newPass) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $res = apiCall('/auth/forgot_password.php', ['email' => $email, 'new_password' => $newPass]);
        if (!empty($res['success'])) {
            $success = 'Password reset successfully! You can now log in with your new password.';
        } else {
            $error = $res['message'] ?? 'Password reset failed. Please check your email.';
        }
    }
}

$pageTitle = 'Reset Password | Food Rescue';
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
        justify-content: center;
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
    }

    .auth-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
        margin-bottom: 48px;
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

    .hero-text {
        position: relative;
        z-index: 1;
    }

    .hero-text h2 {
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 16px;
        color: #fff;
    }

    .hero-text p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.75);
        line-height: 1.7;
        max-width: 360px;
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

    .auth-form-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .auth-form-header .icon {
        width: 64px;
        height: 64px;
        background: #F0FDF4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #2E7D32;
        margin: 0 auto 16px;
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
        margin-top: 8px;
    }

    .auth-submit:hover {
        background: #1B5E20;
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

    .alert-success {
        background: #DCFCE7;
        border-left: 4px solid #2E7D32;
        color: #166534;
        padding: 14px 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
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
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-icon"><i class="fa-solid fa-leaf" style="color:#4ADE80;"></i></div>
            <div class="auth-brand-text">
                <span class="name">Food Rescue</span>
                <span class="sub">Platform</span>
            </div>
        </div>
        <div class="hero-text">
            <h2>Reset Your<br>Password</h2>
            <p>Enter your registered email address and set a new secure password. Your account data remains safe.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-form-box">
            <div class="auth-form-header">
                <div class="icon"><i class="fa-solid fa-key"></i></div>
                <h1>Reset Password</h1>
                <p>Enter your credentials to set a new password</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <div style="text-align:center;">
                    <a href="/login.php"
                        style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#2E7D32;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:0.875rem;">
                        <i class="fa-solid fa-right-to-bracket"></i> Go to Login
                    </a>
                </div>
            <?php else: ?>

                <!-- Hidden autofill blockers -->
                <div style="position:absolute;top:-9999px;left:-9999px;">
                    <input type="text" name="fake_u"><input type="password" name="fake_p">
                </div>

                <form method="POST" autocomplete="off">
                    <div class="form-field">
                        <label for="fp-email">Registered Email <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input class="auth-input" type="email" id="fp-email" name="email" required
                                placeholder="name@example.com" autocomplete="off" readonly
                                onfocus="this.removeAttribute('readonly')"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="fp-pwd">New Password <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" id="fp-pwd" name="new_password" required
                                placeholder="Min 6 characters" autocomplete="new-password" readonly
                                onfocus="this.removeAttribute('readonly')">
                            <button type="button" onclick="togglePwd('fp-pwd','e1')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:0;"><i
                                    class="fa-regular fa-eye" id="e1"></i></button>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="fp-cpwd">Confirm New Password <span style="color:#D32F2F">*</span></label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input class="auth-input" type="password" id="fp-cpwd" name="confirm_password" required
                                placeholder="Re-enter password" autocomplete="off" readonly
                                onfocus="this.removeAttribute('readonly')">
                            <button type="button" onclick="togglePwd('fp-cpwd','e2')"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:0;"><i
                                    class="fa-regular fa-eye" id="e2"></i></button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">
                        <i class="fa-solid fa-key"></i> Reset Password
                    </button>
                </form>

            <?php endif; ?>

            <div class="auth-switch">
                Remembered it? <a href="/login.php">Back to Login</a><br>
                <a href="/index.php" style="color:#9CA3AF;font-size:0.8rem;font-weight:400;text-decoration:none;">← Back
                    to Home</a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePwd(fieldId, iconId) {
        const f = document.getElementById(fieldId);
        const i = document.getElementById(iconId);
        if (f.type === 'password') { f.type = 'text'; i.className = 'fa-regular fa-eye-slash'; }
        else { f.type = 'password'; i.className = 'fa-regular fa-eye'; }
    }
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>