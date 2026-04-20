<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* Mark notifications as read */
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
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

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
<title>Notifications</title>
<link rel="stylesheet" href="../assets/css/intern_notification.css">
<link rel="stylesheet" href="../assets/css/logout_modal.css">
<link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

<div class="wrapper">

<!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="text-center">Intern Panel</h4>
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
<td><?= htmlspecialchars($notif['message']) ?></td>

<td><?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?></td>

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
<script src="../js/responsive-nav.js"></script>
  
</body>
</html>