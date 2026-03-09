<?php
/**
 * pages/donor/donate.php — Create a new food donation
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'DONOR') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'donate';
$pageTitle = 'Donate Food | Food Rescue';
$error = $success = '';

// City List - Hardcoded Fallback for Tamil Nadu (ensures page never breaks)
$tnCities = ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Tiruppur', 'Vellore', 'Erode', 'Thoothukudi', 'Dindigul', 'Thanjavur', 'Kanyakumari', 'Kanchipuram', 'Ooty'];

// Try to fetch live cities from API (optional enhancement)
$cityRes = apiCall('/donor/get_cities.php', [], 'GET');
if (!empty($cityRes['data']['cities'])) {
    $tnCities = $cityRes['data']['cities'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert HTML5 datetime-local (YYYY-MM-DDTHH:MM) to DB format (YYYY-MM-DD HH:MM:SS)
    $propLocal = trim($_POST['preparation_time'] ?? '');
    $prepFormatted = $propLocal ? str_replace('T', ' ', $propLocal) . ':00' : '';

    $expLocal = trim($_POST['expiry_time'] ?? '');
    $expFormatted = $expLocal ? str_replace('T', ' ', $expLocal) . ':00' : '';

    $payload = [
        'donor_id' => $sessionUser['user_id'],
        'food_name' => trim($_POST['food_name'] ?? ''),
        'category' => trim($_POST['category'] ?? 'VEG'),
        'quantity' => (int) ($_POST['quantity'] ?? 0),
        'preparation_time' => $prepFormatted,
        'expiry_time' => $expFormatted,
        'pickup_address' => trim($_POST['pickup_address'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
        'special_instructions' => trim($_POST['special_instructions'] ?? ''),
        'city' => trim($_POST['city'] ?? $sessionUser['city'] ?? 'Chennai'),
        'priority' => 'NORMAL',
        'selection_mode' => trim($_POST['selection_mode'] ?? 'AUTO'),
        'target_ngo_id' => (int) ($_POST['target_ngo_id'] ?? 0),
    ];

    if (empty($payload['food_name']) || $payload['quantity'] < 1 || empty($payload['pickup_address']) || empty($payload['expiry_time']) || empty($payload['contact_number']) || empty($payload['city'])) {
        $error = 'Please fill in all required fields.';
    } else {
        $res = apiCall('/donor/create_donation.php', $payload);
        if (!empty($res['success'])) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Donation created successfully! NGOs will be notified.'];
            header('Location: /pages/donor/my_donations.php');
            exit();
        } else {
            $error = $res['message'] ?? 'Failed to create donation.';
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<style>
    .donate-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 32px;
        align-items: flex-start;
    }

    @media(max-width: 900px) {
        .donate-layout {
            grid-template-columns: 1fr;
        }
    }

    .flow-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 32px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 24px;
    }

    .flow-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 32px;
        text-align: center;
    }

    .flow-steps {
        display: flex;
        flex-direction: column;
        gap: 32px;
        position: relative;
        padding-left: 12px;
    }

    .flow-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        bottom: 20px;
        left: 36px;
        width: 2px;
        background: #E5E7EB;
        z-index: 0;
    }

    .pulse-line {
        position: absolute;
        top: 20px;
        left: 36px;
        width: 2px;
        height: 0;
        background: #2E7D32;
        z-index: 0;
        animation: flowDown 3s infinite;
        transform-origin: top;
    }

    @keyframes flowDown {
        0% {
            height: 0;
            opacity: 1;
        }

        50% {
            height: 100%;
            opacity: 1;
        }

        100% {
            height: 100%;
            opacity: 0;
        }
    }

    .flow-step {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        z-index: 1;
        opacity: 0;
        transform: translateX(-20px);
        animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .flow-step:nth-child(2) {
        animation-delay: 0.1s;
    }

    .flow-step:nth-child(3) {
        animation-delay: 0.4s;
    }

    .flow-step:nth-child(4) {
        animation-delay: 0.7s;
    }

    .flow-step:nth-child(5) {
        animation-delay: 1.0s;
    }

    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #2E7D32;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #2E7D32;
        box-shadow: 0 0 0 6px #fff;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .flow-step:hover .step-icon {
        background: #2E7D32;
        color: #fff;
        transform: scale(1.1);
    }

    .step-content h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 4px 0;
    }

    .step-content p {
        font-size: 0.875rem;
        color: #6B7280;
        margin: 0;
        line-height: 1.4;
    }

    @keyframes slideInRight {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .ngo-modal {
        background: #fff;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        padding: 32px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .modal-overlay.active .ngo-modal {
        transform: translateY(0);
    }

    .modal-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .modal-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .modal-header p {
        color: #6B7280;
        font-size: 0.95rem;
    }

    .selection-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }

    .select-card {
        border: 2px solid #E5E7EB;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }

    .select-card:hover {
        border-color: #2E7D32;
        background: #F0FDF4;
    }

    .select-card.active {
        border-color: #2E7D32;
        background: #F0FDF4;
        box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
    }

    .select-card .icon {
        font-size: 2rem;
        color: #2E7D32;
        margin-bottom: 12px;
    }

    .select-card h4 {
        margin: 0 0 4px 0;
        color: #111827;
    }

    .select-card p {
        font-size: 0.8rem;
        color: #6B7280;
        margin: 0;
        line-height: 1.2;
    }

    #specificNgoArea {
        display: none;
        margin-top: 16px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content" style="padding: 32px;">
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.75rem;font-weight:700;color:#111827;">Submit Food Donation</h1>
            <p style="color:#6B7280;font-size:0.9rem;margin-top:6px;">Fill in the details below to safely pass surplus
                food into the network.</p>
        </div>

        <?php if ($error): ?>
            <div
                style="background:#FEE2E2;border-left:4px solid #D32F2F;color:#B91C1C;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:0.875rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="donate-layout">
            <form id="donationForm" method="POST" class="glass-card" style="padding:32px;">
                <input type="hidden" name="selection_mode" id="selectionMode" value="AUTO">
                <input type="hidden" name="target_ngo_id" id="targetNgoId" value="">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div class="form-group">
                        <label>Food Name <span style="color:#D32F2F">*</span></label>
                        <input type="text" name="food_name" required placeholder="e.g. Rice, Biryani"
                            value="<?= htmlspecialchars($_POST['food_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Food Category <span style="color:#D32F2F">*</span></label>
                        <select name="category" required>
                            <option value="VEG" <?= (($_POST['category'] ?? '') === 'VEG') ? 'selected' : '' ?>>Vegetarian
                            </option>
                            <option value="NON_VEG" <?= (($_POST['category'] ?? '') === 'NON_VEG') ? 'selected' : '' ?>>
                                Non-Vegetarian</option>
                            <option value="PACKED" <?= (($_POST['category'] ?? '') === 'PACKED') ? 'selected' : '' ?>>
                                Packed / Sealed</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div class="form-group">
                        <label>Quantity <span style="color:#D32F2F">*</span></label>
                        <input type="number" name="quantity" required min="1"
                            value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Number <span style="color:#D32F2F">*</span></label>
                        <input type="text" name="contact_number" required
                            value="<?= htmlspecialchars($_POST['contact_number'] ?? $sessionUser['phone'] ?? '') ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div class="form-group">
                        <label>Preparation Time</label>
                        <input type="datetime-local" name="preparation_time"
                            value="<?= htmlspecialchars($_POST['preparation_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Expiry Time <span style="color:#D32F2F">*</span></label>
                        <input type="datetime-local" name="expiry_time" required
                            value="<?= htmlspecialchars($_POST['expiry_time'] ?? '') ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">
                    <div class="form-group">
                        <label>Pickup Address <span style="color:#D32F2F">*</span></label>
                        <input type="text" name="pickup_address" required
                            value="<?= htmlspecialchars($_POST['pickup_address'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>City <span style="color:#D32F2F">*</span></label>
                        <select name="city" required>
                            <option value="">Choose City...</option>
                            <?php foreach ($tnCities as $cityName): ?>
                                <option value="<?= htmlspecialchars($cityName) ?>" <?= (strcasecmp($_POST['city'] ?? $sessionUser['city'] ?? '', $cityName) === 0) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cityName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:32px;">
                    <label>Special Instructions</label>
                    <textarea name="special_instructions"
                        rows="3"><?= htmlspecialchars($_POST['special_instructions'] ?? '') ?></textarea>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="button" id="submitBtn" class="btn btn-primary" style="padding:12px 32px;">Submit
                        Donation</button>
                    <a href="/pages/donor/my_donations.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>

            <div class="flow-card">
                <div class="flow-title">The Rescue Journey</div>
                <div class="flow-steps">
                    <div class="pulse-line"></div>
                    <div class="flow-step">
                        <div class="step-icon"><i class="fa-solid fa-file-pen"></i></div>
                        <div class="step-content">
                            <h4>You Submit</h4>
                            <p>Details entered.</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-icon"><i class="fa-solid fa-building"></i></div>
                        <div class="step-content">
                            <h4>NGO Assigned</h4>
                            <p>Matched with NGOs.</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-icon"><i class="fa-solid fa-person-running"></i></div>
                        <div class="step-content">
                            <h4>Pickup</h4>
                            <p>Volunteer arrives.</p>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="step-icon" style="background:#2E7D32;color:#fff;"><i
                                class="fa-solid fa-people-group"></i></div>
                        <div class="step-content">
                            <h4>Delivered</h4>
                            <p>Mission complete.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ngoModal" class="modal-overlay">
    <div class="ngo-modal">
        <div class="modal-header">
            <h2>Assign Donation</h2>
            <p>How would you like to notify NGOs?</p>
        </div>
        <div class="selection-grid">
            <div class="select-card active" onclick="setMode('ANY')">
                <div class="icon"><i class="fa-solid fa-globe"></i></div>
                <h4>To Any NGO</h4>
                <p>Broadcast to all.</p>
            </div>
            <div class="select-card" onclick="setMode('SPECIFIC')">
                <div class="icon"><i class="fa-solid fa-building-circle-check"></i></div>
                <h4>Specific NGO</h4>
                <p>Assign directly.</p>
            </div>
        </div>
        <div id="specificNgoArea" class="form-group">
            <label>Select NGO</label>
            <select id="ngoListSelect">
                <option value="">Loading...</option>
            </select>
        </div>
        <div style="display:flex; gap:12px; margin-top:24px;">
            <button type="button" class="btn btn-primary" style="flex:1;" onclick="confirmDonation()">Confirm</button>
            <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal()">Back</button>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('ngoModal');
    const form = document.getElementById('donationForm');
    const specificArea = document.getElementById('specificNgoArea');
    const ngoSelect = document.getElementById('ngoListSelect');
    let currentMode = 'ANY';

    document.getElementById('submitBtn').addEventListener('click', () => {
        if (!form.checkValidity()) { alert('Please fill all required fields.'); return; }
        modal.classList.add('active');
    });

    function setMode(mode) {
        currentMode = mode;
        const cards = document.querySelectorAll('.select-card');
        cards[0].classList.toggle('active', mode === 'ANY');
        cards[1].classList.toggle('active', mode === 'SPECIFIC');
        specificArea.style.display = (mode === 'SPECIFIC') ? 'block' : 'none';
        if (mode === 'SPECIFIC') fetchNGOs();
    }

    async function fetchNGOs() {
        try {
            const res = await fetch('/food-rescue-api/donor/get_active_ngos.php');
            const data = await res.json();
            if (data.success) {
                ngoSelect.innerHTML = '<option value="">-- Choose NGO --</option>';
                data.data.ngos.forEach(n => ngoSelect.innerHTML += `<option value="${n.id}">${n.name}</option>`);
            }
        } catch (e) { }
    }

    function confirmDonation() {
        if (currentMode === 'SPECIFIC' && !ngoSelect.value) { alert('Select an NGO.'); return; }
        document.getElementById('selectionMode').value = currentMode;
        document.getElementById('targetNgoId').value = ngoSelect.value;
        form.submit();
    }

    function closeModal() { modal.classList.remove('active'); }
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>