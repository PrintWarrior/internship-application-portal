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

// Get all applications with details
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
");
$stmt->execute();
$applications = $stmt->fetchAll();

// Calculate stats
$total = count($applications);
$pending = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
$accepted = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
$declined = count(array_filter($applications, fn($a) => $a['status'] === 'Declined'));
$offered = count(array_filter($applications, fn($a) => $a['status'] === 'Offered'));
//$hired = count(array_filter($applications, fn($a) => $a['status'] === 'Hired'));

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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
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
