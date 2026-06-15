<?php
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'orphanages';
$pageTitle = 'Recipient Locations | Food Link';
$userId = $sessionUser['user_id'];

// Handle Form Submission (Add Orphanage)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $payload = [
            'ngo_id' => $userId,
            'name' => $_POST['name'],
            'contact_person' => $_POST['contact'],
            'phone_number' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'latitude' => $_POST['lat'] ?? 0,
            'longitude' => $_POST['lng'] ?? 0
        ];
        $res = apiCall('/ngo/add_orphanage.php', $payload);
        if ($res['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Orphanage added successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $res['message']];
        }
    } elseif ($_POST['action'] === 'edit') {
        $payload = [
            'ngo_id' => $userId,
            'orphanage_id' => $_POST['orphanage_id'],
            'name' => $_POST['name'],
            'contact_person' => $_POST['contact'],
            'phone_number' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'latitude' => $_POST['lat'] ?? 0,
            'longitude' => $_POST['lng'] ?? 0
        ];
        $res = apiCall('/ngo/update_orphanage.php', $payload);
        if ($res['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Orphanage updated successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $res['message']];
        }
    } elseif ($_POST['action'] === 'delete') {
        $payload = ['ngo_id' => $userId, 'orphanage_id' => $_POST['orphanage_id']];
        $res = apiCall('/ngo/delete_orphanage.php', $payload);
        if ($res['success']) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Orphanage removed successfully!'];
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch Orphanages
$data = apiCall("/ngo/get_orphanages.php?ngo_id={$userId}", [], 'GET');
$orphanages = $data['data']['orphanages'] ?? [];

include __DIR__ . '/../../includes/header.php';
?>

<style>
    .orp-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 14px;
        padding: 40px;
        color: #fff;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .orp-hero h1 { font-size: 2rem; font-weight: 800; margin: 0; }
    .orp-hero p { opacity: 0.8; margin-top: 8px; }

    .orp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    .orp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
        position: relative;
    }
    .orp-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border-color: #3b82f6;
    }

    .orp-name { font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .orp-detail { display: flex; align-items: center; gap: 10px; color: #4b5563; font-size: 0.9rem; margin-bottom: 8px; }
    .orp-detail i { color: #9ca3af; width: 16px; text-align: center; }

    .btn-add {
        background: #fff;
        color: #1e3a8a;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
        border: none;
        cursor: pointer;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        inset: 0;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal.open { display: flex; }
    .modal-content {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        padding: 32px;
        position: relative;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div class="orp-hero">
            <h1><i class="fa-solid fa-house-chimney-window"></i> Recipient Locations</h1>
            <p>Manage orphanages and distribution centers where food is delivered.</p>
            <button class="btn-add" onclick="document.getElementById('addModal').classList.add('open')">
                <i class="fa-solid fa-plus"></i> Add New Location
            </button>
        </div>

        <div style="margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:1.25rem; font-weight:700; color:#111827;">Registered Recipient Centers</h2>
            <span style="color:#6b7280; font-size:0.875rem;"><?= count($orphanages) ?> Locations found</span>
        </div>

        <?php if (empty($orphanages)): ?>
            <div style="background:#fff; border-radius:12px; padding:60px; text-align:center; border:1px solid #e5e7eb;">
                <i class="fa-solid fa-map-location-dot" style="font-size:3rem; color:#d1d5db; margin-bottom:16px;"></i>
                <h3 style="color:#374151;">No locations added yet.</h3>
                <p style="color:#6b7280; margin-bottom:24px;">Start by adding an orphanage or community center.</p>
            </div>
        <?php else: ?>
            <div class="orp-grid">
                <?php foreach ($orphanages as $o): ?>
                    <div class="orp-card">
                        <div class="orp-name"><?= htmlspecialchars($o['ORPHANAGE_NAME']) ?></div>
                        <div class="orp-detail">
                            <i class="fa-solid fa-user"></i>
                            <span><?= htmlspecialchars($o['CONTACT_PERSON'] ?: '—') ?></span>
                        </div>
                        <div class="orp-detail">
                            <i class="fa-solid fa-phone"></i>
                            <span><?= htmlspecialchars($o['PHONE_NUMBER'] ?: '—') ?></span>
                        </div>
                        <div class="orp-detail">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= htmlspecialchars($o['ADDRESS']) ?>, <?= htmlspecialchars($o['CITY']) ?></span>
                        </div>
                        
                        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid #f3f4f6; display:flex; gap:10px;">
                            <form method="POST" style="margin:0; flex:1;" onsubmit="return confirm('Remove this location?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="orphanage_id" value="<?= $o['ORPHANAGE_ID'] ?>">
                                <button type="submit" class="btn btn-outline" style="width:100%; color:#ef4444; border-color:#fecaca;">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </button>
                            </form>
                            <button type="button" class="btn btn-primary" style="flex:1; background:#3b82f6;" 
                                    onclick='openEditModal(<?= json_encode($o) ?>)'>
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Orphanage Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h2 style="margin-bottom:24px;">Add Recipient Location</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Orphanage/Center Name *</label>
                <input type="text" name="name" required placeholder="e.g. Hope Children's Home">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact" placeholder="Name">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="9876543210">
                </div>
            </div>
            <div class="form-group">
                <label>Street Address *</label>
                <input type="text" name="address" required placeholder="Full address">
            </div>
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" required placeholder="e.g. Coimbatore">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Latitude (Optional)</label>
                    <input type="text" name="lat" placeholder="11.0168">
                </div>
                <div class="form-group">
                    <label>Longitude (Optional)</label>
                    <input type="text" name="lng" placeholder="76.9558">
                </div>
            </div>
            <div style="display:flex; gap:12px; margin-top:24px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('open')" style="flex:1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:2; background:#3b82f6;">Save Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Orphanage Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h2 style="margin-bottom:24px;">Edit Recipient Location</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="orphanage_id" id="edit_orp_id">
            <div class="form-group">
                <label>Orphanage/Center Name *</label>
                <input type="text" name="name" id="edit_orp_name" required>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact" id="edit_orp_contact">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="edit_orp_phone">
                </div>
            </div>
            <div class="form-group">
                <label>Street Address *</label>
                <input type="text" name="address" id="edit_orp_address" required>
            </div>
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" id="edit_orp_city" required>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Latitude (Optional)</label>
                    <input type="text" name="lat" id="edit_orp_lat">
                </div>
                <div class="form-group">
                    <label>Longitude (Optional)</label>
                    <input type="text" name="lng" id="edit_orp_lng">
                </div>
            </div>
            <div style="display:flex; gap:12px; margin-top:24px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').classList.remove('open')" style="flex:1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:2; background:#3b82f6;">Update Location</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(orp) {
        document.getElementById('edit_orp_id').value = orp.ORPHANAGE_ID;
        document.getElementById('edit_orp_name').value = orp.ORPHANAGE_NAME;
        document.getElementById('edit_orp_contact').value = orp.CONTACT_PERSON;
        document.getElementById('edit_orp_phone').value = orp.PHONE_NUMBER;
        document.getElementById('edit_orp_address').value = orp.ADDRESS;
        document.getElementById('edit_orp_city').value = orp.CITY;
        document.getElementById('edit_orp_lat').value = orp.LATITUDE || '';
        document.getElementById('edit_orp_lng').value = orp.LONGITUDE || '';
        document.getElementById('editModal').classList.add('open');
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('addModal')) {
            document.getElementById('addModal').classList.remove('open');
        }
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').classList.remove('open');
        }
    }
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
