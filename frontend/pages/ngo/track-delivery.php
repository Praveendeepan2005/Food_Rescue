<?php
/**
 * pages/ngo/track-delivery.php — NGO Delivery Tracking Map
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'accepted';
$pageTitle = 'Monitor Delivery | Food Rescue';
$ngoId = $sessionUser['user_id'];

if (!isset($_GET['alert_id'])) {
    header('Location: /pages/ngo/accepted.php');
    exit();
}
$alertId = (int) $_GET['alert_id'];

$res = apiCall("/ngo/get_tracking_info.php?ngo_id={$ngoId}&alert_id={$alertId}", [], 'GET');
$tracking = $res['data']['tracking'] ?? null;

if (!$tracking) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Delivery not found or unauthorized.'];
    header('Location: /pages/ngo/accepted.php');
    exit();
}

include __DIR__ . '/../../includes/header.php';

$donorLat = $tracking['DONOR_LAT'] ?? 0;
$donorLng = $tracking['DONOR_LNG'] ?? 0;
$ngoLat = $tracking['NGO_LAT'] ?? 0;
$ngoLng = $tracking['NGO_LNG'] ?? 0;
$volLat = $tracking['VOL_LAT'] ?? 0;
$volLng = $tracking['VOL_LNG'] ?? 0;
$status = strtoupper($tracking['STATUS'] ?? 'PENDING');
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
        height: 450px;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        z-index: 10;
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

        <a href="/pages/ngo/accepted.php" class="btn-back">← Back to Accepted Donations</a>

        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Monitor Delivery: #
                    <?= $alertId ?>
                </h1>
                <p style="color:#6B7280;font-size:0.875rem;">Status: <span style="font-weight:700;color:#2E7D32;">
                        <?= htmlspecialchars($status) ?>
                    </span></p>
            </div>
            <div style="text-align:right;">
                <div class="track-meta">Assigned Volunteer</div>
                <div class="track-value">
                    <?= htmlspecialchars($tracking['VOLUNTEER_NAME'] ?? 'Waiting for Assignment') ?>
                    <?php if (!empty($tracking['VOL_PHONE'])): ?>
                        <div style="font-size:0.8rem; color:#6B7280;"><i class="fa-solid fa-phone"></i>
                            <?= htmlspecialchars($tracking['VOL_PHONE']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tracking-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <div class="track-meta">Pickup Location</div>
                    <div class="track-value"><i class="fa-solid fa-location-dot"
                            style="color:#f59e0b;margin-right:4px;"></i>
                        <?= htmlspecialchars($tracking['PICKUP_ADDRESS'] ?: $tracking['DONOR_NAME']) ?></div>
                </div>
                <div style="text-align:right;">
                    <div class="track-meta">Delivery Location</div>
                    <div class="track-value"><i class="fa-solid fa-map-pin" style="color:#2E7D32;margin-right:4px;"></i>
                        <?= htmlspecialchars($tracking['NGO_NAME']) ?></div>
                </div>
            </div>
            <hr style="border:none;border-top:1px solid #E5E7EB;margin:16px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0;">
                <div>
                    <div class="track-meta">Food Type</div>
                    <div class="track-value"><?= htmlspecialchars($tracking['FOOD_TYPE'] ?? '') ?>
                        (<?= htmlspecialchars($tracking['QUANTITY'] ?? '') ?>)</div>
                </div>
                <div style="text-align:right;">
                    <div class="track-meta">Route Distance & ETA</div>
                    <div class="track-value" id="distDisplay">Calculating...</div>
                </div>
            </div>
        </div>

        <div class="tracking-card">
            <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:16px;">Delivery Live Route</h3>
            <div id="trackingMap"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let dLat = <?= json_encode((float) $donorLat) ?>;
        let dLng = <?= json_encode((float) $donorLng) ?>;
        let nLat = <?= json_encode((float) $ngoLat) ?>;
        let nLng = <?= json_encode((float) $ngoLng) ?>;
        const status = <?= json_encode($status) ?>;

        // Smart fallbacks in case DB drops coordinates, prevents rendering in the ocean [0,0]!
        if (dLat === 0) { dLat = 12.9716; dLng = 77.5946; } // Defaulting to somewhere central
        if (nLat === 0) { nLat = dLat - 0.02; nLng = dLng + 0.02; }

        let vLat = <?= json_encode((float) $volLat) ?>;
        let vLng = <?= json_encode((float) $volLng) ?>;
        if (vLat === 0) { vLat = dLat; vLng = dLng; } // fallback to donor originally

        const map = L.map('trackingMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        const donorIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        const ngoIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        const volIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        // Add pins for donor and NGO
        L.marker([dLat, dLng], { icon: donorIcon }).addTo(map).bindPopup("<b>Donor</b><br>Pickup Point");
        L.marker([nLat, nLng], { icon: ngoIcon }).addTo(map).bindPopup("<b>Your NGO</b><br>Drop Point");

        // Route polyline
        L.polyline([[dLat, dLng], [nLat, nLng]], { color: '#2E7D32', weight: 4, dashArray: '5, 10' }).addTo(map);
        map.fitBounds(L.latLngBounds([[dLat, dLng], [nLat, nLng]]), { padding: [50, 50] });

        // Plot and Animate the Volunteer Vehicle
        const hasVolunteer = <?= json_encode(!empty($tracking['VOLUNTEER_NAME'])) ?>;

        if (hasVolunteer) {
            let volMarker;
            if (status === 'DELIVERED' || status === 'COMPLETED') {
                volMarker = L.marker([nLat, nLng], { icon: volIcon }).addTo(map).bindPopup("<b>Vehicle Arrived</b><br>Delivery successful!");
            } else if (status === 'PENDING' || status === 'ASSIGNED' || status === 'ACCEPTED') {
                volMarker = L.marker([vLat, vLng], { icon: volIcon }).addTo(map).bindPopup("<b>Volunteer Assigned</b><br>Heading to pickup now!");
            } else {
                // On the way - SIMULATE VEHICLE MOVEMENT 
                volMarker = L.marker([dLat, dLng], { icon: volIcon }).addTo(map).bindPopup("<b>Live Vehicle Tracking</b><br>En route to destination!");

                let progress = 0;
                const totalFrames = 300; // About 5 seconds of animation

                function animateVehicle() {
                    progress += 1;
                    if (progress <= totalFrames) {
                        const ratio = progress / totalFrames;
                        const currentLat = dLat + (nLat - dLat) * ratio;
                        const currentLng = dLng + (nLng - dLng) * ratio;

                        volMarker.setLatLng([currentLat, currentLng]);

                        if (progress % 30 === 0) { // Keep popup tracking position
                            map.panTo([currentLat, currentLng], { animate: true });
                        }

                        requestAnimationFrame(animateVehicle);
                    } else {
                        volMarker.bindPopup("<b>Arrived</b><br>Volunteer has reached the location!").openPopup();
                    }
                }

                setTimeout(animateVehicle, 800); // Start moving slightly after map loads
            }
        }

        // Haversine Distance Calculation (approximate)
        function calcDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // km
            const pLat = (lat2 - lat1) * Math.PI / 180;
            const pLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(pLat / 2) * Math.sin(pLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(pLon / 2) * Math.sin(pLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        const distance = calcDistance(dLat, dLng, nLat, nLng);
        const estMins = Math.round((distance / 30) * 60) + 5;
        document.getElementById('distDisplay').innerText = distance.toFixed(1) + ' km (~' + estMins + ' mins)';
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>