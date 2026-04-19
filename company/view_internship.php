<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get internship details - verify it belongs to this company
$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.company_id 
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    WHERE i.internship_id = ? AND i.company_id = ?
");
$stmt->execute([$internship_id, $company['company_id']]);
$internship = $stmt->fetch();

if (!$internship) {
    header('Location: manage_internships.php');
    exit;
}

// Get applicant count for this internship
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications 
    WHERE internship_id = ?
");
$stmt->execute([$internship_id]);
$applicant_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Internship - <?= htmlspecialchars($internship['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/company_view_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyinternship_view.css">
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
        <a href="post_internship.php">Post Internship</a>
        <a href="manage_internships.php">My Internships</a>
        <a href="view_applicants.php">View Applicants</a>
        <a href="contracts.php">Contracts</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!--<div class="page-header">
            <h2>Internship Details</h2>
            <a href="manage_internships.php" class="btn-back">Back to List</a>
        </div> -->

        <div class="internship-detail-card">
            <div class="detail-header">
                <h3><?= htmlspecialchars($internship['title']) ?></h3>
                <span class="status-badge <?= strtolower($internship['status']) ?>">
                    <?= htmlspecialchars($internship['status']) ?>
                </span>
            </div>

            <div class="detail-meta">
                <div class="meta-item">
                    <span class="meta-label">Posted:</span>
                    <span class="meta-value"><?= date('F d, Y', strtotime($internship['created_at'])) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Deadline:</span>
                    <span class="meta-value"><?= date('F d, Y', strtotime($internship['deadline'])) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Duration:</span>
                    <span class="meta-value"><?= htmlspecialchars($internship['duration'] ?: 'Not specified') ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Allowance:</span>
                    <span class="meta-value">PHP <?= number_format($internship['allowance'], 2) ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Applicants:</span>
                    <span class="meta-value"><?= $applicant_count ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h4>Description</h4>
                <p><?= nl2br(htmlspecialchars($internship['description'] ?: 'No description provided.')) ?></p>
            </div>

            <div class="detail-section">
                <h4>Requirements</h4>
                <p><?= nl2br(htmlspecialchars($internship['requirements'] ?: 'No requirements specified.')) ?></p>
            </div>

            <!--<div class="detail-actions">
                <a href="edit_internship.php?id=<?= $internship['internship_id'] ?>" class="btn-edit">Edit Internship</a>
                <a href="view_applicants.php?internship_id=<?= $internship['internship_id'] ?>" class="btn-applicants">View Applicants (<?= $applicant_count ?>)</a>
            </div> -->
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
