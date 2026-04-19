<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Get system logs with pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("
    SELECT l.*, u.email
    FROM system_logs l
    LEFT JOIN users u ON l.user_id = u.user_id
    ORDER BY l.created_at DESC
    LIMIT $limit OFFSET $offset
");
$logs = $stmt->fetchAll();

// Get total count for pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM system_logs");
$totalLogs = $totalStmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Get stats
$today = date('Y-m-d');
$todayStmt = $pdo->prepare("SELECT COUNT(*) FROM system_logs WHERE DATE(created_at) = ?");
$todayStmt->execute([$today]);
$todayCount = $todayStmt->fetchColumn();

$uniqueUsersStmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM system_logs WHERE user_id IS NOT NULL");
$uniqueUsers = $uniqueUsersStmt->fetchColumn();

// Get unique action types for filter
$actionTypesStmt = $pdo->query("SELECT DISTINCT action FROM system_logs ORDER BY action");
$actionTypes = $actionTypesStmt->fetchAll(PDO::FETCH_COLUMN);

function getActionClass($action)
{
    $action = strtolower($action);
    if (strpos($action, 'create') !== false) {
        return 'action-create';
    }
    if (strpos($action, 'update') !== false) {
        return 'action-update';
    }
    if (strpos($action, 'delete') !== false) {
        return 'action-delete';
    }
    if (strpos($action, 'login') !== false) {
        return 'action-login';
    }
    if (strpos($action, 'logout') !== false) {
        return 'action-logout';
    }

    return '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/system.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="topnav">
    <div class="logo-section">
        <img src="../assets/img/logo.png" alt="Logo">
        <h4>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h4>
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
        <a href="index.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="create_users.php">Create Users</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_internships.php">Manage Internships</a>
        <a href="applications.php">All Applications</a>
        <a href="system_logs.php" class="active">System Logs</a>
        <a href="about.php">About</a>
    </div>

    <div class="main-content">
        <h2>System Logs</h2>

        <div class="logs-stats">
            <div class="stat-item">
                <span class="stat-label">Total Logs:</span>
                <span class="stat-value"><?= $totalLogs ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Today:</span>
                <span class="stat-value"><?= $todayCount ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Active Users:</span>
                <span class="stat-value"><?= $uniqueUsers ?></span>
            </div>
        </div>

        <div class="logs-controls">
            <div class="logs-filters">
                <select class="filter-select" id="actionFilter">
                    <option value="all">All Actions</option>
                    <?php foreach ($actionTypes as $action): ?>
                    <option value="<?= htmlspecialchars($action) ?>"><?= htmlspecialchars($action) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" class="search-input" id="searchInput" placeholder="Search by user or action...">
            </div>
            <div class="logs-actions">
                <button class="btn-action btn-refresh" onclick="location.reload()">Refresh</button>
                <a href="export_logs.php" class="btn-action btn-export">Export</a>
                <a href="clear_logs.php" class="btn-action btn-clear" onclick="return confirm('Are you sure you want to clear logs? This action cannot be undone.');">Clear</a>
            </div>
        </div>

        <div class="table-container">
            <?php if (count($logs) > 0): ?>
            <table id="logsTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr data-action="<?= htmlspecialchars($log['action']) ?>">
                        <td>
                            <?php if ($log['email']): ?>
                                <span class="user-badge"><?= htmlspecialchars($log['email']) ?></span>
                            <?php else: ?>
                                <span class="user-badge user-system">SYSTEM</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="action-log <?= getActionClass($log['action']) ?>">
                                <?= htmlspecialchars($log['action']) ?>
                            </span>
                        </td>
                        <td class="log-date"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                No system logs found.
            </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-item <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('actionFilter').addEventListener('change', filterTable);
document.getElementById('searchInput').addEventListener('keyup', filterTable);

function filterTable() {
    const actionFilter = document.getElementById('actionFilter').value;
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#logsTable tbody tr');

    rows.forEach(row => {
        const action = row.getAttribute('data-action');
        const user = row.cells[0].textContent.toLowerCase();
        const actionText = row.cells[1].textContent.toLowerCase();

        const matchesAction = actionFilter === 'all' || action === actionFilter;
        const matchesSearch = user.includes(searchText) || actionText.includes(searchText);

        row.style.display = matchesAction && matchesSearch ? '' : 'none';
    });
}
</script>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
