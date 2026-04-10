<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get all applications
$stmt = $pdo->prepare("
    SELECT a.*, i.title, ir.first_name, ir.last_name, ir.contact_no, u.email,
           c.contract_id
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    LEFT JOIN contracts c ON a.application_id = c.application_id
    WHERE i.company_id = ?
    ORDER BY a.date_applied DESC
");
$stmt->execute([$company['company_id']]);
$applications = $stmt->fetchAll();

// Calculate stats
$total = count($applications);
$pending = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
$accepted = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
$rejected = count(array_filter($applications, fn($a) => $a['status'] === 'Declined'));
$offered = count(array_filter($applications, fn($a) => $a['status'] === 'Offered'));

// Function to get status class
function getStatusClass($status)
{
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>View Applicants</title>
    <link rel="stylesheet" href="../assets/css/company_applicant.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>

<body>

    <!-- TOP NAV -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h4>Internship Portal - Company</h4>
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
            <a href="profile.php">Company Profile</a>
            <a href="staff_profile.php">Staff Profile</a>
            <a href="post_internship.php">Post Internship</a>
            <a href="manage_internships.php">My Internships</a>
            <a href="view_applicants.php">View Applicants</a>
            <a href="contracts.php">Contracts</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Applicants</h2>

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
                    <h3><?= $offered ?></h3>
                    <p>Offered</p>
                </div>
                <div class="stat-card">
                    <h3><?= $rejected ?></h3>
                    <p>Declined</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Declined">Declined</option>
                    <option value="Offered">Offered</option>
                </select>

                <select class="filter-select" id="internshipFilter">
                    <option value="all">All Internships</option>
                    <?php
                    $uniqueTitles = array_unique(array_column($applications, 'title'));
                    foreach ($uniqueTitles as $title):
                        ?>
                        <option value="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Applications Table -->
            <div class="table-container">
                <?php if (count($applications) > 0): ?>
                    <table id="applicationsTable">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Internship</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr data-status="<?= $app['status'] ?>"
                                    data-internship="<?= htmlspecialchars($app['title']) ?>">
                                    <td>
                                        <a href="view_intern.php?id=<?= $app['intern_id'] ?>" class="applicant-name">
                                            <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($app['title']) ?></td>
                                    <td><?= date('M d, Y', strtotime($app['date_applied'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= getStatusClass($app['status']) ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
            
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_applications.php?id=<?= $app['application_id'] ?>"
                                                class="btn-action btn-view">View</a>
                                           <!-- <a href="update_status.php?id=<?= $app['application_id'] ?>"
                                                class="btn-action btn-update">Update</a> -->

                                            <?php if ($app['contract_id']): ?>
                                                <a href="contracts.php?id=<?= $app['contract_id'] ?>"
                                                    class="btn-action btn-view-contract">View Contract</a>
                                            <?php else: ?>
                                                <a href="create_contract.php?application_id=<?= $app['application_id'] ?>"
                                                    class="btn-action btn-create-contract">Create Contract</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                        
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        No applications received yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Simple filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('internshipFilter').addEventListener('change', filterTable);

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const internshipFilter = document.getElementById('internshipFilter').value;
            const rows = document.querySelectorAll('#applicationsTable tbody tr');

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const internship = row.getAttribute('data-internship');

                const statusMatch = statusFilter === 'all' || status === statusFilter;
                const internshipMatch = internshipFilter === 'all' || internship === internshipFilter;

                row.style.display = statusMatch && internshipMatch ? '' : 'none';
            });
        }
    </script>
        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
</body>

</html>
