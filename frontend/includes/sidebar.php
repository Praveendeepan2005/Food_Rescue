<?php
/**
 * sidebar.php — Role-aware dashboard sidebar
 * Requires $sessionUser to be set (from auth_guard.php).
 * Requires $activePage string to mark the active nav item.
 */
$role = $sessionUser['role'] ?? '';
$name = htmlspecialchars($sessionUser['name'] ?? 'User');
$activePage = $activePage ?? '';

// Define nav items per role
$navItems = [];

if ($role === 'DONOR') {
    $navItems = [
        ['icon' => 'fa-gauge', 'label' => 'Dashboard', 'href' => '/dashboard.php', 'page' => 'overview'],
        ['icon' => 'fa-plus-circle', 'label' => 'Create Donation', 'href' => '/pages/donor/donate.php', 'page' => 'donate'],
        ['icon' => 'fa-list', 'label' => 'My Donations', 'href' => '/pages/donor/my_donations.php', 'page' => 'my_donations'],
        ['icon' => 'fa-clock-rotate-left', 'label' => 'Donation History', 'href' => '/pages/donor/history.php', 'page' => 'history'],
        ['icon' => 'fa-user', 'label' => 'Profile', 'href' => '/pages/donor/profile.php', 'page' => 'profile'],
    ];
} elseif ($role === 'NGO') {
    $navItems = [
        ['icon' => 'fa-gauge', 'label' => 'Dashboard', 'href' => '/dashboard.php', 'page' => 'overview'],
        ['icon' => 'fa-box-open', 'label' => 'Available Donations', 'href' => '/pages/ngo/available.php', 'page' => 'available'],
        ['icon' => 'fa-hand-holding', 'label' => 'Accepted Donations', 'href' => '/pages/ngo/accepted.php', 'page' => 'accepted'],
        ['icon' => 'fa-house-chimney-window', 'label' => 'Recipient Locations', 'href' => '/pages/ngo/orphanages.php', 'page' => 'orphanages'],
        ['icon' => 'fa-people-group', 'label' => 'Volunteers', 'href' => '/pages/ngo/volunteers.php', 'page' => 'volunteers'],
        ['icon' => 'fa-user', 'label' => 'Profile', 'href' => '/pages/ngo/profile.php', 'page' => 'profile'],
    ];
} elseif ($role === 'VOLUNTEER') {
    $navItems = [
        ['icon' => 'fa-gauge', 'label' => 'Dashboard', 'href' => '/dashboard.php', 'page' => 'overview'],
        ['icon' => 'fa-route', 'label' => 'Live Tracking', 'href' => '/pages/volunteer/delivery-map.php', 'page' => 'delivery-map'],
        ['icon' => 'fa-truck', 'label' => 'Assigned Pickups', 'href' => '/pages/volunteer/assignments.php', 'page' => 'assignments'],
        ['icon' => 'fa-clock-rotate-left', 'label' => 'Pickup History', 'href' => '/pages/volunteer/history.php', 'page' => 'history'],
        ['icon' => 'fa-user', 'label' => 'Profile', 'href' => '/pages/volunteer/profile.php', 'page' => 'profile'],
    ];
} elseif ($role === 'ADMIN') {
    $navItems = [
        ['icon' => 'fa-gauge', 'label' => 'Overview', 'href' => '/dashboard.php', 'page' => 'overview'],
        ['icon' => 'fa-route', 'label' => 'Deliveries', 'href' => '/pages/admin/delivery-monitoring.php', 'page' => 'deliveries'],
        ['icon' => 'fa-users', 'label' => 'Users', 'href' => '/pages/admin/users.php', 'page' => 'users'],
        ['icon' => 'fa-bell', 'label' => 'Alerts', 'href' => '/pages/admin/alerts.php', 'page' => 'alerts'],
    ];
}

// Role colors
$roleColors = [
    'DONOR' => ['badge' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.15)', 'label' => 'Donor'],
    'NGO' => ['badge' => '#0d9488', 'bg' => 'rgba(13,148,136,0.15)', 'label' => 'NGO'],
    'VOLUNTEER' => ['badge' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.15)', 'label' => 'Volunteer'],
    'ADMIN' => ['badge' => '#ef4444', 'bg' => 'rgba(239,68,68,0.15)', 'label' => 'Admin'],
];
$rc = $roleColors[$role] ?? ['badge' => '#6B7280', 'bg' => 'rgba(107,114,128,0.15)', 'label' => $role];
?>
<div class="sidebar">
    <a href="/index.php" class="logo"
        style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; margin-bottom: 40px; padding: 0 12px; font-weight: 800; font-size: 1.40rem;">
        <i class="fa-solid fa-leaf" style="color: #4ADE80;"></i>
        FOODLINK
    </a>

    <!-- User Profile Chip -->
    <div
        style="background: rgba(255,255,255,0.07); border-radius: 10px; padding: 14px; margin-bottom: 28px; display:flex; align-items:center; gap:12px;">
        <div
            style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;color:#fff;flex-shrink:0;">
            <?= strtoupper(substr($name, 0, 1)) ?>
        </div>
        <div style="min-width:0;">
            <div
                style="color:#fff;font-weight:600;font-size:0.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= $name ?>
            </div>
            <span
                style="background:<?= $rc['bg'] ?>;color:<?= $rc['badge'] ?>;font-size:0.7rem;font-weight:600;padding:2px 8px;border-radius:20px;">
                <?= $rc['label'] ?>
            </span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['href'] ?>" class="nav-item <?= $activePage === $item['page'] ? 'active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div style="margin-top: auto; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.08);">
        <a href="/logout.php" class="nav-item" style="color: #FCA5A5;">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>
</div>