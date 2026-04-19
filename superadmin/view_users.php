<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($userId <= 0) {
    header('Location: manage_users.php');
    exit;
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Fetch selected user
$stmt = $pdo->prepare("
    SELECT
        u.*,
        i.first_name AS intern_first_name,
        i.middle_name AS intern_middle_name,
        i.last_name AS intern_last_name,
        i.suffix AS intern_suffix,
        i.profile_image AS intern_profile_image,
        s.first_name AS staff_first_name,
        s.last_name AS staff_last_name,
        s.email AS staff_email,
        s.position AS staff_position,
        s.profile_image AS staff_profile_image,
        s.company_id AS staff_company_id,
        c.company_name AS staff_company_name,
        ap.admin_fname AS admin_first_name,
        ap.admin_lname AS admin_last_name,
        ap.profile_image AS admin_profile_image
    FROM users u
    LEFT JOIN interns i ON i.user_id = u.user_id
    LEFT JOIN staffs s ON s.user_id = u.user_id
    LEFT JOIN companies c ON c.company_id = s.company_id
    LEFT JOIN admin_profiles ap ON ap.user_id = u.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: manage_users.php');
    exit;
}

// Function to get status class
function getStatusClass($status) {
    return $status === 'active' ? 'status-active' : 'status-inactive';
}

// Function to get user type class
function getUserTypeClass($type) {
    return 'user-type-' . strtolower($type);
}

function formatStatusLabel($status) {
    return match ($status) {
        'active' => 'Active',
        'banned' => 'Banned',
        'suspended' => 'Suspended',
        default => ucfirst($status),
    };
}

function buildFullName(array $user): string {
    if ($user['user_type'] === 'intern') {
        $parts = [
            trim((string) ($user['intern_first_name'] ?? '')),
            trim((string) ($user['intern_middle_name'] ?? '')),
            trim((string) ($user['intern_last_name'] ?? '')),
            trim((string) ($user['intern_suffix'] ?? '')),
        ];

        return trim(implode(' ', array_filter($parts, fn($part) => $part !== '')));
    }

    if ($user['user_type'] === 'staff') {
        $parts = [
            trim((string) ($user['staff_first_name'] ?? '')),
            trim((string) ($user['staff_last_name'] ?? '')),
        ];

        return trim(implode(' ', array_filter($parts, fn($part) => $part !== '')));
    }

    if ($user['user_type'] === 'admin') {
        $parts = [
            trim((string) ($user['admin_first_name'] ?? '')),
            trim((string) ($user['admin_last_name'] ?? '')),
        ];

        return trim(implode(' ', array_filter($parts, fn($part) => $part !== '')));
    }

    return '';
}

function getProfileImageName(array $user): string {
    if ($user['user_type'] === 'intern' && !empty($user['intern_profile_image'])) {
        return (string) $user['intern_profile_image'];
    }

    if ($user['user_type'] === 'staff' && !empty($user['staff_profile_image'])) {
        return (string) $user['staff_profile_image'];
    }

    if ($user['user_type'] === 'admin' && !empty($user['admin_profile_image'])) {
        return (string) $user['admin_profile_image'];
    }

    return 'default.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/super_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">
                Notifications 
                <span class="badge"><?= $unread ?></span>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- MAIN WRAPPER -->
    <div class="wrapper">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content">
            <h1>View User</h1>

            <?php
            $statusClass = getStatusClass($user['status']);
            $userTypeClass = getUserTypeClass($user['user_type']);
            $fullName = buildFullName($user);
            $displayName = $fullName !== '' ? $fullName : $user['email'];
            $displayEmail = $user['user_type'] === 'staff' && !empty($user['staff_email'])
                ? $user['staff_email']
                : $user['email'];
            $profileImage = getProfileImageName($user);
            $imagePath = '../assets/img/profile/' . $profileImage;

            if (!file_exists($imagePath)) {
                $imagePath = '../assets/img/profile/default.png';
            }
            ?>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-identity">
                        <div class="profile-image-shell">
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="Profile image of <?= htmlspecialchars($displayName) ?>" class="profile-image">
                        </div>
                        <div>
                            <p class="eyebrow">User Details</p>
                            <h2><?= htmlspecialchars($displayName) ?></h2>
                            <?php if ($user['user_type'] === 'staff' && !empty($user['staff_company_name'])): ?>
                                <div class="profile-subtitle-row">
                                    <p class="profile-subtitle"><?= htmlspecialchars($user['staff_company_name']) ?></p>
                                    <a href="view_company.php?id=<?= (int) $user['staff_company_id'] ?>" class="profile-link-button">View</a>
                                </div>
                            <?php else: ?>
                                <p class="profile-subtitle"><?= htmlspecialchars($displayEmail) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="profile-badges">
                        <span class="user-type-badge <?= $userTypeClass ?>">
                            <?= ucfirst($user['user_type']) ?>
                        </span>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars(formatStatusLabel($user['status'])) ?>
                        </span>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value"><?= htmlspecialchars($fullName !== '' ? $fullName : 'Not available') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($displayEmail) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">User Type</span>
                        <span class="detail-value"><?= ucfirst($user['user_type']) ?></span>
                    </div>
                    <?php if ($user['user_type'] === 'staff'): ?>
                        <div class="detail-item">
                            <span class="detail-label">Company</span>
                            <span class="detail-value"><?= htmlspecialchars($user['staff_company_name'] ?: 'Not available') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Position</span>
                            <span class="detail-value"><?= htmlspecialchars($user['staff_position'] ?: 'Not available') ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><?= htmlspecialchars(formatStatusLabel($user['status'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Verified</span>
                        <span class="detail-value"><?= !empty($user['verified']) ? 'Yes' : 'No' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Joined</span>
                        <span class="detail-value"><?= date('F d, Y h:i A', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value"><?= date('F d, Y h:i A', strtotime($user['updated_at'])) ?></span>
                    </div>
                </div>

                <div class="action-row">
                    <a href="manage_users.php" class="btn-action">Back to Manage Users</a>
                    <?php if ($user['user_type'] !== 'superadmin'): ?>
                        <a href="toggle_status.php?id=<?= $user['user_id'] ?>"
                           class="btn-action btn-secondary"
                           onclick="return confirm('Change status for <?= htmlspecialchars($user['email']) ?>?')">
                            <?= $user['status'] === 'active' ? 'Ban/Suspend User' : 'Activate User' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
