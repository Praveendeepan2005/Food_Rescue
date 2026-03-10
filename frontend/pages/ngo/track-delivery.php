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

    /* Animated Workflow Styles */
    .flow-container {
        position: relative;
        padding: 40px 20px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        margin-top: 24px;
        overflow: hidden;
    }

    .flow-line {
        position: absolute;
        top: 60px;
        left: 10%;
        right: 10%;
        height: 4px;
        background: #E5E7EB;
        z-index: 1;
        border-radius: 2px;
    }

    .flow-progress {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: linear-gradient(to right, #8b5cf6, #2E7D32);
        width: 0%;
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 2;
    }

    .flow-steps {
        position: relative;
        z-index: 3;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0 5%;
    }

    .step-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 120px;
        text-align: center;
    }

    .node-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #E5E7EB;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #9CA3AF;
        margin-bottom: 12px;
        transition: all 0.5s ease;
        box-shadow: 0 0 0 4px #fff;
    }

    .step-node.done .node-circle {
        border-color: #2E7D32;
        color: #fff;
        background: #2E7D32;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }

    .step-node.active .node-circle {
        border-color: #8b5cf6;
        color: #8b5cf6;
        animation: pulseShadow 2s infinite;
    }

    @keyframes pulseShadow {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    .node-label {
        font-size: 0.75rem;
        font-weight: 800;
        color: #374151;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .node-sub {
        font-size: 0.65rem;
        color: #9CA3AF;
        margin-top: 4px;
        font-weight: 600;
    }

    .step-node.done .node-label { color: #111827; }
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
                        <?= htmlspecialchars($tracking['NGO_ADDRESS'] ?: $tracking['NGO_NAME']) ?></div>
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

        <?php 
            $curStatus = strtoupper($tracking['STATUS'] ?? 'PENDING');
            $statusMap = [
                'PENDING' => 1,
                'ASSIGNED' => 2,
                'PICKUP_STARTED' => 3,
                'ON_THE_WAY' => 3,
                'FOOD_PICKED' => 4,
                'PICKED_UP' => 4,
                'DELIVERING' => 5,
                'ON_DELIVERY' => 5,
                'DELIVERED' => 6,
                'COMPLETED' => 6
            ];
            $currentStep = $statusMap[$curStatus] ?? 1;
            if (empty($tracking['VOLUNTEER_NAME']) && $currentStep > 1) $currentStep = 1;
            
            $startCity = htmlspecialchars($tracking['CITY'] ?? 'Source');
            $endCity = htmlspecialchars($tracking['NGO_CITY'] ?? 'NGO');
        ?>

        <div class="flow-container">
            <div style="margin-bottom:24px;">
                <h3 style="font-size:1rem; font-weight:800; color:#111827; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-route" style="color:#8b5cf6;"></i> How NGO Tasks Reach People
                </h3>
                <p style="color:#6B7280; font-size:0.8rem;">Visualizing the journey from <?= $startCity ?> to <?= $endCity ?>.</p>
            </div>
            
            <div class="flow-line">
                <div class="flow-progress" id="flowBar"></div>
            </div>
            
            <div class="flow-steps">
                <!-- Step 1 -->
                <div class="step-node <?= $currentStep >= 1 ? 'done' : '' ?>">
                    <div class="node-circle"><i class="fa-solid fa-building"></i></div>
                    <div class="node-label">NGO CREATES<br>TASK</div>
                    <div class="node-sub">Step 1</div>
                </div>
                <!-- Step 2 -->
                <div class="step-node <?= $currentStep >= 2 ? 'done' : ($currentStep == 1 ? 'active' : '') ?>">
                    <div class="node-circle"><i class="fa-solid fa-bell"></i></div>
                    <div class="node-label">VOLUNTEER<br>NOTIFIED</div>
                    <div class="node-sub">Step 2</div>
                </div>
                <!-- Step 3 -->
                <div class="step-node <?= $currentStep >= 3 ? 'done' : ($currentStep == 2 ? 'active' : '') ?>">
                    <div class="node-circle"><i class="fa-solid fa-user-check"></i></div>
                    <div class="node-label">VOLUNTEER<br>ACCEPTS</div>
                    <div class="node-sub">Step 3</div>
                </div>
                <!-- Step 4 -->
                <div class="step-node <?= $currentStep >= 4 ? 'done' : ($currentStep == 3 ? 'active' : '') ?>">
                    <div class="node-circle"><i class="fa-solid fa-motorcycle"></i></div>
                    <div class="node-label">PICKUP IN<br>PROGRESS</div>
                    <div class="node-sub">Step 4</div>
                </div>
                <!-- Step 5 -->
                <div class="step-node <?= $currentStep >= 5 ? 'done' : ($currentStep == 4 ? 'active' : '') ?>">
                    <div class="node-circle"><i class="fa-solid fa-box-open"></i></div>
                    <div class="node-label">FOOD<br>COLLECTED</div>
                    <div class="node-sub">Step 5</div>
                </div>
                <!-- Step 6 -->
                <div class="step-node <?= $currentStep >= 6 ? 'done' : ($currentStep == 5 ? 'active' : '') ?>">
                    <div class="node-circle"><i class="fa-solid fa-handshake-angle"></i></div>
                    <div class="node-label">DELIVERED!</div>
                    <div class="node-sub">Done ✓</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Animate the flow bar based on progress
    document.addEventListener("DOMContentLoaded", function() {
        const step = <?= (int)$currentStep ?>;
        // Map 1-6 steps to 0-100% width on the 80% wide bar
        const perc = ((step - 1) / 5) * 100;
        setTimeout(() => {
            document.getElementById('flowBar').style.width = perc + '%';
        }, 800);
    });
</script>

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
        const pickupAddr = <?= json_encode($tracking['PICKUP_ADDRESS'] ?: $tracking['DONOR_NAME']) ?>;
        const dropAddr = <?= json_encode($tracking['NGO_ADDRESS'] ?: $tracking['NGO_NAME']) ?>;

        L.marker([dLat, dLng], { icon: donorIcon }).addTo(map).bindPopup(`<b>Pickup Location</b><br>${pickupAddr}`);
        L.marker([nLat, nLng], { icon: ngoIcon }).addTo(map).bindPopup(`<b>Delivery Location</b><br>${dropAddr}`);

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