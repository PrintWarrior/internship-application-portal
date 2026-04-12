<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll();

$statusLabels = [
    'active' => 'Active',
    'banned' => 'Banned',
    'suspended' => 'Suspended',
];

$nextStatusLabels = [
    'active' => 'Suspend',
    'suspended' => 'Ban',
    'banned' => 'Activate',
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
    <title>Manage Users - Super Admin</title>
    <link rel="stylesheet" href="../assets/css/super_manage.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - Super Admin</h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">
                Notifications <span class="badge"><?= $unread ?></span>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- MAIN WRAPPER -->
    <div class="wrapper">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
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
                    
                    <h2>Super Admin</h2>
                    <p>Protected system-level accounts are displayed separately for emphasis.</p>
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
                                        <a href="view_users.php?id=<?= $user['user_id'] ?>">View</a>
                                        <a href="toggle_status.php?id=<?= $user['user_id'] ?>">
                                            <?= htmlspecialchars($nextStatusLabels[$user['status']] ?? 'Update Status') ?>
                                        </a>
                                        <a href="delete_users.php?id=<?= $user['user_id'] ?>"
                                           onclick="return confirm('Delete user <?= htmlspecialchars($user['email']) ?>? This action cannot be undone.')">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

</body>
</html>
