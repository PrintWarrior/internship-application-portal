<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
ensureAccountAppealSchema($pdo);
$panelLabel = getAdminAreaLabel();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$itemsPerPage = 9;
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]) ?: 1;
$allowedFilters = ['all', 'staff', 'intern', 'admin'];
$selectedFilter = $_GET['filter'] ?? 'all';
$searchTerm = trim((string) ($_GET['search'] ?? ''));

if (!in_array($selectedFilter, $allowedFilters, true)) {
    $selectedFilter = 'all';
}

$statusLabels = [
    'active' => 'Active',
    'banned' => 'Banned',
    'suspended' => 'Suspended',
];

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_type = 'superadmin' ORDER BY user_id DESC");
$stmt->execute();
$superAdminUsers = $stmt->fetchAll();

$otherUsersWhere = "user_type <> 'superadmin'";
$otherUsersParams = [];

if ($selectedFilter !== 'all') {
    $otherUsersWhere .= " AND user_type = :user_type";
    $otherUsersParams[':user_type'] = $selectedFilter;
}

if ($searchTerm !== '') {
    $otherUsersWhere .= " AND email LIKE :search";
    $otherUsersParams[':search'] = '%' . $searchTerm . '%';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE {$otherUsersWhere}");
$stmt->execute($otherUsersParams);
$totalOtherUsers = (int) $stmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalOtherUsers / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;

$stmt = $pdo->prepare("SELECT * FROM users WHERE {$otherUsersWhere} ORDER BY user_id DESC LIMIT :limit OFFSET :offset");
foreach ($otherUsersParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$otherUsers = $stmt->fetchAll();

$pageWindow = 2;
$startPage = max(1, $currentPage - $pageWindow);
$endPage = min($totalPages, $currentPage + $pageWindow);
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
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
                <li><a href="appeals.php">Appeals</a></li>
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
                    <p>Administrative and operational accounts appear below the super admin tier. Showing 9 users per page.</p>
                </div>

                <div class="filter-tabs" aria-label="User type filters">
                    <a href="?filter=all&search=<?= urlencode($searchTerm) ?>" class="filter-tab <?= $selectedFilter === 'all' ? 'active' : '' ?>">All</a>
                    <a href="?filter=staff&search=<?= urlencode($searchTerm) ?>" class="filter-tab <?= $selectedFilter === 'staff' ? 'active' : '' ?>">Staff</a>
                    <a href="?filter=intern&search=<?= urlencode($searchTerm) ?>" class="filter-tab <?= $selectedFilter === 'intern' ? 'active' : '' ?>">Intern</a>
                    <a href="?filter=admin&search=<?= urlencode($searchTerm) ?>" class="filter-tab <?= $selectedFilter === 'admin' ? 'active' : '' ?>">Admin</a>
                </div>

                <form method="GET" class="search-form">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($selectedFilter) ?>">
                    <input
                        type="search"
                        name="search"
                        class="search-input"
                        placeholder="Search by email..."
                        value="<?= htmlspecialchars($searchTerm) ?>"
                    >
                    <button type="submit" class="search-btn">Search</button>
                    <?php if ($searchTerm !== ''): ?>
                        <a href="?filter=<?= urlencode($selectedFilter) ?>" class="search-reset">Clear</a>
                    <?php endif; ?>
                </form>

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
                            <?php if (empty($otherUsers)): ?>
                                <tr>
                                    <td colspan="4">No users found for the selected filter and search.</td>
                                </tr>
                            <?php else: ?>
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
                                            <div class="action-group">
                                                <a href="view_users.php?id=<?= $user['user_id'] ?>">View</a>
                                                <form method="POST" action="toggle_status.php" style="display:inline;">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                    <input type="hidden" name="status" value="active">
                                                    <input type="hidden" name="status_reason" value="">
                                                    <input type="hidden" name="appeal_allowed" value="1">
                                                    <button type="submit" class="<?= $user['status'] === 'active' ? 'current-status-action' : '' ?>">Activate</button>
                                                </form>
                                                <form method="POST" action="toggle_status.php" style="display:inline;" class="status-change-form" data-status-target="suspended">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                    <input type="hidden" name="status" value="suspended">
                                                    <input type="hidden" name="status_reason" value="">
                                                    <input type="hidden" name="appeal_allowed" value="1">
                                                    <button type="submit" class="<?= $user['status'] === 'suspended' ? 'current-status-action' : '' ?>">Suspend</button>
                                                </form>
                                                <form method="POST" action="toggle_status.php" style="display:inline;" class="status-change-form" data-status-target="banned">
                                                    <?= csrf_input() ?>
                                                    <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                    <input type="hidden" name="status" value="banned">
                                                    <input type="hidden" name="status_reason" value="">
                                                    <input type="hidden" name="appeal_allowed" value="0">
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
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Other users pagination">
                        <?php if ($currentPage > 1): ?>
                            <a class="pagination-btn" href="?filter=<?= urlencode($selectedFilter) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage - 1 ?>">Previous</a>
                        <?php endif; ?>

                        <div class="pagination-numbers">
                            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                                <a class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>" href="?filter=<?= urlencode($selectedFilter) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $page ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>>
                                    <?= $page ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($currentPage < $totalPages): ?>
                            <a class="pagination-btn" href="?filter=<?= urlencode($selectedFilter) ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $currentPage + 1 ?>">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('.status-change-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const targetStatus = form.dataset.statusTarget || '';
                const reasonInput = form.querySelector('input[name="status_reason"]');
                const appealInput = form.querySelector('input[name="appeal_allowed"]');

                const reasonPrompt = targetStatus === 'banned'
                    ? 'Enter the reason for banning this user:'
                    : 'Enter the reason for suspending this user:';

                const reason = window.prompt(reasonPrompt, reasonInput.value || '');
                if (reason === null) {
                    event.preventDefault();
                    return;
                }

                if (!reason.trim()) {
                    window.alert('A reason is required.');
                    event.preventDefault();
                    return;
                }

                reasonInput.value = reason.trim();

                if (targetStatus === 'suspended') {
                    appealInput.value = '1';
                    return;
                }

                const allowAppeal = window.confirm('Allow this banned user to submit an appeal?');
                appealInput.value = allowAppeal ? '1' : '0';
            });
        });
    </script>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
