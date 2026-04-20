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

// Counts
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users");
$countStmt->execute();
$totalUsers = $countStmt->fetchColumn();

$countByTypeStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = ?");
$countByTypeStmt->execute(['admin']);
$totalAdmins = $countByTypeStmt->fetchColumn();
$countByTypeStmt->execute(['staff']);
$totalCompanies = $countByTypeStmt->fetchColumn();
$countByTypeStmt->execute(['intern']);
$totalInterns = $countByTypeStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($panelLabel) ?> Dashboard</title>
    <link rel="stylesheet" href="../assets/css/super_index.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- MAIN WRAPPER -->
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
        <div class="content">
            <h1><?= htmlspecialchars($panelLabel) ?> Dashboard</h1>

            <div class="cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?= $totalUsers ?></p>
                </div>

                <div class="card">
                    <h3>Admins</h3>
                    <p><?= $totalAdmins ?></p>
                </div>

                <div class="card">
                    <h3>Staffs</h3>
                    <p><?= $totalCompanies ?></p>
                </div>

                <div class="card">
                    <h3>Interns</h3>
                    <p><?= $totalInterns ?></p>
                </div>
            </div>
        </div>

    </div>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
