<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT a.*, i.title, c.company_name, i.deadline
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    WHERE ir.user_id = ?
    ORDER BY a.date_applied DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

// Function to get status class for styling
function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}

/* Count unread */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM notifications 
    WHERE user_id=? AND is_read=0
");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Applications</title>
    <link rel="stylesheet" href="../assets/css/intern_application.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
        <style>
/* Notification badge in sidebar */
.sidebar .badge {
    background-color: #ffffff;
    color: #000000;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 8px;
    border: 1px solid #ffffff;
}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>Intern Panel</h4>
    <a href="index.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="browse_internships.php">Browse Internships</a>
    <a href="my_applications.php">My Applications</a>
    <a href="offers.php">Offers</a>
    <a href="contracts.php">Contracts</a>
    <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
    <a href="#" onclick="openLogoutModal()">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <h2>My Applications</h2>

    <div class="table-container">
        <?php if (count($applications) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Internship</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($app['title']) ?></strong></td>
                    <td><?= htmlspecialchars($app['company_name']) ?></td>
                    <td>
                        <span class="status-badge <?= getStatusClass($app['status']) ?>">
                            <?= htmlspecialchars($app['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($app['application_date'] ?? 'now')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-results">
            No applications found.<br>
            <a href="browse_internships.php" style="color: #000000; text-decoration: underline; margin-top: 15px; display: inline-block;">Browse Internships</a>
        </div>
        <?php endif; ?>
    </div>
</div>
    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
</body>
</html>