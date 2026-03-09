<?php
/**
 * index.php — Public Landing Page
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Optional: Define if user is logged in
$isLoggedIn = isset($_SESSION['fr_user']);

$pageTitle = 'Food Rescue | Serve. Rescue. Save.';
$pageDesc = 'A real-time surplus food donation & rescue platform connecting donors, NGOs, and volunteers.';
include __DIR__ . '/includes/header.php';
?>

<style>
    nav.top-nav {
        background: #2E7D32;
        height: 64px;
        display: flex;
        align-items: center;
        padding: 0 40px;
        justify-content: space-between;
    }

    nav.top-nav .logo {
        color: #fff;
        font-weight: 800;
        font-size: 1.3rem;
        letter-spacing: -0.02em;
        text-decoration: none;
    }

    nav.top-nav .nav-links {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .hero-section {
        min-height: 88vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 60px;
        padding: 80px 80px;
        max-width: 1280px;
        margin: 0 auto;
    }

    .hero-section h1 {
        font-size: 3.2rem;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 20px;
        color: #111827;
    }

    .hero-section p {
        font-size: 1.0625rem;
        color: #6B7280;
        line-height: 1.7;
        margin-bottom: 36px;
    }

    .hero-visual {
        background: linear-gradient(135deg, #E8F5E9 0%, #F0FDF4 100%);
        border-radius: 24px;
        padding: 60px 40px;
        text-align: center;
        animation: floating 6s ease-in-out infinite;
    }

    @keyframes floating {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }

        100% {
            transform: translateY(0);
        }
    }

    .hero-visual .icon-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        max-width: 300px;
        margin: 0 auto;
    }

    .hero-icon-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #6B7280;
    }

    .hero-icon-card i {
        font-size: 1.5rem;
    }

    section.features {
        background: #fff;
        padding: 80px 80px;
        border-top: 1px solid #F3F4F6;
    }

    .feature-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
        margin-top: 48px;
    }

    .feature-card {
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 28px;
        transition: box-shadow 0.2s;
    }

    .feature-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .feature-card .fi {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 16px;
    }

    .stats-row {
        background: #2E7D32;
        padding: 60px 80px;
        display: flex;
        justify-content: space-around;
    }

    .stat-tile {
        text-align: center;
        color: #fff;
    }

    .stat-tile h2 {
        font-size: 2.5rem;
        font-weight: 800;
    }

    .stat-tile p {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-top: 4px;
    }

    footer.site-footer {
        background: #111827;
        color: rgba(255, 255, 255, 0.5);
        text-align: center;
        padding: 28px;
        font-size: 0.8rem;
    }

    @media(max-width:900px) {

        .hero-section,
        .features,
        .stats-row {
            padding: 40px 24px;
        }

        .hero-section,
        .feature-grid-3 {
            grid-template-columns: 1fr;
        }

        .hero-visual {
            display: none;
        }

        .stats-row {
            flex-direction: column;
            gap: 32px;
        }
    }

    /* ─────────────────────────────────────────
       Landing Animated Background
    ───────────────────────────────────────── */
    .landing-bg-animation {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(46, 125, 50, 0.03) 0%, rgba(107, 114, 128, 0.03) 100%);
    }

    .landing-bg-animation i {
        position: absolute;
        color: #2E7D32;
        opacity: 0.15;
        filter: blur(1px);
        font-size: 24px;
        animation: driftUp linear infinite;
        bottom: -150px;
    }

    .landing-bg-animation i:nth-child(1) {
        left: 10%;
        font-size: 80px;
        animation-duration: 25s;
        animation-delay: 0s;
    }

    .landing-bg-animation i:nth-child(2) {
        left: 25%;
        font-size: 45px;
        animation-duration: 18s;
        animation-delay: 4s;
    }

    .landing-bg-animation i:nth-child(3) {
        left: 45%;
        font-size: 100px;
        animation-duration: 35s;
        animation-delay: 2s;
    }

    .landing-bg-animation i:nth-child(4) {
        left: 65%;
        font-size: 55px;
        animation-duration: 20s;
        animation-delay: 8s;
    }

    .landing-bg-animation i:nth-child(5) {
        left: 85%;
        font-size: 85px;
        animation-duration: 28s;
        animation-delay: 1s;
    }

    .landing-bg-animation i:nth-child(6) {
        left: 15%;
        font-size: 60px;
        animation-duration: 22s;
        animation-delay: 15s;
    }

    .landing-bg-animation i:nth-child(7) {
        left: 55%;
        font-size: 110px;
        animation-duration: 30s;
        animation-delay: 12s;
    }

    .landing-bg-animation i:nth-child(8) {
        left: 75%;
        font-size: 50px;
        animation-duration: 24s;
        animation-delay: 5s;
    }

    @keyframes driftUp {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 0;
        }

        10% {
            opacity: 0.15;
        }

        90% {
            opacity: 0.15;
        }

        100% {
            transform: translateY(-120vh) rotate(360deg);
            opacity: 0;
        }
    }
</style>

<!-- Nav -->
<nav class="top-nav">
    <a href="/index.php" class="logo"><i class="fa-solid fa-leaf"
            style="color:#4ADE80;margin-right:8px;"></i>FOODRESCUE</a>
    <div class="nav-links">
        <?php if ($isLoggedIn): ?>
            <a href="/dashboard.php" class="btn btn-primary" style="background:#fff;color:#2E7D32;">
                <i class="fa-solid fa-gauge" style="margin-right:6px;"></i> Go to Dashboard
            </a>
        <?php else: ?>
            <a href="/login.php" class="btn btn-outline" style="border-color:rgba(255,255,255,0.4);color:#fff;">Login</a>
            <a href="/register.php" class="btn btn-primary" style="background:#fff;color:#2E7D32;">Get Started</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Animated Background Effect -->
<div class="landing-bg-animation">
    <i class="fa-solid fa-leaf"></i>
    <i class="fa-solid fa-apple-whole"></i>
    <i class="fa-solid fa-hand-holding-heart"></i>
    <i class="fa-solid fa-bowl-food"></i>
    <i class="fa-solid fa-truck-fast"></i>
    <i class="fa-solid fa-box-open"></i>
    <i class="fa-solid fa-seedling"></i>
    <i class="fa-solid fa-basket-shopping"></i>
</div>
<!-- Hero -->
<section style="max-width:1280px;margin:0 auto;">
    <div class="hero-section">
        <div>
            <div
                style="display:inline-flex;align-items:center;gap:8px;background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;margin-bottom:24px;">
                <i class="fa-solid fa-circle-check"></i> Making Food Rescue Smarter
            </div>
            <h1>Eliminate <span style="color:#2E7D32;">Food Waste.</span><br>Feed Communities.</h1>
            <p>A real-time logistics ecosystem connecting surplus food resources to those who need them most. We bridge
                the gap between abundance and scarcity with precision.</p>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <a href="/register.php" class="btn btn-primary" style="padding:14px 28px;font-size:1rem;">
                    <i class="fa-solid fa-user-plus"></i> Join the Mission
                </a>
                <a href="/login.php" class="btn btn-outline" style="padding:14px 28px;font-size:1rem;">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In
                </a>
            </div>
        </div>
        <div class="hero-visual">
            <i class="fa-solid fa-leaf" style="font-size:4rem;color:#2E7D32;margin-bottom:24px;display:block;"></i>
            <div class="icon-grid">
                <?php
                $icons = [
                    ['fa-hand-holding-heart', '#f59e0b', 'Donor'],
                    ['fa-building', '#0d9488', 'NGO'],
                    ['fa-person-running', '#8b5cf6', 'Volunteer'],
                    ['fa-truck', '#2E7D32', 'Delivery'],
                    ['fa-bowl-food', '#ef4444', 'Food'],
                    ['fa-chart-line', '#0ea5e9', 'Analytics'],
                ];
                foreach ($icons as [$icon, $color, $label]):
                    ?>
                    <div class="hero-icon-card">
                        <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>;"></i>
                        <?= $label ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-tile">
        <h2><span class="count-up" data-target="15000">0</span>+</h2>
        <p>Meals Delivered</p>
    </div>
    <div class="stat-tile">
        <h2><span class="count-up" data-target="920">0</span></h2>
        <p>Certified Donors</p>
    </div>
    <div class="stat-tile">
        <h2><span class="count-up" data-target="120">0</span>+</h2>
        <p>NGO Partners</p>
    </div>
    <div class="stat-tile">
        <h2><span class="count-up" data-target="98">0</span>%</h2>
        <p>Success Rate</p>
    </div>
</div>

<!-- Features -->
<section class="features">
    <div style="text-align:center;">
        <h2 style="font-size:2.5rem;font-weight:800;color:#111827;">The Zero-Waste Infrastructure</h2>
        <p style="color:#6B7280;margin-top:12px;font-size:1rem;">Empowering communities with professional tools for food
            redistribution.</p>
    </div>
    <div class="feature-grid-3">
        <div class="feature-card">
            <div class="fi" style="background:#F0FDF4;"><i class="fa-solid fa-shield-halved" style="color:#2E7D32;"></i>
            </div>
            <h3 style="margin-bottom:10px;font-size:1.1rem;">Secure Network</h3>
            <p style="color:#6B7280;font-size:0.9rem;line-height:1.6;">Multi-layer verification for all donors and NGOs
                ensuring a trusted chain of custody.</p>
        </div>
        <div class="feature-card">
            <div class="fi" style="background:#FFF7ED;"><i class="fa-solid fa-bolt" style="color:#f59e0b;"></i></div>
            <h3 style="margin-bottom:10px;font-size:1.1rem;">Instant Alerts</h3>
            <p style="color:#6B7280;font-size:0.9rem;line-height:1.6;">Real-time notifications connect NGOs to surplus
                food the moment it becomes available.</p>
        </div>
        <div class="feature-card">
            <div class="fi" style="background:#F0FDFA;"><i class="fa-solid fa-location-dot" style="color:#0d9488;"></i>
            </div>
            <h3 style="margin-bottom:10px;font-size:1.1rem;">Smart Logistics</h3>
            <p style="color:#6B7280;font-size:0.9rem;line-height:1.6;">Hyper-local matching algorithms connect surplus
                to the nearest available rescue unit.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background:#F0FDF4;padding:80px;text-align:center;border-top:1px solid #BBF7D0;">
    <h2 style="font-size:2.2rem;font-weight:800;color:#111827;margin-bottom:16px;">Ready to Make a Difference?</h2>
    <p style="color:#6B7280;font-size:1rem;margin-bottom:36px;">Join thousands of donors, NGOs, and volunteers on the
        platform.</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="/register.php?role=DONOR" class="btn btn-primary"
            style="padding:14px 32px;font-size:1rem;background:#f59e0b;border:none;">
            <i class="fa-solid fa-hand-holding-heart"></i> Register as Donor
        </a>
        <a href="/register.php?role=NGO" class="btn btn-primary"
            style="padding:14px 32px;font-size:1rem;background:#0d9488;border:none;">
            <i class="fa-solid fa-building"></i> Register as NGO
        </a>
        <a href="/register.php?role=VOLUNTEER" class="btn btn-primary"
            style="padding:14px 32px;font-size:1rem;background:#8b5cf6;border:none;">
            <i class="fa-solid fa-person-running"></i> Become a Volunteer
        </a>
    </div>
</section>

<footer class="site-footer">
    © 2025 Food Rescue Platform. Built to end hunger and reduce waste.
</footer>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.count-up');

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const duration = 2000; // Total animation time

                    let count = 0;
                    const updateCount = () => {
                        // easing-like increment
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

<?php include __DIR__ . '/includes/footer.php'; ?>