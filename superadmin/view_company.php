<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

$companyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($companyId <= 0) {
    header('Location: manage_users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

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
$stmt->execute([$companyId]);
$company = $stmt->fetch();

if (!$company) {
    header('Location: manage_users.php');
    exit;
}

$profile = !empty($company['profile_image'])
    ? '../assets/img/profile/' . $company['profile_image']
    : '../assets/img/profile/default.png';

if (!file_exists($profile)) {
    $profile = '../assets/img/profile/default.png';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Company - Super Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_company.css">
    <link rel="stylesheet" href="../assets/css/admincompany_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>

<div class="topnav">
    <div class="logo-section">
        <img src="../assets/img/logo.png" alt="Logo">
        <h4>Internship Portal - Super Admin</h4>
    </div>

    <div class="topnav-right">
        <a href="notifications.php">
            Notifications <span class="badge"><?= $unread ?></span>
        </a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="wrapper">
    <div class="sidebar">
        <a href="index.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="create_users.php">Create Users</a>
        <a href="manage_internships.php">Manage Internships</a>
        <a href="system_logs.php">System Logs</a>
    </div>

    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-image-section">
                    <img src="<?= htmlspecialchars($profile) ?>" alt="Company Logo" class="profile-image">
                </div>

                <div class="company-title-section">
                    <h1 class="company-name-large"><?= htmlspecialchars($company['company_name']) ?></h1>
                    <div class="company-meta">
                        <?php if (!empty($company['industry'])): ?>
                            <span class="meta-item"><?= htmlspecialchars($company['industry']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <div class="profile-section">
                    <h3>Contact Information</h3>

                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">
                            <a href="mailto:<?= htmlspecialchars($company['contact_email'] ?: ($company['email'] ?? '')) ?>" class="website-link">
                                <?= htmlspecialchars($company['contact_email'] ?: ($company['email'] ?? 'Not provided')) ?>
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

                <?php if (!empty($company['description'])): ?>
                    <div class="profile-section">
                        <h3>About the Company</h3>
                        <div class="description-box">
                            <?= nl2br(htmlspecialchars($company['description'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
