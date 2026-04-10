<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Get all companies with additional info
$stmt = $pdo->query("
    SELECT c.*, u.email, u.created_at 
    FROM companies c
    LEFT JOIN staffs s ON c.company_id = s.company_id
    LEFT JOIN users u ON s.user_id = u.user_id
    GROUP BY c.company_id
    ORDER BY c.company_id DESC
");
$companies = $stmt->fetchAll();

// Calculate stats
$totalCompanies = count($companies);
$withInternships = 0;

// Count companies with internships
foreach ($companies as $company) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ?");
    $stmt->execute([$company['company_id']]);
    if ($stmt->fetchColumn() > 0) {
        $withInternships++;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Companies - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_company.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/delete_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>

<body>

    <!-- TOP NAV -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h4>Internship Portal - Admin</h4>
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
            <a href="index.php">Dashboard</a>
            <a href="manage_interns.php">Manage Interns</a>
            <a href="manage_companies.php" class="active">Manage Companies</a>
            <a href="manage_staffs.php">Manage Staffs</a>
            <a href="manage_internships.php">Manage Internships</a>
            <a href="applications.php">All Applications</a>
            <!--<a href="reports.php">Reports & Analytics</a>-->
            <a href="system_logs.php">System Logs</a>
            <a href="about.php">About</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Manage Companies</h2>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3><?= $totalCompanies ?></h3>
                    <p>Total Companies</p>
                </div>
                <div class="stat-card">
                    <h3><?= $withInternships ?></h3>
                    <p>With Internships</p>
                </div>
                <div class="stat-card">
                    <h3><?= $totalCompanies - $withInternships ?></h3>
                    <p>No Internships</p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <input type="text" class="search-input" id="searchInput"
                    placeholder="Search by company name or email...">
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                </select>
            </div>

            <!-- Companies Table -->
            <div class="table-container">
                <?php if (count($companies) > 0): ?>
                    <table id="companiesTable">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Industry</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <!-- <td>#<?= $company['company_id'] ?></td> -->
                                    <td><strong><?= htmlspecialchars($company['company_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($company['email']) ?></td>
                                    <td>
                                        <?php if (!empty($company['industry'])): ?>
                                            <span class="industry-badge"><?= htmlspecialchars($company['industry']) ?></span>
                                        <?php else: ?>
                                            <span class="industry-badge">Not Specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($company['created_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_company.php?id=<?= $company['company_id'] ?>"
                                                class="btn-action btn-view">View</a>
                                            <!-- <a href="edit_company.php?id=<?= $company['company_id'] ?>"
                                                class="btn-action btn-edit">Edit</a> -->
                                            <a href="#" class="btn-action btn-delete" onclick="openDeleteCompanyModal(
<?= $company['company_id'] ?>,
'<?= htmlspecialchars($company['company_name']) ?>',
'<?= htmlspecialchars($company['email']) ?>'
); return false;">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        No companies found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#companiesTable tbody tr');

            rows.forEach(row => {
                // Fix: Use correct column indices
                // Column 0: Company Name
                // Column 1: Email
                // Column 2: Industry
                const companyName = row.cells[0]?.textContent.toLowerCase() || '';
                const email = row.cells[1]?.textContent.toLowerCase() || '';
                const industry = row.cells[2]?.textContent.toLowerCase() || '';

                if (companyName.includes(searchText) || email.includes(searchText) || industry.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Sort functionality
        document.getElementById('sortFilter').addEventListener('change', function () {
            const sortBy = this.value;
            const tbody = document.querySelector('#companiesTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                switch (sortBy) {
                    case 'newest':
                    case 'oldest':
                        // Parse the date column (index 3 for Joined Date)
                        const dateA = new Date(a.cells[3]?.textContent);
                        const dateB = new Date(b.cells[3]?.textContent);
                        return sortBy === 'newest' ? dateB - dateA : dateA - dateB;

                    case 'name_asc':
                        // Company name is at index 0
                        const nameA = a.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                        const nameB = b.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                        return nameA.localeCompare(nameB);

                    case 'name_desc':
                        const nameC = a.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                        const nameD = b.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                        return nameD.localeCompare(nameC);

                    default:
                        return 0;
                }
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    </script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/delete_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/delete_modal.js"></script>

</body>

</html>
