<?php
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';
if ($sessionUser['role'] !== 'NGO') {
    header('Location: /dashboard.php');
    exit();
}
$activePage = 'volunteers';
$pageTitle = 'Volunteers | Food Rescue';
$userId = $sessionUser['user_id'];
$data = apiCall("/ngo/get_volunteers.php?ngo_id={$userId}", [], 'GET');
$volunteers = $data['data']['volunteers'] ?? [];
$total = count($volunteers);
$active = count(array_filter($volunteers, fn($v) => strtoupper($v['STATUS'] ?? '') === 'ACTIVE'));
include __DIR__ . '/../../includes/header.php';
?>
<style>
    /* ── Layout ────────────────────────────────────────────────── */
    .vol-page {
        padding: 0;
    }

    /* ── Hero Banner ───────────────────────────────────────────── */
    .vol-hero {
        background: linear-gradient(135deg, #1a3a2a 0%, #2E7D32 60%, #1b5e20 100%);
        border-radius: 14px;
        padding: 36px 36px 48px;
        margin-bottom: -24px;
        position: relative;
        overflow: hidden;
    }

    .vol-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .vol-hero h1 {
        color: #fff;
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .vol-hero p {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.9rem;
        margin: 0;
    }

    /* Hero stat pills */
    .hero-pills {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .hero-pill {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 99px;
        padding: 8px 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-size: 0.88rem;
        font-weight: 600;
        animation: fadeUp 0.6s ease both;
    }

    .hero-pill:nth-child(1) {
        animation-delay: 0.1s;
    }

    .hero-pill:nth-child(2) {
        animation-delay: 0.25s;
    }

    .hero-pill:nth-child(3) {
        animation-delay: 0.4s;
    }

    .hero-pill .pill-icon {
        font-size: 1.1rem;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── Pipeline (Task Flow) Section ──────────────────────────── */
    .flow-section {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 28px 32px;
        margin: 36px 0 28px;
    }

    .flow-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .flow-section-sub {
        font-size: 0.78rem;
        color: #6B7280;
        margin-bottom: 24px;
    }

    .task-pipeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        gap: 0;
    }

    .task-pipeline::before {
        content: '';
        position: absolute;
        top: 28px;
        left: 30px;
        right: 30px;
        height: 2px;
        background: #E5E7EB;
        z-index: 0;
    }

    .task-pipeline-fill {
        position: absolute;
        top: 28px;
        left: 30px;
        height: 2px;
        width: 0;
        background: linear-gradient(90deg, #8b5cf6, #2E7D32);
        z-index: 1;
        animation: pipelineExpand 1.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s forwards;
    }

    @keyframes pipelineExpand {
        to {
            width: calc(100% - 60px);
        }
    }

    .task-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        z-index: 2;
        flex: 1;
        opacity: 0;
        transform: translateY(20px);
        animation: stepIn 0.5s ease both;
    }

    .task-step:nth-child(2) {
        animation-delay: 0.2s;
    }

    .task-step:nth-child(3) {
        animation-delay: 0.5s;
    }

    .task-step:nth-child(4) {
        animation-delay: 0.8s;
    }

    .task-step:nth-child(5) {
        animation-delay: 1.1s;
    }

    .task-step:nth-child(6) {
        animation-delay: 1.4s;
    }

    @keyframes stepIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tstep-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        border: 2px solid;
        position: relative;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
    }

    .tstep-icon:hover {
        transform: scale(1.15);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    /* Ripple for active steps */
    .tstep-icon.active::after {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 2px solid currentColor;
        animation: ripple 1.8s ease-out infinite;
        opacity: 0;
    }

    @keyframes ripple {
        0% {
            transform: scale(0.8);
            opacity: 0.7;
        }

        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    .tstep-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-align: center;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        max-width: 70px;
        line-height: 1.3;
    }

    .tstep-time {
        font-size: 0.65rem;
        color: #9CA3AF;
        font-weight: 500;
        text-align: center;
    }

    /* Colour utilities */
    .c-purple {
        color: #7c3aed;
        border-color: rgba(124, 58, 237, 0.4);
    }

    .c-blue {
        color: #1d4ed8;
        border-color: rgba(29, 78, 216, 0.4);
    }

    .c-amber {
        color: #d97706;
        border-color: rgba(217, 119, 6, 0.4);
    }

    .c-orange {
        color: #ea580c;
        border-color: rgba(234, 88, 12, 0.4);
    }

    .c-green {
        color: #2E7D32;
        border-color: rgba(46, 125, 50, 0.4);
    }

    /* ── Volunteer Cards ────────────────────────────────────────── */
    .vol-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
        margin-top: 0;
    }

    .vol-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        opacity: 0;
        transform: translateY(24px);
        animation: cardIn 0.5s ease both;
    }

    .vol-card:hover {
        border-color: #2E7D32;
        box-shadow: 0 8px 24px rgba(46, 125, 50, 0.12);
        transform: translateY(-4px);
    }

    .vol-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #8b5cf6, #2E7D32);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .vol-card:hover::before {
        transform: scaleX(1);
    }

    @keyframes cardIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .vol-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 14px;
        position: relative;
    }

    .vol-active-dot {
        width: 12px;
        height: 12px;
        background: #22c55e;
        border: 2px solid #fff;
        border-radius: 50%;
        position: absolute;
        bottom: 0;
        right: 0;
        animation: dotPulse 2s ease infinite;
    }

    @keyframes dotPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
        }

        50% {
            box-shadow: 0 0 0 5px rgba(34, 197, 94, 0);
        }
    }

    .vol-name {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .vol-email {
        font-size: 0.78rem;
        color: #6B7280;
        margin-bottom: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vol-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        border-top: 1px solid #F3F4F6;
        padding-top: 12px;
    }

    .vol-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: #374151;
    }

    .vol-meta-item i {
        color: #9CA3AF;
        width: 14px;
        text-align: center;
    }

    .vol-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    /* ── Modal ──────────────────────────────────────────────────── */
    .vol-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.65);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .vol-modal-overlay.open {
        display: flex;
    }

    .vol-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 460px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        transform: scale(0.9) translateY(20px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .vol-modal-overlay.open .vol-modal {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .modal-top {
        background: linear-gradient(135deg, #6d28d9, #8b5cf6);
        padding: 32px;
        text-align: center;
        position: relative;
    }

    .modal-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        margin: 0 auto 12px;
    }

    .modal-name {
        color: #fff;
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .modal-email {
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.875rem;
    }

    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #fff;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .modal-body {
        padding: 28px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .modal-stat {
        background: #F8F9FA;
        border-radius: 8px;
        padding: 14px;
    }

    .modal-stat label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #9CA3AF;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .modal-stat p {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .modal-footer {
        padding: 20px 28px;
        border-top: 1px solid #F3F4F6;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>

<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content vol-page" style="padding: 32px;">
    <?php include __DIR__ . '/../../includes/flash.php'; ?>

    <!-- Hero Banner -->
    <div class="vol-hero">
        <h1><i class="fa-solid fa-people-group" style="margin-right:10px;"></i>Volunteers</h1>
        <p>Manage and track your NGO's volunteer network</p>
        <div class="hero-pills">
            <div class="hero-pill">
                <span class="pill-icon">👥</span>
                <?= $total ?> Total Registered
            </div>
            <div class="hero-pill">
                <span class="pill-icon" style="color:#4ade80;">✅</span>
                <?= $active ?> Currently Active
            </div>
            <div class="hero-pill">
                <span class="pill-icon">📦</span>
                Ready for Assignments
            </div>
        </div>
    </div>

    <!-- Task Flow Pipeline -->
    <div class="flow-section">
        <div class="flow-section-title">
            <i class="fa-solid fa-diagram-project" style="color:#8b5cf6;"></i>
            How NGO Tasks Reach People
        </div>
        <div class="flow-section-sub">Animated view of the complete food rescue delivery workflow</div>

        <div class="task-pipeline">
            <div class="task-pipeline-fill"></div>

            <div class="task-step">
                <div class="tstep-icon c-purple active">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="tstep-label">NGO Creates Task</div>
                <div class="tstep-time">Step 1</div>
            </div>

            <div class="task-step">
                <div class="tstep-icon c-blue active">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="tstep-label">Volunteer Notified</div>
                <div class="tstep-time">Step 2</div>
            </div>

            <div class="task-step">
                <div class="tstep-icon c-amber">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="tstep-label">Volunteer Accepts</div>
                <div class="tstep-time">Step 3</div>
            </div>

            <div class="task-step">
                <div class="tstep-icon c-orange">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <div class="tstep-label">Pickup in Progress</div>
                <div class="tstep-time">Step 4</div>
            </div>

            <div class="task-step">
                <div class="tstep-icon c-orange">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class="tstep-label">Food Collected</div>
                <div class="tstep-time">Step 5</div>
            </div>

            <div class="task-step">
                <div class="tstep-icon c-green">
                    <i class="fa-solid fa-hands-holding-child"></i>
                </div>
                <div class="tstep-label">Delivered!</div>
                <div class="tstep-time">Done ✓</div>
            </div>
        </div>
    </div>

    <!-- Section Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; color:#111827;">Your Volunteer Network</h2>
            <p style="font-size:0.8rem; color:#6B7280; margin-top:2px;">Click a card to see full details</p>
        </div>
        <a href="/pages/ngo/accepted.php" class="btn btn-primary" style="font-size:0.82rem;">
            <i class="fa-solid fa-plus"></i> Assign a Delivery
        </a>
    </div>

    <!-- Volunteer Cards Grid -->
    <?php if (empty($volunteers)): ?>
        <div
            style="background:#fff; border-radius:12px; padding:80px; text-align:center; color:#9CA3AF; border:1px solid #E5E7EB;">
            <i class="fa-solid fa-people-group"
                style="font-size:3rem; display:block; margin-bottom:16px; color:#D1D5DB;"></i>
            <p style="font-size:1rem; font-weight:600;">No volunteers yet</p>
            <p style="font-size:0.875rem;">Volunteers who register under your NGO will appear here.</p>
        </div>
    <?php else: ?>
        <div class="vol-grid">
            <?php foreach ($volunteers as $i => $v):
                $name = htmlspecialchars($v['NAME'] ?? '—');
                $email = htmlspecialchars($v['EMAIL'] ?? '—');
                $phone = htmlspecialchars($v['PHONE'] ?? 'Not provided');
                $city = htmlspecialchars($v['CITY'] ?? 'N/A');
                $status = strtoupper($v['STATUS'] ?? 'ACTIVE');
                $initial = strtoupper(substr($v['NAME'] ?? 'V', 0, 1));
                $delay = 0.05 * $i;
                $isActive = $status === 'ACTIVE';
                ?>
                <div class="vol-card" onclick='openVolModal(<?= json_encode($v) ?>)' style="animation-delay: <?= $delay ?>s;">
                    <div class="vol-avatar">
                        <?= $initial ?>
                        <?php if ($isActive): ?>
                            <div class="vol-active-dot" title="Currently Active"></div>
                        <?php endif; ?>
                    </div>
                    <div class="vol-name"><?= $name ?></div>
                    <div class="vol-email"><?= $email ?></div>
                    <div class="vol-meta">
                        <div class="vol-meta-item">
                            <i class="fa-solid fa-phone"></i>
                            <?= $phone ?>
                        </div>
                        <div class="vol-meta-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= $city ?>
                        </div>
                        <div class="vol-meta-item">
                            <i class="fa-solid fa-circle"
                                style="color:<?= $isActive ? '#22c55e' : '#9CA3AF' ?>;font-size:0.5rem;"></i>
                            <span style="color:<?= $isActive ? '#16a34a' : '#6B7280' ?>; font-weight:600;">
                                <?= $status ?>
                            </span>
                        </div>
                    </div>
                    <div class="vol-actions" onclick="event.stopPropagation();">
                        <button onclick='openVolModal(<?= json_encode($v) ?>)' class="btn btn-outline"
                            style="flex:1; padding:6px 10px; font-size:0.75rem;">
                            <i class="fa-solid fa-eye"></i> View
                        </button>
                        <a href="/pages/ngo/accepted.php" class="btn btn-primary"
                            style="flex:1; padding:6px 10px; font-size:0.75rem; background:#8b5cf6; text-align:center; display:flex; align-items:center; justify-content:center; gap:4px;">
                            <i class="fa-solid fa-clipboard-check"></i> Assign
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Volunteer Detail Modal -->
<div id="volModalOverlay" class="vol-modal-overlay" onclick="closeVolModal(event)">
    <div class="vol-modal" id="volModalBox">
        <div class="modal-top">
            <button class="modal-close" onclick="closeVolModal(null)">&times;</button>
            <div class="modal-avatar" id="m-initial">V</div>
            <div class="modal-name" id="m-name">Volunteer</div>
            <div class="modal-email" id="m-email">—</div>
        </div>
        <div class="modal-body">
            <div class="modal-stat">
                <label>Phone</label>
                <p id="m-phone">—</p>
            </div>
            <div class="modal-stat">
                <label>City</label>
                <p id="m-city">—</p>
            </div>
            <div class="modal-stat">
                <label>Status</label>
                <p id="m-status">—</p>
            </div>
            <div class="modal-stat">
                <label>Availability</label>
                <p id="m-avail">—</p>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeVolModal(null)" class="btn btn-outline" style="padding:8px 20px;">Close</button>
            <a href="/pages/ngo/accepted.php" class="btn btn-primary" style="padding:8px 20px; background:#8b5cf6;">
                <i class="fa-solid fa-clipboard-check"></i> Assign Task
            </a>
        </div>
    </div>
</div>

<script>
    function openVolModal(v) {
        document.getElementById('m-initial').innerText = (v.NAME || 'V').charAt(0).toUpperCase();
        document.getElementById('m-name').innerText = v.NAME || '—';
        document.getElementById('m-email').innerText = v.EMAIL || '—';
        document.getElementById('m-phone').innerText = v.PHONE || 'Not provided';
        document.getElementById('m-city').innerText = v.CITY || 'Global';
        document.getElementById('m-status').innerText = v.STATUS || 'ACTIVE';
        document.getElementById('m-avail').innerText = v.AVAILABILITY || 'Flexible';
        document.getElementById('volModalOverlay').classList.add('open');
    }
    function closeVolModal(e) {
        if (e === null || e.target === document.getElementById('volModalOverlay')) {
            document.getElementById('volModalOverlay').classList.remove('open');
        }
    }
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>