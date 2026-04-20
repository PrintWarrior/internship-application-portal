<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();

$user_id = $_SESSION['user_id'];
$panelLabel = getAdminAreaLabel();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

$developerName = 'Portal Development Team';
$developerRole = 'Development Team';
$developerEmail = 'support@example.com';
$developerLocation = 'Philippines';
$developerAbout = 'This portal is maintained by the Internship Portal development team.';
$profileImage = '../assets/img/profile/developer.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - <?= htmlspecialchars($panelLabel) ?> Panel</title>
    <link rel="stylesheet" href="../assets/css/admin_about.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
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
            <a href="#" onclick="openLogoutModal()">Logout</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
                <li><a href="about.php" class="active">About</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="about-container">
                <div class="about-header"></div>

                <div class="profile-section">
                    <div class="profile-image-wrapper">
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Developer Profile Picture" class="profile-image" id="profileImage">
                    </div>

                    <div class="profile-info">
                        <h2>Xavier Ace Clark S. Azcona</h2>
                        <p>Developer</p>

                        <div class="info-details">
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <p>internshipapplicationportal@gmail.com</p>
                            </div>

                            <div class="detail-item">
                                <span class="detail-label">Location:</span>
                                <p>Oroquieta City, Misamis Occidental, Philippines</p>
                            </div>

                            <h3>About The Developer</h3>
                            <p>I am Xavier Ace Clark S. Azcona, a student of Northwestern Mindanao State College of Science and Technology, currently taking up Bachelor of Science in Information Technology.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../html/logout_modal.html'; ?>
    <script src="../js/logout_modal.js"></script>
    <script src="../js/responsive-nav.js"></script>
</body>
</html>
