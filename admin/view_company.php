<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT c.*, u.email, u.created_at, s.first_name, s.last_name,
           addr.address_line AS address, addr.city, addr.province, addr.postal_code, addr.country
    FROM companies c
    LEFT JOIN staffs s ON c.company_id = s.company_id
    LEFT JOIN users u ON s.user_id = u.user_id
    LEFT JOIN addresses addr
        ON addr.entity_id = c.company_id
        AND addr.entity_type = 'company'
        AND addr.is_primary = 1
    WHERE c.company_id = ?
    ORDER BY s.staff_id ASC
    LIMIT 1
");
$stmt->execute([$id]);

$company = $stmt->fetch();

if (!$company) {
    die("Company not found.");
}

$profile = !empty($company['profile_image'])
    ? "../assets/img/profile/" . $company['profile_image']
    : "../assets/img/profile/default.png";
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Company - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_company.css">
    <link rel="stylesheet" href="../assets/css/admincompany_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<!-- TOP NAV -->
<div class="topnav">
    <div class="logo-section">
        <img src="../assets/img/logo.png" alt="Logo">
        <h4>Internship Portal - Admin</h4>
    </div>

    <div class="topnav-right">
        <a href="notifications.php">
            Notifications <span class="badge"><?= $unread ?></span>
        </a>
        <a href="../logout.php">Logout</a>
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
    <div class="main-content">
        <!--<div class="page-header">
            <h2>Company Details</h2>
            <a href="manage_companies.php" class="back-button">Back to Companies</a>
        </div>-->

        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image-section">
                    <img src="<?= $profile ?>" alt="Company Logo" class="profile-image">
                </div>
                
                <div class="company-title-section">
                    <h1 class="company-name-large"><?= htmlspecialchars($company['company_name']) ?></h1>
                    <div class="company-meta">
                        <?php if (!empty($company['industry'])): ?>
                        <span class="meta-item"><?= htmlspecialchars($company['industry']) ?></span>
                        <?php endif; ?>
                        <!--<span class="meta-item">ID: #<?= $company['company_id'] ?></span>-->
                    </div>
                </div>
            </div>

            <!-- Profile Grid -->
            <div class="profile-grid">
                <!-- Contact Information -->
                <div class="profile-section">
                    <h3>Contact Information</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">
                            <a href="mailto:<?= htmlspecialchars($company['email']) ?>" class="website-link">
                                <?= htmlspecialchars($company['email']) ?>
                            </a>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['contact_phone'] ?? 'Not provided') ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Website:</span>
                        <span class="detail-value">
                            <?php if (!empty($company['website'])): ?>
                            <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" class="website-link">
                                <?= htmlspecialchars($company['website']) ?>
                            </a>
                            <?php else: ?>
                            Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="profile-section">
                    <h3>Address</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Street:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['address'] ?? 'Not provided') ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">City:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['city'] ?? 'Not provided') ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Province:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['province'] ?? 'Not provided') ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Country:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['country'] ?? 'Not provided') ?></span>
                    </div>
                    
                    <?php if (!empty($company['postal_code'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Postal Code:</span>
                        <span class="detail-value"><?= htmlspecialchars($company['postal_code']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Account Information -->
                <div class="profile-section">
                    <h3>Account Information</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Joined:</span>
                        <span class="detail-value">
                            <span class="joined-badge"><?= date('M d, Y', strtotime($company['created_at'])) ?></span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Contact Person:</span>
                        <span class="detail-value"><?= htmlspecialchars(trim(($company['first_name'] ?? '') . ' ' . ($company['last_name'] ?? '')) ?: ($company['contact_person'] ?? 'Not specified')) ?></span>
                    </div>
                </div>

                <!-- Company Description -->
                <?php if (!empty($company['description'])): ?>
                <div class="profile-section">
                    <h3>About the Company</h3>
                    <div class="description-box">
                        <?= nl2br(htmlspecialchars($company['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons
            <div class="action-buttons">
                <a href="edit_company.php?id=<?= $company['company_id'] ?>" class="btn-action btn-edit">Edit Company</a>
                <a href="delete_company.php?id=<?= $company['company_id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this company? This action cannot be undone.')">Delete Company</a>
            </div> -->
        </div>
    </div>
</div>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
