<?php
/**
 * volunteer-code.php — Volunteer Code of Conduct
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'Volunteer Code of Conduct | Food Rescue';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 900px; margin: 0 auto; line-height: 1.8; color: #4B5563;">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 24px;">Volunteer Code of Conduct</h1>
    <p style="font-size: 1.125rem; color: #6B7280; font-weight: 500; margin-bottom: 48px;">Our "Rescuers" are the
        heartbeat of Food Rescue. By joining our network, you commit to these standards of professionalism and care.</p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 60px;">
        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">Punctuality</h3>
            <p style="font-size: 0.95rem;">Food has a limited shelf life. Once you claim an assignment, proceed to the
                pickup location as soon as possible. If you're delayed, update the status in the app immediately.</p>
        </div>

        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-temperature-empty"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">Food Safety</h3>
            <p style="font-size: 0.95rem;">Handle food containers gently and keep them flat during transport. Ensure
                your vehicle is clean. Don't leave food unattended in direct sunlight or high-heat areas.</p>
        </div>

        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdf4; color: #2E7D32; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">Professionalism</h3>
            <p style="font-size: 0.95rem;">You represent the Food Rescue community. Be polite and respectful to Donors
                and NGO staff. Respect the privacy and property of everyone you interact with.</p>
        </div>

        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">Zero Tampering</h3>
            <p style="font-size: 0.95rem;">Never open, sample, or tamper with the food once listed. Our system of trust
                depends on food arriving in exactly the same condition it was donated.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>