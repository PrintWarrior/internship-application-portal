<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT intern_id FROM interns WHERE user_id=?");
$stmt->execute([$user_id]);
$intern_id = $stmt->fetchColumn();

// Apply logic
if (isset($_POST['apply'])) {

    $internship_id = $_POST['internship_id'];

    $checkStmt = $pdo->prepare("
        SELECT application_id 
        FROM applications 
        WHERE intern_id = ? AND internship_id = ?
    ");

    $checkStmt->execute([$intern_id, $internship_id]);

    if (!$checkStmt->fetch()) {

        $stmt = $pdo->prepare("
            INSERT INTO applications (intern_id, internship_id, status, date_applied)
            VALUES (?, ?, 'Pending', NOW())
        ");

        $stmt->execute([$intern_id, $internship_id]);

        // Get company for notifications
        $stmt = $pdo->prepare("
            SELECT i.company_id, i.title
            FROM internships i
            WHERE i.internship_id = ?
        ");
        $stmt->execute([$internship_id]);
        $data = $stmt->fetch();

        $message = "A new intern applied for your internship: " . $data['title'];
        notifyCompanyStaff($data['company_id'], $message, "applications.php", "View Application");

        // Redirect with success parameter
        header("Location: browse_internships.php?apply_success=1");
        exit;
    } else {
        // Redirect with already applied parameter
        header("Location: browse_internships.php?already_applied=1");
        exit;
    }
}


$stmt = $pdo->query("
    SELECT i.*, c.company_name 
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    WHERE i.status = 'approved'
    ORDER BY i.deadline ASC
");
$internships = $stmt->fetchAll();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Internships</title>
    <link rel="stylesheet" href="../assets/css/intern_browse.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/internapply_modal.css">
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
    <link rel="stylesheet" href="../assets/css/responsive.css">
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
        <h2>Browse Internships</h2>

        <div class="table-container">
            <?php if (count($internships) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Deadline</th>
                            <th>Allowance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($internships as $job): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                <td><?= htmlspecialchars($job['company_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($job['deadline'])) ?></td>
                                <td>PHP <?= number_format($job['allowance'], 2) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="internship_id" value="<?= $job['internship_id'] ?>">
                                        <button type="submit" name="apply">Apply</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    No internships available at the moment.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/internapply_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/internapply_modal.js"></script>

    <!-- Check for success messages -->
    <?php if (isset($_GET['apply_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Application Submitted Successfully');
                    }
                }, 100);
            });
        </script>
    <?php elseif (isset($_GET['already_applied'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('You have already applied for this internship', false);
                    }
                }, 100);
            });
        </script>
    <?php endif; ?>
    <script src="../js/responsive-nav.js"></script>
</body>

</html>
