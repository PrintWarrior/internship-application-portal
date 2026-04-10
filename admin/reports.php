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

// Basic counts
$totalInterns = $pdo->query("SELECT COUNT(*) FROM interns")->fetchColumn();
$totalCompanies = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalInternships = $pdo->query("SELECT COUNT(*) FROM internships")->fetchColumn();
$totalApplications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Additional stats
$openInternships = $pdo->query("SELECT COUNT(*) FROM internships WHERE status = 'open'")->fetchColumn();
$pendingApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Pending'")->fetchColumn();
$acceptedApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Accepted'")->fetchColumn();
$rejectedApplications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Rejected'")->fetchColumn();

// Companies with internships
$companiesWithInternships = $pdo->query("
    SELECT COUNT(DISTINCT company_id) FROM internships
")->fetchColumn();

// Interns with applications
$internsWithApplications = $pdo->query("
    SELECT COUNT(DISTINCT intern_id) FROM applications
")->fetchColumn();

// Monthly applications (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM applications 
        WHERE DATE_FORMAT(date_applied, '%Y-%m') = ?
    ");
    $stmt->execute([$month]);
    $monthlyData[$month] = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports & Analytics - Admin</title>
    <link rel="stylesheet" href="../assets/css/reports.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
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
        <a href="manage_companies.php">Manage Companies</a>
        <a href="manage_internships.php">Manage Internships</a>
        <a href="applications.php">All Applications</a>
        <a href="reports.php" class="active">Reports & Analytics</a>
        <a href="system_logs.php">System Logs</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Reports & Analytics</h2>

        <div class="reports-container">
            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3><?= $totalInterns ?></h3>
                    <p>Total Interns</p>
                </div>
                <div class="summary-card">
                    <h3><?= $totalCompanies ?></h3>
                    <p>Total Companies</p>
                </div>
                <div class="summary-card">
                    <h3><?= $totalInternships ?></h3>
                    <p>Total Internships</p>
                </div>
                <div class="summary-card">
                    <h3><?= $totalApplications ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>

            <!-- Analytics Grid -->
            <div class="analytics-grid">
                <!-- Application Status Breakdown -->
                <div class="analytics-card">
                    <h3>Application Status</h3>
                    <ul class="stats-list">
                        <li>
                            <span class="stat-label">Pending</span>
                            <span class="stat-value"><?= $pendingApplications ?></span>
                        </li>
                        <li>
                            <span class="stat-label">Accepted</span>
                            <span class="stat-value"><?= $acceptedApplications ?></span>
                        </li>
                        <li>
                            <span class="stat-label">Rejected</span>
                            <span class="stat-value"><?= $rejectedApplications ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Internship Status -->
                <div class="analytics-card">
                    <h3>Internship Status</h3>
                    <ul class="stats-list">
                        <li>
                            <span class="stat-label">Open</span>
                            <span class="stat-value"><?= $openInternships ?></span>
                        </li>
                        <li>
                            <span class="stat-label">Closed</span>
                            <span class="stat-value"><?= $totalInternships - $openInternships ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Engagement Metrics -->
                <div class="analytics-card">
                    <h3>Engagement Metrics</h3>
                    <ul class="stats-list">
                        <li>
                            <span class="stat-label">Companies Active</span>
                            <span class="stat-value"><?= $companiesWithInternships ?></span>
                        </li>
                        <li>
                            <span class="stat-label">Interns Applied</span>
                            <span class="stat-value"><?= $internsWithApplications ?></span>
                        </li>
                        <li>
                            <span class="stat-label">Avg Apps/Internship</span>
                            <span class="stat-value"><?= $totalInternships > 0 ? round($totalApplications / $totalInternships, 1) : 0 ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Monthly Trends -->
                <div class="analytics-card">
                    <h3>Monthly Applications</h3>
                    <ul class="stats-list">
                        <?php foreach ($monthlyData as $month => $count): ?>
                        <li>
                            <span class="stat-label"><?= date('M Y', strtotime($month)) ?></span>
                            <span class="stat-value"><?= $count ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Export Section -->
            <div class="export-section">
                <a href="export_reports.php?type=summary" class="btn-export">Export Summary</a>
                <a href="export_reports.php?type=full" class="btn-export">Export Full Report</a>
            </div>
        </div>
    </div>
</div>

        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

</body>
</html>