<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Unread notifications
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Dashboard Stats
$totalInterns = $pdo->query("SELECT COUNT(*) FROM interns")->fetchColumn();
$totalCompanies = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalInternships = $pdo->query("SELECT COUNT(*) FROM internships")->fetchColumn();
$totalApplications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_index.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>

<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <!-- Replace logo.png with your actual logo -->
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - Admin</h2>
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
        <div class="content">
            <h2>Dashboard Overview</h2>

            <div class="cards">
                <div class="card">
                    <h3><?= $totalInterns ?></h3>
                    <p>Total Interns</p>
                </div>

                <div class="card">
                    <h3><?= $totalCompanies ?></h3>
                    <p>Total Staffs</p>
                </div>

                <div class="card">
                    <h3><?= $totalInternships ?></h3>
                    <p>Total Internships</p>
                </div>

                <div class="card">
                    <h3><?= $totalApplications ?></h3>
                    <p>Total Applications</p>
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
