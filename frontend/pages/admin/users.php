<?php
/**
 * pages/admin/users.php — Admin: Manage all users
 */
require_once __DIR__ . '/../../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/api_call.php';

if ($sessionUser['role'] !== 'ADMIN') {
    header('Location: /dashboard.php');
    exit();
}

$activePage = 'users';
$pageTitle = 'Manage Users | Admin | Food Rescue';

// Handle toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_id'])) {
    if (isset($_POST['force_status'])) {
        $statusToSet = $_POST['force_status'];
    } else {
        $statusToSet = $_POST['current_status'] === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
    }
    $res = apiCall('/admin/manage_users.php', ['user_id' => (int) $_POST['toggle_user_id'], 'status' => $statusToSet]);
    $_SESSION['flash'] = !empty($res['success'])
        ? ['type' => 'success', 'msg' => 'User status updated.']
        : ['type' => 'error', 'msg' => $res['message'] ?? 'Action failed.'];
    header('Location: /pages/admin/users.php');
    exit();
}

$data = apiCall('/admin/manage_users.php', [], 'GET');
$users = $data['data']['users'] ?? [];

$grouped = ['NGO' => [], 'DONOR' => [], 'VOLUNTEER' => [], 'ADMIN' => []];
foreach ($users as $u) {
    $r = strtoupper($u['ROLE']);
    if (!isset($grouped[$r])) {
        $grouped[$r] = [];
    }
    $grouped[$r][] = $u;
}

function renderUsersTable(array $list)
{
    if (empty($list)) {
        return '<div style="padding:40px;text-align:center;color:#9ca3af;"><i class="fa-solid fa-users" style="font-size:3rem;margin-bottom:12px;opacity:0.5;"></i><p>No users found in this category.</p></div>';
    }

    $html = '<table><thead><tr>
                <th>Name</th>
                <th>Email</th>
                <th>City</th>
                <th>Status</th>
                <th>Action</th>
             </tr></thead><tbody>';
    foreach ($list as $u) {
        $isSuspended = strtoupper($u['STATUS']) === 'SUSPENDED';
        $statusBadge = $isSuspended ? 'error' : 'completed';

        $name = htmlspecialchars($u['NAME']);
        $email = htmlspecialchars($u['EMAIL']);
        $city = htmlspecialchars($u['CITY'] ?? '—');
        $status = htmlspecialchars($u['STATUS']);
        $uid = $u['USER_ID'];

        $btnActiveDisabled = !$isSuspended ? 'disabled' : '';
        $btnActiveStyle = !$isSuspended ? 'opacity:0.4; cursor:not-allowed;' : '';

        $btnSuspendDisabled = $isSuspended ? 'disabled' : '';
        $btnSuspendStyle = $isSuspended ? 'opacity:0.4; cursor:not-allowed;' : '';

        $html .= "<tr>
            <td><strong>{$name}</strong></td>
            <td style=\"color:#6B7280;\">{$email}</td>
            <td>{$city}</td>
            <td><span class=\"badge badge-{$statusBadge}\">{$status}</span></td>
            <td>
                <div style=\"display:flex; gap:8px;\">
                    <form method=\"POST\" style=\"margin:0;\">
                        <input type=\"hidden\" name=\"toggle_user_id\" value=\"{$uid}\">
                        <input type=\"hidden\" name=\"force_status\" value=\"ACTIVE\">
                        <button type=\"submit\" {$btnActiveDisabled} style=\"font-size:0.75rem; font-weight:700; padding:6px 12px; color:#2E7D32; background:#f0fdf4; border:1px solid #2E7D32; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:opacity 0.2s; {$btnActiveStyle}\">
                            <i class=\"fa-solid fa-unlock\"></i> Activate
                        </button>
                    </form>
                    <form method=\"POST\" style=\"margin:0;\">
                        <input type=\"hidden\" name=\"toggle_user_id\" value=\"{$uid}\">
                        <input type=\"hidden\" name=\"force_status\" value=\"SUSPENDED\">
                        <button type=\"submit\" {$btnSuspendDisabled} style=\"font-size:0.75rem; font-weight:700; padding:6px 12px; color:#dc2626; background:#fef2f2; border:1px solid #dc2626; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:opacity 0.2s; {$btnSuspendStyle}\">
                            <i class=\"fa-solid fa-ban\"></i> Suspend
                        </button>
                    </form>
                </div>
            </td>
        </tr>";
    }
    $html .= '</tbody></table>';
    return $html;
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="dashboard-container">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/../../includes/flash.php'; ?>
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Manage Users</h1>
            <p style="color:#6B7280;font-size:0.875rem;">Total:
                <?= count($users) ?> registered users
            </p>
        </div>

        <style>
            .role-tabs {
                display: flex;
                gap: 8px;
                margin-bottom: 20px;
                border-bottom: 2px solid #F3F4F6;
                padding-bottom: 12px;
                flex-wrap: wrap;
            }

            .role-tab {
                background: #fff;
                border: 1px solid #E5E7EB;
                padding: 10px 20px;
                font-size: .9rem;
                font-weight: 700;
                color: #6B7280;
                cursor: pointer;
                border-radius: 99px;
                transition: all .2s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .role-tab:hover {
                background: #F9FAFB;
                color: #111827;
            }

            .role-tab.active {
                background: #2E7D32;
                border-color: #2E7D32;
                color: #fff;
                box-shadow: 0 4px 12px rgba(46, 125, 50, .2);
            }

            .tab-pane {
                display: none;
                animation: slideUp .3s ease both;
            }

            .tab-pane.active {
                display: block;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>

        <div class="role-tabs">
            <button class="role-tab active" onclick="showTab('ngo', this)">
                <i class="fa-solid fa-building"></i> NGOs <span
                    style="background:rgba(0,0,0,.1);padding:2px 8px;border-radius:10px;font-size:.75rem;"><?= count($grouped['NGO']) ?></span>
            </button>
            <button class="role-tab" onclick="showTab('donor', this)">
                <i class="fa-solid fa-hand-holding-heart"></i> Donors <span
                    style="background:rgba(0,0,0,.1);padding:2px 8px;border-radius:10px;font-size:.75rem;"><?= count($grouped['DONOR']) ?></span>
            </button>
            <button class="role-tab" onclick="showTab('volunteer', this)">
                <i class="fa-solid fa-hands-holding-circle"></i> Volunteers <span
                    style="background:rgba(0,0,0,.1);padding:2px 8px;border-radius:10px;font-size:.75rem;"><?= count($grouped['VOLUNTEER']) ?></span>
            </button>
            <button class="role-tab" onclick="showTab('admin', this)">
                <i class="fa-solid fa-shield"></i> Admins <span
                    style="background:rgba(0,0,0,.1);padding:2px 8px;border-radius:10px;font-size:.75rem;"><?= count($grouped['ADMIN']) ?></span>
            </button>
        </div>

        <div id="pane-ngo" class="table-card tab-pane active">
            <?= renderUsersTable($grouped['NGO']) ?>
        </div>
        <div id="pane-donor" class="table-card tab-pane">
            <?= renderUsersTable($grouped['DONOR']) ?>
        </div>
        <div id="pane-volunteer" class="table-card tab-pane">
            <?= renderUsersTable($grouped['VOLUNTEER']) ?>
        </div>
        <div id="pane-admin" class="table-card tab-pane">
            <?= renderUsersTable($grouped['ADMIN']) ?>
        </div>

        <script>
            function showTab(role, btnEl) {
                document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                btnEl.classList.add('active');
                document.getElementById('pane-' + role).classList.add('active');
            }
        </script>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>