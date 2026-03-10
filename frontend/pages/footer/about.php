<?php
/**
 * about.php — About Us
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'About Us | Food Link';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 800px; margin: 0 auto;">
    <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 24px; color: #111827;">Ending Hunger, One Rescue at a
        Time</h1>
    <p style="font-size: 1.125rem; line-height: 1.8; color: #4B5563; margin-bottom: 32px;">
        Food Link is a technology-driven platform designed to tackle the global crisis of food waste. We bridge the
        gap between surplus food from restaurants, events, and households and the NGOs that serve those in need.
    </p>

    <div
        style="background: #F0FDF4; border-radius: 16px; padding: 32px; border: 1px solid #BBF7D0; margin-bottom: 40px;">
        <h3 style="color: #166534; font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">Our Mission</h3>
        <p style="color: #15803d; line-height: 1.6;">To create a seamless, transparent, and efficient ecosystem where no
            edible food goes to waste, and no person goes hungry.</p>
    </div>

    <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 20px;">How We Started</h2>
    <p style="line-height: 1.7; color: #4B5563; margin-bottom: 24px;">
        Inspired by the amount of perfectly good food discarded at local events, we built this platform to make
        "rescuing" food as easy as ordering it. By connecting donors with local volunteers and verified NGOs, we've
        created a rapid-response network for surplus food redistribution.
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 40px;">
        <div style="padding: 24px; background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;">
            <i class="fa-solid fa-users" style="font-size: 1.5rem; color: #2E7D32; margin-bottom: 12px;"></i>
            <h4 style="font-weight: 700; margin-bottom: 8px;">For Donors</h4>
            <p style="font-size: 0.875rem; color: #6B7280;">Reduce your environmental footprint and help your community
                with just a few clicks.</p>
        </div>
        <div style="padding: 24px; background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;">
            <i class="fa-solid fa-handshake-angle" style="font-size: 1.5rem; color: #8b5cf6; margin-bottom: 12px;"></i>
            <h4 style="font-weight: 700; margin-bottom: 8px;">For Volunteers</h4>
            <p style="font-size: 0.875rem; color: #6B7280;">Be the bridge. Join our fleet of "Rescuers" and help
                transport food to those who need it most.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>