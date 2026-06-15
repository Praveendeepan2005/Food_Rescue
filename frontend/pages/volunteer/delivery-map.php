<?php
/**
 * pages/volunteer/delivery-map.php — Volunteer Delivery Tracking Map (All active)
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'VOLUNTEER') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'delivery-map';
$pageTitle = 'Live Tracking Map | Food Link';
$userId = $sessionUser['user_id'];

$res = apiCall("/volunteer/get_live_tracking.php?volunteer_id={$userId}", [], 'GET');
$deliveries = $res['data']['deliveries'] ?? [];

include __DIR__ . '/../../includes/header.php';
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

    #deliveryMap {
        width: 100%;
        height: 550px;
        border-radius: 12px;
        border: 1px solid #E5E7EB;
        z-index: 10;
    }

    .tr-clickable {
        cursor: pointer;
        transition: background 0.2s;
    }

    .tr-clickable:hover {
        background-color: #f1f5f9;
    }

    .route-active {
        background-color: #e0f2fe !important;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;color:#111827;">Live Delivery Tracking</h1>
                <p style="color:#6B7280;font-size:0.875rem;">Command center for your active pickup and delivery
                    missions.</p>
            </div>
        </div>

        <!-- 1. Active Assignments Table First -->
        <div class="table-card" style="margin-bottom: 28px;">
            <div
                style="padding: 20px 24px; border-bottom: 1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0;">Active Assignments</h3>
                <span
                    style="font-size: 0.75rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Click
                    a row to focus on map</span>
            </div>
            <?php if (empty($deliveries)): ?>
                <div style="padding:60px;text-align:center;color:#9CA3AF;">
                    <i class="fa-solid fa-truck-fast"
                        style="font-size:2.5rem;display:block;margin-bottom:14px;color:#D1D5DB;"></i>
                    No active deliveries at the moment.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Food Name</th>
                            <th>Target Recipient</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $d):
                            $status = strtoupper($d['STATUS'] ?? 'PENDING');
                            $dLat = (float) ($d['DONOR_LAT'] ?? 0);
                            $dLng = (float) ($d['DONOR_LNG'] ?? 0);
                            $nLat = (float) ($d['ORPHANAGE_LAT'] ?? 0);
                            $nLng = (float) ($d['ORPHANAGE_LNG'] ?? 0);
                            ?>
                            <tr class="tr-clickable"
                                onclick="focusRoute(<?= $d['ALERT_ID'] ?>, <?= $dLat ?>, <?= $dLng ?>, <?= $nLat ?>, <?= $nLng ?>, this)">
                                <td><strong>#<?= $d['ALERT_ID'] ?></strong></td>
                                <td><?= htmlspecialchars($d['FOOD_TYPE'] ?? '—') ?></td>
                                <td><i class="fa-solid fa-house-chimney-window" style="color:#1d4ed8;margin-right:4px;"></i>
                                    <?= htmlspecialchars($d['ORPHANAGE_NAME'] ?? $d['NGO_NAME'] ?? '—') ?>
                                </td>
                                <td><span class="badge badge-<?= strtolower($status) ?>"><?= $status ?></span></td>
                                <td>
                                    <a href="/pages/volunteer/pickup-details.php?claim_id=<?= $d['CLAIM_ID'] ?>&alert_id=<?= $d['ALERT_ID'] ?>"
                                        class="btn-xs btn-outline-sm">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 2. Live Tracking Map Below -->
        <div class="tracking-card">
            <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:16px;">
                <i class="fa-solid fa-map-location-dot" style="color:#2E7D32; margin-right:8px;"></i>Live Mission Route
            </h3>
            <div id="deliveryMap"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    let map, globalSet = L.featureGroup();
    let trackingLines = {};
    let trackingMarkers = {};

    function initMap() {
        map = L.map('deliveryMap').setView([11.0168, 76.9558], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        // Force a resize check after a small delay to fix the 'gray box' issue
        setTimeout(() => { map.invalidateSize(); }, 500);

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
        const finishIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-grey.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [20, 32], iconAnchor: [10, 32], popupAnchor: [1, -30]
        });

        const activeDeliveries = <?= json_encode($deliveries) ?>;
        let userPos = { lat: 11.0168, lng: 76.9558 };

        function drawDeliveries() {
            globalSet.clearLayers();
            activeDeliveries.forEach(d => {
                const aid = d.ALERT_ID;
                const status = strtoupper(d.STATUS || 'PENDING');
                const isFinished = ['COMPLETED', 'DELIVERED'].includes(status);
                const isPickedUp = ['FOOD_PICKED', 'PICKED_UP', 'DELIVERING', 'ON_DELIVERY'].includes(status);

                const dLat = parseFloat(d.DONOR_LAT || 12.9716);
                const dLng = parseFloat(d.DONOR_LNG || 77.5946);
                const nLat = parseFloat(d.ORPHANAGE_LAT || dLat + 0.02);
                const nLng = parseFloat(d.ORPHANAGE_LNG || dLng + 0.02);

                if (isFinished) {
                    const finishM = L.marker([nLat, nLng], { icon: finishIcon }).bindPopup('<b>Task Finished</b><br>Delivery #' + aid);
                    globalSet.addLayer(finishM);
                    L.polyline([[dLat, dLng], [nLat, nLng]], { color: '#E5E7EB', weight: 2 }).addTo(globalSet);
                } else {
                    const dM = L.marker([dLat, dLng], { icon: donorIcon }).bindPopup('<b>Donor</b> (#' + aid + ')');
                    const nM = L.marker([nLat, nLng], { icon: ngoIcon }).bindPopup('<b>Orphanage</b> (#' + aid + ')');
                    const vM = L.marker([userPos.lat, userPos.lng], { icon: volIcon }).bindPopup('<b>Your Position</b>');

                    globalSet.addLayer(dM);
                    globalSet.addLayer(nM);
                    globalSet.addLayer(vM);

                    let mainLine, secondLine;

                    if (isPickedUp) {
                        mainLine = L.polyline([[userPos.lat, userPos.lng], [nLat, nLng]], {
                            color: '#2E7D32', weight: 5, opacity: 0.8, dashArray: '1, 10'
                        }).addTo(globalSet);
                        secondLine = L.polyline([[dLat, dLng], [userPos.lat, userPos.lng]], {
                            color: '#9CA3AF', weight: 3, opacity: 0.6, dashArray: '5, 5'
                        }).addTo(globalSet);
                    } else {
                        mainLine = L.polyline([[userPos.lat, userPos.lng], [dLat, dLng]], {
                            color: '#8b5cf6', weight: 5, opacity: 0.8, dashArray: '1, 10'
                        }).addTo(globalSet);
                        secondLine = L.polyline([[dLat, dLng], [nLat, nLng]], {
                            color: '#f59e0b', weight: 3, opacity: 0.6, dashArray: '5, 5'
                        }).addTo(globalSet);
                    }
                    
                    trackingLines[aid] = L.layerGroup([mainLine, secondLine]).addTo(globalSet);
                }
            });

            if (globalSet.getLayers().length > 0) {
                map.fitBounds(globalSet.getBounds(), { padding: [40, 40] });
            }
        }

        function strtoupper(str) { return str ? str.toString().toUpperCase() : ''; }

        // Start initial draw
        drawDeliveries();

        // Live Geolocation
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition((pos) => {
                userPos.lat = pos.coords.latitude;
                userPos.lng = pos.coords.longitude;
                drawDeliveries();
            }, (err) => {
                console.warn("GPS Access Denied");
                drawDeliveries();
            }, { enableHighAccuracy: true });
        } else {
            drawDeliveries();
        }

        map.addLayer(globalSet);
    }

    function focusRoute(alertId, dLat, dLng, nLat, nLng, row) {
        document.querySelectorAll('.tr-clickable').forEach(el => el.classList.remove('route-active'));
        row.classList.add('route-active');

        Object.values(trackingLines).forEach(item => {
            if (item.setStyle) item.setStyle({ color: '#9CA3AF', weight: 4, opacity: 0.5, dashArray: '' });
            else if (item.eachLayer) item.eachLayer(layer => layer.setStyle({ color: '#9CA3AF', weight: 4, opacity: 0.5, dashArray: '' }));
        });

        if (trackingLines[alertId]) {
            let selected = trackingLines[alertId];
            if (selected.setStyle) {
                selected.setStyle({ color: '#2E7D32', weight: 6, opacity: 1, dashArray: '5, 10' });
                if (selected.bringToFront) selected.bringToFront();
            } else if (selected.eachLayer) {
                selected.eachLayer(layer => {
                    layer.setStyle({ color: '#2E7D32', weight: 6, opacity: 1, dashArray: '5, 10' });
                    if (layer.bringToFront) layer.bringToFront();
                });
            }

            let bounds = [];
            if (dLat != 0) bounds.push([dLat, dLng]);
            if (nLat != 0) bounds.push([nLat, nLng]);

            if (bounds.length > 0) {
                map.flyToBounds(L.latLngBounds(bounds), { padding: [60, 60], maxZoom: 15 });
            }
        }
    }

    document.addEventListener("DOMContentLoaded", initMap);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>