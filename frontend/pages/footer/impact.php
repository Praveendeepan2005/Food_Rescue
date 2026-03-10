<?php
/**
 * impact.php — Our Impact
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'Our Impact | Food Rescue';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 1100px; margin: 0 auto; text-align: center;">
    <h1 style="font-size: 3rem; font-weight: 800; color: #111827; margin-bottom: 24px;">Our Collective Impact</h1>
    <p style="font-size: 1.25rem; color: #6B7280; max-width: 700px; margin: 0 auto 60px; line-height: 1.6;">Together,
        we're building a more sustainable and compassionate world by transforming surplus into opportunity.</p>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 80px;">
        <div
            style="background: #fff; padding: 40px; border-radius: 24px; border: 1px solid #E5E7EB; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transform: translateY(-10px);">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: #f0fdf4; color: #2E7D32; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 24px;">
                <i class="fa-solid fa-bowl-food"></i>
            </div>
            <h3 style="font-size: 2.5rem; font-weight: 800; color: #2E7D32; margin-bottom: 8px;">15,400+</h3>
            <p style="font-weight: 700; color: #111827; margin-bottom: 12px;">Meals Shared</p>
            <p style="font-size: 0.9rem; color: #6B7280;">Providing nutritional support to those experiencing food
                insecurity.</p>
        </div>

        <div
            style="background: #fff; padding: 40px; border-radius: 24px; border: 1px solid #E5E7EB; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transform: translateY(10px);">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 24px;">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </div>
            <h3 style="font-size: 2.5rem; font-weight: 800; color: #1d4ed8; margin-bottom: 8px;">4,200kg</h3>
            <p style="font-weight: 700; color: #111827; margin-bottom: 12px;">CO2 Emissions Saved</p>
            <p style="font-size: 0.9rem; color: #6B7280;">Diverting organic waste from landfills to reduce methane and
                greenhouse gases.</p>
        </div>

        <div
            style="background: #fff; padding: 40px; border-radius: 24px; border: 1px solid #E5E7EB; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transform: translateY(-10px);">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: #fff7ed; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 24px;">
                <i class="fa-solid fa-handshake-angle"></i>
            </div>
            <h3 style="font-size: 2.5rem; font-weight: 800; color: #f59e0b; margin-bottom: 8px;">1,200+</h3>
            <p style="font-weight: 700; color: #111827; margin-bottom: 12px;">Volunteers Active</p>
            <p style="font-size: 0.9rem; color: #6B7280;">Empowering heroes to deliver hope directly to their
                communities.</p>
        </div>
    </div>

    <div
        style="background: linear-gradient(135deg, #2E7D32 0%, #15803d 100%); padding: 60px; border-radius: 32px; color: #fff; text-align: left; overflow: hidden; position: relative;">
        <div style="max-width: 600px; position: relative; z-index: 2;">
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 20px;">Ready to help our impact grow?</h2>
            <p style="font-size: 1.1rem; opacity: 0.8; margin-bottom: 32px; line-height: 1.6;">Every donation counts.
                Every rescue matters. Join the movement today and help us build a zero-waste world.</p>
            <div style="display: flex; gap: 16px;">
                <a href="/register.php" class="btn btn-primary"
                    style="background: #fff; color: #2E7D32; padding: 14px 32px; font-weight: 700; border: none; border-radius: 12px;">Join
                    the Network</a>
                <a href="/login.php" class="btn btn-outline"
                    style="border: 1px solid rgba(255,255,255,0.4); color: #fff; padding: 14px 32px; font-weight: 700; border-radius: 12px;">Log
                    In</a>
            </div>
        </div>
        <i class="fa-solid fa-leaf"
            style="position: absolute; right: -40px; top: -40px; font-size: 20rem; color: rgba(255,255,255,0.05);"></i>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>