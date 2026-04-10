<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Application ID missing.");
}

$application_id = $_GET['id'];

// Fetch application details
$stmt = $pdo->prepare("
    SELECT a.*,
           i.title,
           i.description,
           c.company_name,
           ir.first_name,
           ir.last_name,
           u.email
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN users u ON ir.user_id = u.user_id
    WHERE a.application_id = ?
");
$stmt->execute([$application_id]);
$app = $stmt->fetch();

if (!$app) {
    die("Application not found.");
}

// Function to get status class
function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Application - Admin</title>
    <link rel="stylesheet" href="../assets/css/adminapplication_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>

<div class="wrapper">
    <div class="main-content">
        <h2>Application Details</h2>

        <div class="detail-card">
            <p>
                <strong>Intern:</strong> 
                <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
            </p>

            <p>
                <strong>Email:</strong> 
                <a href="mailto:<?= htmlspecialchars($app['email']) ?>" style="color: #000000; text-decoration: underline;">
                    <?= htmlspecialchars($app['email']) ?>
                </a>
            </p>

            <p>
                <strong>Internship:</strong> 
                <?= htmlspecialchars($app['title']) ?>
                <span class="company-badge"><?= htmlspecialchars($app['company_name']) ?></span>
            </p>

            <p>
                <strong>Company:</strong> 
                <?= htmlspecialchars($app['company_name']) ?>
            </p>

            <p>
                <strong>Date Applied:</strong> 
                <span class="date-badge"><?= date('F d, Y', strtotime($app['date_applied'])) ?></span>
            </p>

            <p>
                <strong>Status:</strong> 
                <span class="status-badge <?= getStatusClass($app['status']) ?>">
                    <?= htmlspecialchars($app['status']) ?>
                </span>
            </p>

            <p><strong>Internship Description:</strong></p>

            <div class="description-box">
                <?= nl2br(htmlspecialchars($app['description'])) ?>
            </div>

            <div class="action-buttons">
                <a href="applications.php" class="btn-action btn-view">Back to Applications</a>
                
                <?php if ($app['status'] === 'Pending'): ?>
                    <a href="update_application.php?id=<?= $application_id ?>&status=Reviewed" class="btn-action btn-view" style="background-color: #ffffff; color: #000000;">Mark Reviewed</a>
                    <a href="update_application.php?id=<?= $application_id ?>&status=Rejected" class="btn-action btn-view" style="background-color: #ffffff; color: #000000;" onclick="return confirm('Reject this application?')">Reject</a>
                <?php elseif ($app['status'] === 'Reviewed'): ?>
                    <a href="update_application.php?id=<?= $application_id ?>&status=Accepted" class="btn-action btn-view" style="background-color: #ffffff; color: #000000;">Accept</a>
                    <a href="update_application.php?id=<?= $application_id ?>&status=Rejected" class="btn-action btn-view" style="background-color: #ffffff; color: #000000;" onclick="return confirm('Reject this application?')">Reject</a>
                <?php elseif ($app['status'] === 'Accepted'): ?>
                   <!-- <a href="create_offer.php?id=<?= $application_id ?>" class="btn-action btn-view" style="background-color: #ffffff; color: #000000;">Create Offer</a> -->
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>