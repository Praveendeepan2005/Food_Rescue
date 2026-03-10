<?php
/**
 * terms.php — Terms of Service
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
$pageTitle = 'Terms of Service | Food Rescue';
include __DIR__ . '/../../includes/header.php';
?>
<div class="container" style="padding: 80px 20px; max-width: 800px; margin: 0 auto; line-height: 1.8; color: #4B5563;">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 32px;">Terms of Service</h1>

    <p style="font-weight: 700; color: #111827; margin-bottom: 24px;">Effective Date: March 2026</p>

    <p style="margin-bottom: 32px;">Welcome to Food Rescue. These Terms of Service ("Terms") govern your use of the Food
        Rescue web platform (the "Platform"). By accessing or using the Platform, you agree to be bound by these Terms.
    </p>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">1.
        Eligibility</h2>
    <p style="margin-bottom: 32px;">To use the Platform, you must be at least 18 years old and capable of forming a
        binding contract. You must also provide accurate and complete information during registration.</p>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">2. User
        Conduct</h2>
    <p style="margin-bottom: 16px;">By using the platform, you agree to:</p>
    <ul style="padding-left: 20px; margin-bottom: 32px;">
        <li>Provide accurate and complete information about donated food.</li>
        <li>Ensure that all donated food is safe for consumption and has been handled according to food safety
            guidelines.</li>
        <li>Maintain a professional demeanor while interacting with other users.</li>
        <li>Not use the Platform for any illegal or unauthorized purpose.</li>
    </ul>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">3.
        Disclaimers</h2>
    <p style="margin-bottom: 32px;">Food Rescue is a platform to facilitate food donation and rescue. We do not provide
        the food, transport, or NGOs ourselves. We are not responsible for the quality, safety, or legality of the
        donated food or services provided by users. Participation in the rescue network is at your own risk.</p>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 40px; margin-bottom: 16px;">4.
        Modifications to the Service</h2>
    <p style="margin-bottom: 32px;">We reserve the right to modify or discontinue the Platform at any time, with or
        without notice. We will not be liable for any modification, suspension, or discontinuance of the Platform.</p>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>