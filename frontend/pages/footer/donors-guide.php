<?php
/**
 * donors-guide.php — Donor's Guide
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = "Donor's Guide | Food Rescue";
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 900px; margin: 0 auto; line-height: 1.8; color: #4B5563;">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 24px;">Donor's Guide</h1>
    <p style="font-size: 1.125rem; color: #6B7280; font-weight: 500; margin-bottom: 48px;">Share surplus food safely and
        efficiently with this guide for restaurant owners, event planners, and household donors.</p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 60px;">
        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdf4; color: #2E7D32; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">What Can I Donate?
            </h3>
            <ul style="padding-left: 20px; font-size: 0.95rem;">
                <li>Prepared foods from commercial kitchens.</li>
                <li>Unopened packaged goods and dry goods.</li>
                <li>Fresh produce and bread.</li>
                <li>Cooked food (must be chilled or frozen immediately).</li>
            </ul>
        </div>

        <div
            style="background: #fff; border: 1px solid #E5E7EB; border-radius: 20px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div
                style="width: 48px; height: 48px; border-radius: 12px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 20px;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 12px;">What Not to Donate?
            </h3>
            <ul style="padding-left: 20px; font-size: 0.95rem;">
                <li>Food that has been previously served.</li>
                <li>Partially consumed items.</li>
                <li>Food with strange odors or signs of spoilage.</li>
                <li>Items with broken seals or packaging.</li>
            </ul>
        </div>
    </div>

    <h2 style="font-size: 1.75rem; font-weight: 700; color: #2E7D32; margin-bottom: 24px;">A Step-by-Step Guide</h2>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div style="display: flex; gap: 24px; align-items: start;">
            <div
                style="width: 36px; height: 36px; border-radius: 50%; background: #2E7D32; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">
                1</div>
            <div>
                <h4 style="font-weight: 800; color: #111827; margin-bottom: 8px;">List Your Surplus</h4>
                <p>Use your dashboard to provide details about the type of food, quantity (in servings), and pick-up
                    location. Be accurate and prompt.</p>
            </div>
        </div>

        <div style="display: flex; gap: 24px; align-items: start;">
            <div
                style="width: 36px; height: 36px; border-radius: 50%; background: #2E7D32; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">
                2</div>
            <div>
                <h4 style="font-weight: 800; color: #111827; margin-bottom: 8px;">Pack and Preserve</h4>
                <p>Transfer the surplus food to single-use containers or take-out boxes. Ensure any cooked food is
                    properly cooled before packing.</p>
            </div>
        </div>

        <div style="display: flex; gap: 24px; align-items: start;">
            <div
                style="width: 36px; height: 36px; border-radius: 50%; background: #2E7D32; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">
                3</div>
            <div>
                <h4 style="font-weight: 800; color: #111827; margin-bottom: 8px;">Handover</h4>
                <p>Once a Volunteer or NGO arrives, provide them with any special handling or reheating instructions.
                    They'll handle the rest!</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>