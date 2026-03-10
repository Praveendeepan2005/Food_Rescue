<?php
/**
 * index.php — Public Landing Page
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Optional: Define if user is logged in
$isLoggedIn = isset($_SESSION['fr_user']);

$pageTitle = 'Food Link | Serve. Link. Save.';
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
        background: url('food_rescue_hero_bg_1773131764771.png') center/cover no-repeat;
        border-radius: 24px;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
        animation: floating 6s ease-in-out infinite;
        position: relative;
    }

    .hero-visual::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(2px);
        border-radius: 24px;
        z-index: 0;
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
        gap: 32px;
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
        padding: 40px;
        position: relative;
    }

    .hero-visual .icon-grid::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, rgba(46, 125, 50, 0.05) 0%, transparent 70%);
        z-index: -1;
    }

    /* Connection Lines SVG */
    .grid-connections {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .grid-connections path {
        stroke: #2E7D32;
        stroke-width: 1.5;
        fill: none;
        stroke-dasharray: 8 8;
        opacity: 0.15;
        animation: dashFlow 20s linear infinite;
    }

    .grid-connections path.glow-path {
        stroke: #4ADE80;
        stroke-width: 2.5;
        stroke-dasharray: 40 260;
        opacity: 0.6;
        filter: blur(1.5px);
        animation: glowFlow 4s linear infinite;
    }

    @keyframes dashFlow {
        to {
            stroke-dashoffset: -200;
        }
    }

    @keyframes glowFlow {
        to {
            stroke-dashoffset: -300;
        }
    }

    .hero-icon-card {
        background: #fff;
        background: color-mix(in srgb, var(--card-color) 4%, white);
        border-radius: 20px;
        padding: 28px 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        font-size: 0.9rem;
        font-weight: 800;
        color: #1f2937;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        z-index: 2;
        border: 1px solid color-mix(in srgb, var(--card-color) 12%, transparent);
        cursor: pointer;
    }

    .hero-icon-card:hover {
        transform: translateY(-12px) scale(1.05);
        background: color-mix(in srgb, var(--card-color) 10%, white);
        box-shadow: 0 25px 50px -12px color-mix(in srgb, var(--card-color) 25%, transparent);
        z-index: 10;
        border-color: var(--card-color);
    }

    .hero-icon-card i {
        font-size: 2.2rem;
        filter: drop-shadow(0 4px 10px color-mix(in srgb, var(--card-color) 30%, transparent));
        transition: transform 0.4s;
    }

    .hero-icon-card:hover i {
        transform: scale(1.15) rotate(8deg);
    }

    .hero-icon-card::after {
        content: '';
        position: absolute;
        inset: -1px;
        border-radius: 21px;
        background: linear-gradient(135deg, transparent, var(--card-color), transparent);
        opacity: 0;
        transition: opacity 0.4s;
        z-index: -1;
    }

    .hero-icon-card:hover::after {
        opacity: 0.15;
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
            style="color:#4ADE80;margin-right:8px;"></i>FOODLINK</a>
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
            <div class="reveal reveal-delay-1"
                style="display:inline-flex;align-items:center;gap:8px;background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;margin-bottom:24px;">
                <i class="fa-solid fa-circle-check"></i> Making Food Rescue Smarter
            </div>
            <h1 class="reveal reveal-delay-2">Eliminate <span style="color:#2E7D32;">Food Waste.</span><br>Feed
                Communities.</h1>
            <p class="reveal reveal-delay-3">A real-time logistics ecosystem connecting surplus food resources to those
                who need them most. We bridge
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
        <div class="hero-visual reveal animate-float">
            <div class="icon-grid">
                <svg class="grid-connections" viewBox="0 0 300 200" preserveAspectRatio="none">
                    <!-- Base Dash Lines -->
                    <path d="M50,50 L250,50" />
                    <path d="M50,150 L250,150" />
                    <path d="M50,50 L50,150" />
                    <path d="M150,50 L150,150" />
                    <path d="M250,50 L250,150" />

                    <!-- Flowing Glow Paths -->
                    <path class="glow-path" d="M50,50 L250,50" />
                    <path class="glow-path" d="M50,150 L250,150" />
                    <path class="glow-path" d="M50,50 L50,150" />
                    <path class="glow-path" d="M150,50 L150,150" />
                    <path class="glow-path" d="M250,50 L250,150" />
                </svg>

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
                    <div class="hero-icon-card" style="--card-color: <?= $color ?>;">
                        <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>;"></i>
                        <span><?= $label ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<div class="stats-row reveal">
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
<section class="features reveal">
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

<footer class="site-footer"
    style="background: linear-gradient(135deg, #0a2e1f 0%, #111827 100%); color: #fff; padding: 60px 20px; text-align: center; border-top: 4px solid #2E7D32; position: relative;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 30px; display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
            <a href="/pages/footer/about.php"
                style="color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: color 0.3s;"
                onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Our
                Story</a>
            <a href="/pages/footer/impact.php"
                style="color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: color 0.3s;"
                onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Impact</a>
            <a href="/pages/footer/donors-guide.php"
                style="color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: color 0.3s;"
                onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Donor
                Guide</a>
            <a href="/pages/footer/contact.php"
                style="color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: color 0.3s;"
                onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Contact</a>
            <a href="/pages/footer/privacy.php"
                style="color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 500; transition: color 0.3s;"
                onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Privacy</a>
        </div>
        <div style="opacity: 0.5; font-size: 0.85rem; margin-top: 20px;">
            &copy; <?= date('Y') ?> Food Rescue Platform. Empowering change through sustainability.
        </div>
    </div>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.count-up');
        const reveals = document.querySelectorAll('.reveal');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('count-up')) {
                        startCount(entry.target);
                    } else if (entry.target.classList.contains('reveal')) {
                        entry.target.classList.add('active');
                    }
                }
            });
        }, { threshold: 0.15 });

        function startCount(counter) {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const updateCount = () => {
                const increment = target / 40;
                if (count < target) {
                    count += increment;
                    counter.innerText = Math.ceil(count).toLocaleString();
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCount();
            observer.unobserve(counter);
        }

        counters.forEach(c => observer.observe(c));
        reveals.forEach(r => observer.observe(r));
    });
</script>

<?php include __DIR__ . '/includes/simple_footer.php'; ?>