<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM users ORDER BY user_id DESC");
$stmt->execute();
$users = $stmt->fetchAll();

$statusLabels = [
    'active' => 'Active',
    'banned' => 'Banned',
    'suspended' => 'Suspended',
];

$superAdminUsers = array_filter($users, function ($user) {
    return $user['user_type'] === 'superadmin';
});

$otherUsers = array_filter($users, function ($user) {
    return $user['user_type'] !== 'superadmin';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/super_manage.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">
                Notifications <span class="badge"><?= $unread ?></span>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>Manage Users</h1>

            <?php if (isset($_SESSION['feedback_message'])): ?>
                <div style="margin-bottom: 20px; padding: 12px 16px; border: 1px solid #000; background: <?= ($_SESSION['feedback_type'] ?? 'success') === 'error' ? '#000' : '#fff' ?>; color: <?= ($_SESSION['feedback_type'] ?? 'success') === 'error' ? '#fff' : '#000' ?>;">
                    <?= htmlspecialchars($_SESSION['feedback_message']) ?>
                </div>
                <?php
                unset($_SESSION['feedback_message']);
                unset($_SESSION['feedback_type']);
                ?>
            <?php endif; ?>

            <section class="user-group user-group-superadmin">
                <div class="section-heading">
                    <h2>Protected Admin Accounts</h2>
                    <p>These accounts use the same features as admins, but they cannot be deleted.</p>
                </div>

                <div class="table-section table-section-superadmin">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($superAdminUsers as $user): ?>
                                <?php
                                $statusClass = $user['status'] === 'active' ? 'status-active' : 'status-inactive';
                                $userTypeClass = 'user-type-' . strtolower($user['user_type']);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="user-type <?= $userTypeClass ?>">
                                            <?= ucfirst($user['user_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($statusLabels[$user['status']] ?? ucfirst($user['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="protected-label">System Protected</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="user-group">
                <div class="section-heading">
                    <h2>Other Users</h2>
                    <p>Administrative and operational accounts appear below the super admin tier.</p>
                </div>

                <div class="filter-tabs" aria-label="User type filters">
                    <button type="button" class="filter-tab active" data-filter="all">All</button>
                    <button type="button" class="filter-tab" data-filter="staff">Staff</button>
                    <button type="button" class="filter-tab" data-filter="intern">Intern</button>
                    <button type="button" class="filter-tab" data-filter="admin">Admin</button>
                </div>

                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otherUsers as $user): ?>
                                <?php
                                $statusClass = $user['status'] === 'active' ? 'status-active' : 'status-inactive';
                                $userTypeClass = 'user-type-' . strtolower($user['user_type']);
                                ?>
                                <tr class="user-row" data-user-type="<?= htmlspecialchars($user['user_type']) ?>">
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="user-type <?= $userTypeClass ?>">
                                            <?= ucfirst($user['user_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($statusLabels[$user['status']] ?? ucfirst($user['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="view_users.php?id=<?= $user['user_id'] ?>">View</a>
                                            <form method="POST" action="toggle_status.php" style="display:inline;">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="<?= $user['status'] === 'active' ? 'current-status-action' : '' ?>">Activate</button>
                                            </form>
                                            <form method="POST" action="toggle_status.php" style="display:inline;">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                <input type="hidden" name="status" value="suspended">
                                                <button type="submit" class="<?= $user['status'] === 'suspended' ? 'current-status-action' : '' ?>">Suspend</button>
                                            </form>
                                            <form method="POST" action="toggle_status.php" style="display:inline;">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                <input type="hidden" name="status" value="banned">
                                                <button type="submit" class="<?= $user['status'] === 'banned' ? 'current-status-action' : '' ?>">Ban</button>
                                            </form>
                                            <form method="POST" action="delete_users.php" style="display:inline;" onsubmit="return confirm('Delete user <?= htmlspecialchars($user['email']) ?>? This action cannot be undone.')">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        const filterTabs = document.querySelectorAll('.filter-tab');
        const userRows = document.querySelectorAll('.user-row');

        filterTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const filterType = tab.dataset.filter;

                filterTabs.forEach((item) => item.classList.remove('active'));
                tab.classList.add('active');

                userRows.forEach((row) => {
                    const showRow = filterType === 'all' || row.dataset.userType === filterType;
                    row.style.display = showRow ? '' : 'none';
                });
            });
        });
    </script>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
