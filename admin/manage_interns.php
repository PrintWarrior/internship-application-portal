<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Get all interns with additional info
$stmt = $pdo->query("
    SELECT i.*, u.email, u.created_at 
    FROM interns i
    JOIN users u ON i.user_id = u.user_id
    ORDER BY i.intern_id DESC
");
$interns = $stmt->fetchAll();

// Calculate stats
$totalInterns = count($interns);
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Interns - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_intern.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/delete_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
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
            <a href="profile.php">Profile</a>
            <a href="manage_interns.php">Manage Interns</a>
            <!--<a href="manage_companies.php">Manage Companies</a>-->
            <a href="manage_staffs.php">Manage Staffs</a>
            <a href="manage_internships.php">Manage Internships</a>
            <a href="applications.php">All Applications</a>
            <!--<a href="reports.php">Reports & Analytics</a>-->
            <a href="system_logs.php">System Logs</a>
            <a href="about.php">About</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Manage Interns</h2>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3><?= $totalInterns ?></h3>
                    <p>Total Interns</p>
                </div>
                <div class="stat-card">
                    <h3><?= $totalInterns ?></h3>
                    <p>Active</p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <input type="text" class="search-input" id="searchInput" placeholder="Search by name or email...">
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                </select>
            </div>

            <!-- Interns Table -->
            <div class="table-container">
                <?php if (count($interns) > 0): ?>
                    <table id="internsTable">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interns as $intern): ?>
                                <tr>
                                    <!-- <td>#<?= $intern['intern_id'] ?></td> -->
                                    <td><?= htmlspecialchars($intern['first_name']) ?></td>
                                    <td><?= htmlspecialchars($intern['last_name']) ?></td>
                                    <td><?= htmlspecialchars($intern['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($intern['created_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_intern.php?id=<?= $intern['intern_id'] ?>"
                                                class="btn-action btn-view">View</a>
                                            <!-- <a href="edit_intern.php?id=<?= $intern['intern_id'] ?>"
                                                class="btn-action btn-edit">Edit</a> -->
                                            <a href="#" class="btn-action btn-delete"
                                                onclick="openDeleteModal(<?= $intern['intern_id'] ?>, '<?= htmlspecialchars($intern['first_name']) ?>', '<?= htmlspecialchars($intern['last_name']) ?>', '<?= htmlspecialchars($intern['email']) ?>'); return false;">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        No interns found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {

            const searchText = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#internsTable tbody tr');

            rows.forEach(row => {

                // get full row text instead of specific columns
                const rowText = row.textContent.toLowerCase();

                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }

            });

        });


        // Sort functionality
        // Sort functionality
document.getElementById('sortFilter').addEventListener('change', function () {
    const sortBy = this.value;
    const tbody = document.querySelector('#internsTable tbody');
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
                // First name is at index 0, last name at index 1
                const firstNameA = a.cells[0]?.textContent || '';
                const firstNameB = b.cells[0]?.textContent || '';
                return firstNameA.localeCompare(firstNameB);
            case 'name_desc':
                const firstNameC = a.cells[0]?.textContent || '';
                const firstNameD = b.cells[0]?.textContent || '';
                return firstNameD.localeCompare(firstNameC);
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

    <script src="../js/responsive-nav.js"></script>
</body>

</html>
