<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_staff_id'])) {
    $staffId = (int) $_POST['delete_staff_id'];

    $stmt = $pdo->prepare("
        SELECT s.staff_id, s.user_id, s.first_name, s.last_name, s.email, c.company_name
        FROM staffs s
        LEFT JOIN companies c ON s.company_id = c.company_id
        WHERE s.staff_id = ?
        LIMIT 1
    ");
    $stmt->execute([$staffId]);
    $staffToDelete = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staffToDelete) {
        $_SESSION['staff_error'] = 'Staff record not found.';
        header('Location: manage_staffs.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        if (!empty($staffToDelete['user_id'])) {
            $deleteUser = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteUser->execute([$staffToDelete['user_id']]);
        } else {
            $deleteStaff = $pdo->prepare("DELETE FROM staffs WHERE staff_id = ?");
            $deleteStaff->execute([$staffId]);
        }

        $pdo->commit();

        $staffName = trim(($staffToDelete['first_name'] ?? '') . ' ' . ($staffToDelete['last_name'] ?? ''));
        if ($staffName === '') {
            $staffName = $staffToDelete['email'] ?: 'Staff member';
        }

        logAction('Delete Staff', "Deleted staff ID {$staffId} ({$staffName})");
        $_SESSION['staff_success'] = $staffName . ' was deleted successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['staff_error'] = 'Unable to delete the selected staff member right now.';
    }

    header('Location: manage_staffs.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT
        s.staff_id,
        s.user_id,
        s.company_id,
        s.first_name,
        s.last_name,
        s.email AS staff_email,
        s.contact_no,
        s.position,
        s.profile_image,
        s.created_at AS staff_created_at,
        c.company_name,
        c.industry,
        u.email AS user_email,
        u.status AS account_status,
        u.verified
    FROM staffs s
    LEFT JOIN companies c ON s.company_id = c.company_id
    LEFT JOIN users u ON s.user_id = u.user_id
    ORDER BY s.created_at DESC, s.staff_id DESC
");
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStaffs = count($staffs);
$activeStaffs = count(array_filter($staffs, static function ($staff) {
    return ($staff['account_status'] ?? 'active') === 'active';
}));
$companyCount = count(array_unique(array_filter(array_column($staffs, 'company_id'))));

$selectedStaff = null;
$viewStaffId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
if ($viewStaffId > 0) {
    foreach ($staffs as $staff) {
        if ((int) $staff['staff_id'] === $viewStaffId) {
            $selectedStaff = $staff;
            break;
        }
    }
}

if (!$selectedStaff && !empty($staffs)) {
    $selectedStaff = $staffs[0];
}

function formatDisplayDate(?string $date): string
{
    if (empty($date)) {
        return 'Not available';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'Not available';
    }

    return date('M d, Y', $timestamp);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staffs - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_company.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/adminstaffs.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="topnav">
    <div class="logo-section">
        <img src="../assets/img/logo.png" alt="Logo">
        <h4>Internship Portal - Admin</h4>
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

    <div class="main-content">
        <h2>Manage Staffs</h2>

        <?php if (isset($_SESSION['staff_success'])): ?>
            <div class="alert-box alert-success"><?= htmlspecialchars($_SESSION['staff_success']) ?></div>
            <?php unset($_SESSION['staff_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['staff_error'])): ?>
            <div class="alert-box alert-error"><?= htmlspecialchars($_SESSION['staff_error']) ?></div>
            <?php unset($_SESSION['staff_error']); ?>
        <?php endif; ?>

        <div class="stats-cards">
            <div class="stat-card">
                <h3><?= $totalStaffs ?></h3>
                <p>Total Staffs</p>
            </div>
            <div class="stat-card">
                <h3><?= $activeStaffs ?></h3>
                <p>Active Accounts</p>
            </div>
            <div class="stat-card">
                <h3><?= $companyCount ?></h3>
                <p>Companies Represented</p>
            </div>
        </div>

        <?php if ($selectedStaff): ?>
            <?php
            $profileImage = '../assets/img/profile/' . ($selectedStaff['profile_image'] ?: 'default.png');
            if (!file_exists($profileImage)) {
                $profileImage = '../assets/img/profile/default.png';
            }

            $fullName = trim(($selectedStaff['first_name'] ?? '') . ' ' . ($selectedStaff['last_name'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Unnamed Staff';
            }

            $accountStatus = $selectedStaff['account_status'] ?? 'inactive';
            $statusClass = $accountStatus === 'active' ? 'status-active' : 'status-inactive';
            ?>
            <div class="staff-overview">
                <div>
                    <img src="<?= htmlspecialchars($profileImage) ?>" alt="Staff Profile" class="staff-photo">
                </div>
                <div>
                    <h3><?= htmlspecialchars($fullName) ?></h3>
                    <div class="staff-company-row">
                        <p class="staff-subtitle">
                            <?= htmlspecialchars($selectedStaff['company_name'] ?: 'No company assigned') ?>
                        </p>
                        <?php if (!empty($selectedStaff['company_id'])): ?>
                            <a href="view_company.php?id=<?= (int) $selectedStaff['company_id'] ?>" class="company-view-link">View</a>
                        <?php endif; ?>
                    </div>

                    <div class="staff-detail-grid">
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Position</span>
                            <span class="staff-detail-value"><?= htmlspecialchars($selectedStaff['position'] ?: 'Not provided') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Login Email</span>
                            <span class="staff-detail-value"><?= htmlspecialchars($selectedStaff['user_email'] ?: 'No linked user') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Staff Email</span>
                            <span class="staff-detail-value"><?= htmlspecialchars($selectedStaff['staff_email'] ?: 'Not provided') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Contact Number</span>
                            <span class="staff-detail-value"><?= htmlspecialchars($selectedStaff['contact_no'] ?: 'Not provided') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Industry</span>
                            <span class="staff-detail-value"><?= htmlspecialchars($selectedStaff['industry'] ?: 'Not specified') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Joined</span>
                            <span class="staff-detail-value"><?= htmlspecialchars(formatDisplayDate($selectedStaff['staff_created_at'])) ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Account Status</span>
                            <span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($accountStatus ?: 'inactive') ?></span>
                        </div>
                        <div class="staff-detail-item">
                            <span class="staff-detail-label">Verified</span>
                            <span class="staff-detail-value"><?= !empty($selectedStaff['verified']) ? 'Yes' : 'No' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="search-section">
            <input type="text" class="search-input" id="searchInput" placeholder="Search by name, company, email, or position...">
            <select class="filter-select" id="sortFilter">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name_asc">Name A-Z</option>
                <option value="name_desc">Name Z-A</option>
            </select>
        </div>

        <div class="table-container">
            <?php if ($totalStaffs > 0): ?>
                <table id="staffsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Login Email</th>
                            <th>Contact</th>
                            <th>Position</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffs as $staff): ?>
                            <?php
                            $staffName = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
                            if ($staffName === '') {
                                $staffName = 'Unnamed Staff';
                            }
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($staffName) ?></strong></td>
                                <td><?= htmlspecialchars($staff['company_name'] ?: 'No company assigned') ?></td>
                                <td><?= htmlspecialchars($staff['user_email'] ?: ($staff['staff_email'] ?: 'Not provided')) ?></td>
                                <td><?= htmlspecialchars($staff['contact_no'] ?: 'Not provided') ?></td>
                                <td><?= htmlspecialchars($staff['position'] ?: 'Not provided') ?></td>
                                <td><?= htmlspecialchars(formatDisplayDate($staff['staff_created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage_staffs.php?view=<?= $staff['staff_id'] ?>" class="btn-action btn-view">View</a>
                                        <form method="POST" class="btn-delete-form" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($staffName), ENT_QUOTES) ?>? This will also remove the linked staff login account.');">
                                            <input type="hidden" name="delete_staff_id" value="<?= $staff['staff_id'] ?>">
                                            <button type="submit" class="btn-action btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-panel">No staff members found in the system.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const sortFilter = document.getElementById('sortFilter');
    const staffsTable = document.getElementById('staffsTable');

    if (searchInput && staffsTable) {
        searchInput.addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase().trim();
            const rows = staffsTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchText) ? '' : 'none';
            });
        });
    }

    if (sortFilter && staffsTable) {
        sortFilter.addEventListener('change', function () {
            const tbody = staffsTable.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const sortBy = this.value;

            rows.sort((a, b) => {
                if (sortBy === 'newest' || sortBy === 'oldest') {
                    const dateA = new Date(a.cells[5]?.textContent || '');
                    const dateB = new Date(b.cells[5]?.textContent || '');
                    return sortBy === 'newest' ? dateB - dateA : dateA - dateB;
                }

                const nameA = a.cells[0]?.textContent.trim() || '';
                const nameB = b.cells[0]?.textContent.trim() || '';
                return sortBy === 'name_desc' ? nameB.localeCompare(nameA) : nameA.localeCompare(nameB);
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    }
</script>

<?php include '../html/logout_modal.html'; ?>
<script src="../js/logout_modal.js"></script>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
