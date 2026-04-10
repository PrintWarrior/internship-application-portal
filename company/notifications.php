<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];

/* Mark notifications as read when opened */
$pdo->prepare("
UPDATE notifications
SET is_read = 1
WHERE user_id = ?
")->execute([$user_id]);

/* Fetch notifications */
$stmt = $pdo->prepare("
SELECT * FROM notifications
WHERE user_id = ?
ORDER BY created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

/* Count unread */
$stmt = $pdo->prepare("
SELECT COUNT(*) 
FROM notifications 
WHERE user_id=? AND is_read=0
");

$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

if (!function_exists('getType')) {

function getType($message) {

$message = strtolower($message);

if (strpos($message,'applied') !== false) return "application";
if (strpos($message,'offer') !== false) return "offer";
if (strpos($message,'contract') !== false) return "contract";
if (strpos($message,'internship') !== false) return "admin";

return "system";
}

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Company Notifications</title>

<link rel="stylesheet" href="../assets/css/company_notification.css">
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

<h2>Notifications</h2>

<?php if(empty($notifications)): ?>

<p>No notifications available.</p>

<?php else: ?>

<table>

<tr>
<th>Message</th>
<th>Date</th>
<!--<th>Action</th>-->
</tr>

<?php foreach($notifications as $notif): ?>

<tr>

<td>
<?= htmlspecialchars($notif['message']) ?>
</td>

<td>
<?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
</td>

<!--<td>

<?php if(!empty($notif['action_url'])): ?>

<a href="<?= htmlspecialchars($notif['action_url']) ?>">

<?= htmlspecialchars($notif['action_label'] ?? 'View') ?>

</a>

<?php endif; ?>

</td>-->

</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

</div>

</div>


<!-- Logout Modal -->
<?php include '../html/logout_modal.html'; ?>
<script src="../js/logout_modal.js"></script>

</body>
</html>
