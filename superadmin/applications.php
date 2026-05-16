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

$itemsPerPage = 9;
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]) ?: 1;

$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) AS accepted,
        SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END) AS declined,
        SUM(CASE WHEN status = 'Offered' THEN 1 ELSE 0 END) AS offered
    FROM applications
");
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$total = (int) ($stats['total'] ?? 0);
$pending = (int) ($stats['pending'] ?? 0);
$accepted = (int) ($stats['accepted'] ?? 0);
$declined = (int) ($stats['declined'] ?? 0);
$offered = (int) ($stats['offered'] ?? 0);

$totalPages = max(1, (int) ceil($total / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;

// Get paginated applications with details
$stmt = $pdo->prepare("
    SELECT a.*, 
           i.title,
           ir.first_name, ir.last_name, u.email,
           c.company_name
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN users u ON ir.user_id = u.user_id
    ORDER BY a.date_applied DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$applications = $stmt->fetchAll();

$pageWindow = 2;
$startPage = max(1, $currentPage - $pageWindow);
$endPage = min($totalPages, $currentPage + $pageWindow);

// Function to get status class
function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($panelLabel) ?> Applications</title>
    <link rel="stylesheet" href="../assets/css/application.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<!-- TOP NAV -->
<div class="topnav">
    <div class="logo-section">
        <img src="../assets/img/logo.png" alt="Logo">
        <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
    </div>

    <div class="topnav-right">
        <a href="notifications.php">
            Notifications <span class="badge"><?= $unread ?></span>
        </a>
        <a href="#" onclick="openLogoutModal()">Logout</a>
    </div>
</div>

<div class="wrapper">
    <!-- SIDEBAR -->
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

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>All Applications</h2>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3><?= $total ?></h3>
                <p>Total</p>
            </div>
            <div class="stat-card">
                <h3><?= $pending ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card">
                <h3><?= $accepted ?></h3>
                <p>Accepted</p>
            </div>
            <div class="stat-card">
                <h3><?= $declined ?></h3>
                <p>Declined</p>
            </div>
            <div class="stat-card">
                <h3><?= $offered ?></h3>
                <p>Offered</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <input type="text" class="search-input" id="searchInput" placeholder="Search by intern, internship, or company...">
            <select class="filter-select" id="statusFilter">
                <option value="all">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Declined">Declined</option>
                <option value="Offered">Offered</option>
            </select>
        </div>

        <!-- Applications Table -->
        <div class="table-container">
            <?php if (count($applications) > 0): ?>
            <table id="applicationsTable">
                <thead>
                    <tr>
                        <th>Intern</th>
                        <th>Internship</th>
                        <th>Company</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr data-status="<?= $app['status'] ?>">
                        <td>
                            <a href="view_intern.php?id=<?= $app['intern_id'] ?>" class="intern-name">
                                <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($app['title']) ?></td>
                        <td>
                            <span class="company-badge"><?= htmlspecialchars($app['company_name']) ?></span>
                        </td>
                        <td>
                            <span class="application-date"><?= date('M d, Y', strtotime($app['date_applied'])) ?></span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= getStatusClass($app['status']) ?>">
                                <?= htmlspecialchars($app['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="view_application.php?id=<?= $app['application_id'] ?>" class="btn-action btn-view">View</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                No applications found.
            </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Applications pagination">
            <?php if ($currentPage > 1): ?>
            <a class="pagination-btn" href="?page=<?= $currentPage - 1 ?>">Previous</a>
            <?php endif; ?>

            <div class="pagination-numbers">
                <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                <a class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>" href="?page=<?= $page ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>>
                    <?= $page ?>
                </a>
                <?php endfor; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
            <a class="pagination-btn" href="?page=<?= $currentPage + 1 ?>">Next</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
// Search and filter functionality
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#applicationsTable tbody tr');
    
    rows.forEach(row => {
        const intern = row.cells[0].textContent.toLowerCase();
        const internship = row.cells[1].textContent.toLowerCase();
        const company = row.cells[2].textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = intern.includes(searchText) || 
                             internship.includes(searchText) || 
                             company.includes(searchText);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}
</script>

        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
