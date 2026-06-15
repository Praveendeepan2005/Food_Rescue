<?php
/**
 * pages/volunteer/delivery-tracking.php — Volunteer Delivery Tracking & Map
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'assignments';
$pageTitle = 'Track Delivery | Food Rescue';
$userId = $sessionUser['user_id'];

if (!isset($_GET['claim_id'])) {
    header('Location: /pages/volunteer/assignments.php');
    exit();
}
$claimId = (int) $_GET['claim_id'];

// Actions Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $alertId = (int) ($_POST['alert_id'] ?? 0);
    $endpoint = '';
    $payload = ['volunteer_id' => $userId, 'claim_id' => $claimId, 'alert_id' => $alertId];

    if ($action === 'start_pickup')
        $endpoint = '/volunteer/start_pickup.php';
    if ($action === 'mark_pickedup')
        $endpoint = '/volunteer/update_pickup_status.php';
    if ($action === 'start_delivery')
        $endpoint = '/volunteer/start_delivery.php';
    if ($action === 'mark_delivered')
        $endpoint = '/volunteer/mark_delivered.php';

    if ($endpoint) {
        $res = apiCall($endpoint, $payload);
        $_SESSION['flash'] = !empty($res['success'])
            ? ['type' => 'success', 'msg' => 'Status updated successfully!']
            : ['type' => 'error', 'msg' => $res['message'] ?? 'Action failed.'];
    }
    header('Location: /pages/volunteer/delivery-tracking.php?claim_id=' . $claimId);
    exit();
}

$res = apiCall("/volunteer/get_delivery_detail.php?volunteer_id={$userId}&claim_id={$claimId}", [], 'GET');
$delivery = $res['data']['delivery'] ?? null;

if (!$delivery) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Delivery not found or unauthorized.'];
    header('Location: /pages/volunteer/assignments.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';

// Safe extraction
$orpLat = $delivery['ORPHANAGE_LAT'] ?? 0;
$orpLng = $delivery['ORPHANAGE_LNG'] ?? 0;
$donorLat = $delivery['DONOR_LAT'] ?? 0;
$donorLng = $delivery['DONOR_LNG'] ?? 0;
$status = strtoupper($delivery['ALERT_STATUS'] ?? $delivery['CLAIM_STATUS'] ?? '');

$actionLabel = '';
$actionValue = '';
if ($status === 'ACTIVE' || $status === 'ASSIGNED') {
    $actionLabel = 'Start Pickup';
    $actionValue = 'start_pickup';
} else if ($status === 'PICKUP_STARTED' || $status === 'ON_THE_WAY') {
    $actionLabel = 'Food Picked Up';
    $actionValue = 'mark_pickedup';
} else if ($status === 'FOOD_PICKED' || $status === 'PICKED_UP') {
    $actionLabel = 'Start Delivery';
    $actionValue = 'start_delivery';
} else if ($status === 'DELIVERING' || $status === 'ON_DELIVERY') {
    $actionLabel = 'Mark Delivered';
    $actionValue = 'mark_delivered';
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
    .tracking-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #E5E7EB;
    }

    .track-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .track-meta {
        font-size: 0.85rem;
        color: #6B7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .track-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
    }

    #trackingMap {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        z-index: 10;
    }

    .btn-action {
        background-color: #2E7D32;
        color: #fff;
        border: none;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        text-decoration: none;
        text-align: center;
    }

    .btn-action:hover {
        background-color: #1b5e20;
    }

    .btn-back {
        display: inline-block;
        margin-bottom: 16px;
        color: #2E7D32;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-back:hover {
        text-decoration: underline;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <a href="/pages/volunteer/assignments.php" class="btn-back">← Back to Assignments</a>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Live Tracking: #
                    <?= $delivery['ALERT_ID'] ?>
                </h1>
                <p style="color:#6B7280;font-size:0.875rem;">Status: <span style="font-weight:700;color:#2E7D32;">
                        <?= htmlspecialchars($status) ?>
                    </span></p>
            </div>

            <?php if ($actionLabel): ?>
                <form method="POST" style="margin:0; min-width: 200px;">
                    <input type="hidden" name="action" value="<?= $actionValue ?>">
                    <input type="hidden" name="alert_id" value="<?= $delivery['ALERT_ID'] ?>">
                    <button type="submit" class="btn-action">
                        <i class="fa-solid fa-arrow-right"></i>
                        <?= $actionLabel ?>
                    </button>
                </form>
            <?php else: ?>
                <div
                    style="background:#f0fdf4; color:#166534; padding:12px 24px; border-radius:6px; font-weight:700; border:1px solid #bbf7d0;">
                    <i class="fa-solid fa-check-circle"></i> Delivery Completed
                </div>
            <?php endif; ?>
        </div>

        <div class="tracking-card">
            <div class="track-info-row">
                <div>
                    <div class="track-meta">Pickup Location</div>
                    <div class="track-value"><i class="fa-solid fa-location-dot"
                            style="color:#f59e0b;margin-right:4px;"></i>
                        <?= htmlspecialchars($delivery['PICKUP_ADDRESS'] ?: $delivery['DONOR_NAME']) ?>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="track-meta">Recipient Location</div>
                    <div class="track-value"><i class="fa-solid fa-house-chimney-window"
                            style="color:#1d4ed8;margin-right:4px;"></i>
                        <?= htmlspecialchars($delivery['ORPHANAGE_NAME'] ?? $delivery['NGO_NAME']) ?>
                    </div>
                </div>
            </div>
            <hr style="border:none;border-top:1px solid #E5E7EB;margin:16px 0;">
            <div class="track-info-row" style="margin-bottom:0;">
                <div>
                    <div class="track-meta">Food Type</div>
                    <div class="track-value">
                        <?= htmlspecialchars($delivery['FOOD_TYPE']) ?> (
                        <?= htmlspecialchars($delivery['QUANTITY']) ?>)
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="track-meta">Approximate Distance</div>
                    <div class="track-value" id="distDisplay">Calculating...</div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:12px;">Map Route</h3>
        <div id="trackingMap"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let dLat = <?= json_encode((float) $donorLat) ?>;
        let dLng = <?= json_encode((float) $donorLng) ?>;
        let nLat = <?= json_encode((float) $orpLat) ?>;
        let nLng = <?= json_encode((float) $orpLng) ?>;
        const status = <?= json_encode($status) ?>;

        // Fallback to prevent gray rendering at Null Island [0,0]
        if (dLat === 0) { dLat = 11.0168; dLng = 76.9558; }
        if (nLat === 0) { nLat = dLat - 0.02; nLng = dLng + 0.02; }

        // Leaflet setup
        const map = L.map('trackingMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        // Marker icons
        const donorIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
        });
        const ngoIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
        });
        const volIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
        });

        const donorMarker = L.marker([dLat, dLng], { icon: donorIcon }).addTo(map).bindPopup("<b>Pickup: Donor</b>");
        const ngoMarker = L.marker([nLat, nLng], { icon: ngoIcon }).addTo(map).bindPopup("<b>Delivery: Orphanage</b>");

        // Full route line (static background) removed, using dynamic lines

        let volMarker = L.marker([dLat, dLng], { icon: volIcon }).addTo(map).bindPopup("<b>Connecting GPS...</b>");
        let activeRoute1, activeRoute2;

        function updateRoute(userLat, userLng) {
            if (activeRoute1) map.removeLayer(activeRoute1);
            if (activeRoute2) map.removeLayer(activeRoute2);

            const isPickedUp = ['FOOD_PICKED', 'PICKED_UP', 'DELIVERING', 'ON_DELIVERY', 'COMPLETED', 'DELIVERED'].includes(status);
            const targetLat = isPickedUp ? nLat : dLat;
            const targetLng = isPickedUp ? nLng : dLng;

            if (isPickedUp) {
                activeRoute1 = L.polyline([[userLat, userLng], [nLat, nLng]], {
                    color: '#2E7D32', weight: 5, opacity: 0.9, dashArray: '1, 10'
                }).addTo(map);

                activeRoute2 = L.polyline([[dLat, dLng], [userLat, userLng]], {
                    color: '#9CA3AF', weight: 4, opacity: 0.6, dashArray: '5, 5'
                }).addTo(map);

                volMarker.setPopupContent("<b>Heading to Orphanage</b>");
            } else {
                activeRoute1 = L.polyline([[userLat, userLng], [dLat, dLng]], {
                    color: '#8b5cf6', weight: 5, opacity: 0.9, dashArray: '1, 10'
                }).addTo(map);

                activeRoute2 = L.polyline([[dLat, dLng], [nLat, nLng]], {
                    color: '#f59e0b', weight: 4, opacity: 0.6, dashArray: '5, 5'
                }).addTo(map);

                volMarker.setPopupContent("<b>Heading to Donor</b>");
            }

            volMarker.setLatLng([userLat, userLng]);

            const bounds = L.latLngBounds([[userLat, userLng], [dLat, dLng], [nLat, nLng]]);
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });

            // Update Distance display
            const dist = calcDistance(userLat, userLng, targetLat, targetLng);
            const mins = Math.round((dist / 30) * 60) + 2;
            document.getElementById('distDisplay').innerText = dist.toFixed(1) + ' km to Target (~' + mins + ' mins)';
        }

        // Live Geolocation
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition((pos) => {
                updateRoute(pos.coords.latitude, pos.coords.longitude);
            }, (err) => {
                console.warn("GPS Access Denied, using simulation");
                updateRoute(dLat - 0.005, dLng - 0.005); // Simulated start
            }, { enableHighAccuracy: true });
        } else {
            updateRoute(dLat - 0.005, dLng - 0.005);
        }

        // Haversine Distance Calculation
        function calcDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>