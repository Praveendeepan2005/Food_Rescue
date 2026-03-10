<?php
/**
 * contact.php — Contact Support
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'Contact Us | Food Rescue';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container"
    style="padding: 80px 20px; max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start;">
    <div>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 24px;">Get in Touch</h1>
        <p style="font-size: 1.125rem; line-height: 1.6; color: #6B7280; margin-bottom: 40px;">Have questions about
            donating surplus food or joining our volunteer network? We're here to help.</p>

        <div style="display: flex; gap: 24px; flex-direction: column;">
            <div style="display: flex; gap: 16px; align-items: start;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdf4; color: #2E7D32; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <h4 style="font-weight: 700; color: #111827; margin-bottom: 4px;">Email Support</h4>
                    <p style="color: #6B7280; font-size: 0.9rem;">support@foodrescue.org</p>
                    <p style="color: #6B7280; font-size: 0.9rem;">Available M-F, 9am - 6pm</p>
                </div>
            </div>

            <div style="display: flex; gap: 16px; align-items: start;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <div>
                    <h4 style="font-weight: 700; color: #111827; margin-bottom: 4px;">Emergency Pickup</h4>
                    <p style="color: #6B7280; font-size: 0.9rem;">+1 (234) 567-890</p>
                    <p style="color: #6B7280; font-size: 0.9rem;">Available 24/7 for large food surplus.</p>
                </div>
            </div>

            <div style="display: flex; gap: 16px; align-items: start;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: #fff7ed; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div>
                    <h4 style="font-weight: 700; color: #111827; margin-bottom: 4px;">Headquarters</h4>
                    <p style="color: #6B7280; font-size: 0.9rem;">123 Rescue Way, Zero Waste City</p>
                    <p style="color: #6B7280; font-size: 0.9rem;">Z-45678, Sustainable Dist.</p>
                </div>
            </div>
        </div>
    </div>

    <div
        style="background: #fff; padding: 40px; border-radius: 20px; border: 1px solid #E5E7EB; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 24px;">Send us a message</h3>
        <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label
                    style="font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;">Your
                    Name</label>
                <input type="text" placeholder="John Doe" required
                    style="width: 100%; border-radius: 10px; border: 1px solid #D1D5DB; padding: 12px 16px;">
            </div>
            <div>
                <label
                    style="font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;">Email
                    Address</label>
                <input type="email" placeholder="john@example.com" required
                    style="width: 100%; border-radius: 10px; border: 1px solid #D1D5DB; padding: 12px 16px;">
            </div>
            <div>
                <label
                    style="font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;">Query
                    Type</label>
                <select
                    style="width: 100%; border-radius: 10px; border: 1px solid #D1D5DB; padding: 12px 16px; background: #fff;">
                    <option>Donor Support</option>
                    <option>NGO Partnership</option>
                    <option>Volunteer Verification</option>
                    <option>Technical Issue</option>
                    <option>Other</option>
                </select>
            </div>
            <div>
                <label
                    style="font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 8px; display: block;">Your
                    Message</label>
                <textarea rows="4" placeholder="How can we help?" required
                    style="width: 100%; border-radius: 10px; border: 1px solid #D1D5DB; padding: 12px 16px;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"
                style="width: 100%; padding: 14px; font-weight: 700; font-size: 1rem; border-radius: 10px;">
                <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Send Message
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>