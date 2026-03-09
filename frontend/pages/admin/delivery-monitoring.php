<?php
/**
 * pages/admin/delivery-monitoring.php — Admin Delivery Tracking Map
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'ADMIN') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'deliveries';
$pageTitle = 'Delivery Monitoring | Food Rescue';

$res = apiCall('/admin/get_all_deliveries.php', [], 'GET');
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

    #adminMap {
        width: 100%;
        height: 500px;
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
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>

        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Delivery Monitoring</h1>
                <p style="color:#6B7280;font-size:0.875rem;">Command center for tracking all active platform logistics.
                </p>
            </div>
        </div>

        <div class="tracking-card">
            <h3 style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:16px;">Global Tracking Map</h3>
            <div id="adminMap"></div>
        </div>

        <div class="table-card">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;color:#111827;">Active Deliveries</h3>
            <?php if (empty($deliveries)): ?>
                <div style="padding:40px;text-align:center;color:#9CA3AF;">No active deliveries in the network.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Donor Name</th>
                            <th>NGO Name</th>
                            <th>Volunteer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $d):
                            $status = strtoupper($d['STATUS'] ?? $d['FA_STATUS'] ?? 'PENDING');
                            $dLat = (float) ($d['DONOR_LAT'] ?? 0);
                            $dLng = (float) ($d['DONOR_LNG'] ?? 0);
                            $nLat = (float) ($d['NGO_LAT'] ?? 0);
                            $nLng = (float) ($d['NGO_LNG'] ?? 0);
                            $vLat = (float) ($d['VOL_LAT'] ?? 0);
                            $vLng = (float) ($d['VOL_LNG'] ?? 0);
                            ?>
                            <tr class="tr-clickable"
                                onclick="highlightRoute(<?= $d['ALERT_ID'] ?>, <?= $dLat ?>, <?= $dLng ?>, <?= $nLat ?>, <?= $nLng ?>, <?= $vLat ?>, <?= $vLng ?>, this)">
                                <td><strong>#
                                        <?= $d['ALERT_ID'] ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($d['DONOR_NAME'] ?? '—') ?>
                                </td>
                                <td><i class="fa-solid fa-building-ngo" style="color:#2E7D32;margin-right:4px;"></i>
                                    <?= htmlspecialchars($d['NGO_NAME'] ?? '—') ?>
                                </td>
                                <td>
                                    <?php if ($d['VOLUNTEER_NAME']): ?>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div
                                                style="width:24px;height:24px;border-radius:50%;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.6rem;">
                                                <?= strtoupper(substr($d['VOLUNTEER_NAME'], 0, 1)) ?>
                                            </div>
                                            <span>
                                                <?= htmlspecialchars($d['VOLUNTEER_NAME']) ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#f59e0b;font-weight:600;font-size:0.85rem;">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-<?= strtolower($status) ?>">
                                        <?= $status ?>
                                    </span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    let map, globalSet = L.featureGroup();
    let trackingLines = {};
    let trackingMarkers = {};

    function initMap() {
        map = L.map('adminMap');
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

        // Loop through all data to display all points globally first
        <?php foreach ($deliveries as $d):
            $alertId = $d['ALERT_ID'];
            $status = strtoupper($d['STATUS'] ?? $d['FA_STATUS'] ?? 'PENDING');
            $dLat = (float) $d['DONOR_LAT'];
            $dLng = (float) $d['DONOR_LNG'];
            $nLat = (float) $d['NGO_LAT'];
            $nLng = (float) $d['NGO_LNG'];
            $vLat = (float) $d['VOL_LAT'];
            $vLng = (float) $d['VOL_LNG'];

            // Smart Fallbacks
            if (empty($dLat) || $dLat == 0) {
                $dLat = 12.9716;
                $dLng = 77.5946;
            }
            if (empty($nLat) || $nLat == 0) {
                $nLat = $dLat - 0.02;
                $nLng = $dLng + 0.02;
            }
            if (empty($vLat) || $vLat == 0) {
                $vLat = $dLat;
                $vLng = $dLng;
            }

            echo sprintf("
                var aid = %d;
                trackingMarkers[aid] = [];
                
                var dM = L.marker([%f, %f], {icon: donorIcon}).bindPopup('<b>Donor</b> (Delivery #'+aid+')');
                var nM = L.marker([%f, %f], {icon: ngoIcon}).bindPopup('<b>NGO</b> (Delivery #'+aid+')');
                
                globalSet.addLayer(dM);
                globalSet.addLayer(nM);
                trackingMarkers[aid].push(dM, nM);

                var route = L.polyline([[%f,%f], [%f,%f]], {color: '#9CA3AF', weight: 4, opacity: 0.5, dashArray: '5, 10'}).addTo(map);
                trackingLines[aid] = route;
            ",
                $alertId,
                $dLat,
                $dLng,
                $nLat,
                $nLng,
                $dLat,
                $dLng,
                $nLat,
                $nLng
            );

            if (!empty($d['VOLUNTEER_NAME'])):
                $statusJS = json_encode($status);
                echo "
                (function() {
                    let dL = {$dLat}, dLn = {$dLng}, nL = {$nLat}, nLn = {$nLng};
                    let st = {$statusJS};
                    let vM = L.marker([{$vLat}, {$vLng}], {icon: volIcon}).bindPopup('<b>Volunteer</b> (Delivery #'+aid+')');
                    globalSet.addLayer(vM);
                    trackingMarkers[aid].push(vM);

                    if (st !== 'DELIVERED' && st !== 'COMPLETED' && st !== 'PENDING' && st !== 'ASSIGNED' && st !== 'ACCEPTED') {
                        let prog = 0;
                        function anim() {
                            prog += 1;
                            if (prog <= 300) {
                                let cLat = dL + (nL - dL) * (prog/300);
                                let cLng = dLn + (nLn - dLn) * (prog/300);
                                vM.setLatLng([cLat, cLng]);
                                requestAnimationFrame(anim);
                            }
                        }
                        setTimeout(anim, 1000);
                    }
                })();
                ";
            endif;
        endforeach; ?>

        map.addLayer(globalSet);
        if (globalSet.getLayers().length > 0) {
            map.fitBounds(globalSet.getBounds(), { padding: [30, 30] });
        }
    }

    function highlightRoute(alertId, dLat, dLng, nLat, nLng, vLat, vLng, row) {
        // Reset row highlights
        document.querySelectorAll('.tr-clickable').forEach(el => el.classList.remove('route-active'));
        row.classList.add('route-active');

        // Reset all line styles
        Object.values(trackingLines).forEach(line => line.setStyle({ color: '#9CA3AF', weight: 3, opacity: 0.5, dashArray: '' }));

        // Highlight selected route
        if (trackingLines[alertId]) {
            trackingLines[alertId].setStyle({ color: '#2E7D32', weight: 5, opacity: 1, dashArray: '5, 10' });
            trackingLines[alertId].bringToFront();

            let bounds = [];
            if (dLat != 0) bounds.push([dLat, dLng]);
            if (nLat != 0) bounds.push([nLat, nLng]);
            if (vLat != 0) bounds.push([vLat, vLng]);

            if (bounds.length > 0) {
                map.flyToBounds(L.latLngBounds(bounds), { padding: [50, 50], maxZoom: 16 });
            }
        }
    }

    document.addEventListener("DOMContentLoaded", initMap);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>