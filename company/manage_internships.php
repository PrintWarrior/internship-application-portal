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

// Get internships
$stmt = $pdo->prepare("
    SELECT i.* FROM internships i
    WHERE i.company_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$company['company_id']]);
$internships = $stmt->fetchAll();

// Calculate stats
$total = count($internships);
$open = count(array_filter($internships, fn($i) => $i['status'] === 'open'));
$closed = count(array_filter($internships, fn($i) => $i['status'] === 'closed'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Internships</title>
    <link rel="stylesheet" href="../assets/css/company_manage.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
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
            <a href="generate_application_report.php">Reports</a>
            <a href="contracts.php">Contracts</a>
        </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>My Internships</h2>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3><?= $total ?></h3>
                <p>Total</p>
            </div>
            <!--<div class="stat-card">
                <h3><?= $open ?></h3>
                <p>Open</p>
            </div>
            <div class="stat-card">
                <h3><?= $closed ?></h3>
                <p>Closed</p>
            </div>-->
        </div>

        <!-- Internships Table -->
        <div class="table-container">
            <?php if (count($internships) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($internships as $job): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                        <td>
                            <span class="status-badge <?= strtolower($job['status']) ?>">
                                <?= htmlspecialchars($job['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($job['deadline'])) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="view_internship.php?id=<?= $job['internship_id'] ?>" class="btn-action btn-view">View</a>
                                <a href="edit_internship.php?id=<?= $job['internship_id'] ?>" class="btn-action btn-edit">Edit</a>
                                <a href="view_applicants.php?internship_id=<?= $job['internship_id'] ?>" class="btn-action btn-view">Applicants</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                No internships posted yet.<br>
                <a href="post_internship.php">Post Your First Internship</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
    <script src="../js/responsive-nav.js"></script>
</body>
</html>
