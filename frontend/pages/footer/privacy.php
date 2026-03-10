<?php
/**
 * privacy.php — Privacy Policy
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'Privacy Policy | Food Rescue';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 800px; margin: 0 auto; line-height: 1.8; color: #4B5563;">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 32px;">Privacy Policy</h1>

    <p style="font-weight: 700; color: #111827; margin-bottom: 24px;">Last Updated: March 2026</p>

    <p style="margin-bottom: 32px;">At Food Rescue, we are committed to protecting your personal information and your
        right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards
        to your personal information, please contact us at privacy@foodrescue.org.</p>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">1.
        Information We Collect</h2>
    <p style="margin-bottom: 16px;">We collect personal information that you voluntarily provide to us when you register
        on our platform, express interest in obtaining information about us or our services, or otherwise when you
        contact us.</p>
    <p style="margin-bottom: 16px;">The personal information we collect may include:</p>
    <ul style="padding-left: 20px; margin-bottom: 32px;">
        <li>Personal Identifiable Information (Name, email address, phone number).</li>
        <li>Account Credentials (Username and password).</li>
        <li>Location Data (GPS coordinates and street address for pickups and deliveries).</li>
        <li>User Role Information (Donor, NGO, or Volunteer details).</li>
    </ul>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">2. How We
        Use Your Information</h2>
    <p style="margin-bottom: 16px;">We use personal information collected via our platform for a variety of business
        purposes described below:</p>
    <ul style="padding-left: 20px; margin-bottom: 32px;">
        <li>To facilitate account creation and logon process.</li>
        <li>To facilitate food donation and rescue delivery services.</li>
        <li>To enable communication between Donors, NGOs, and Volunteers.</li>
        <li>To enable real-time tracking of food deliveries.</li>
        <li>To send administrative information to you.</li>
    </ul>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">3. Will Your
        Information Be Shared?</h2>
    <p style="margin-bottom: 32px;">We only share information with your consent, to comply with laws, to provide you
        with services, to protect your rights, or to fulfill business obligations. Specifically, your location and
        contact details will be shared with the relevant NGO/Volunteer once a food rescue alert is claimed to facilitate
        the pickup.</p>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>