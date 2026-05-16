<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$itemsPerPage = 9;
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]) ?: 1;

// Get internship stats
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
    FROM internships
");
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$total = (int) ($stats['total'] ?? 0);
$pending = (int) ($stats['pending'] ?? 0);
$approved = (int) ($stats['approved'] ?? 0);
$rejected = (int) ($stats['rejected'] ?? 0);

$totalPages = max(1, (int) ceil($total / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;

// Get paginated internships with company info
$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.industry, u.email
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    LEFT JOIN staffs s ON c.company_id = s.company_id
    LEFT JOIN users u ON s.user_id = u.user_id
    GROUP BY i.internship_id
    ORDER BY i.internship_id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$internships = $stmt->fetchAll();

$pageWindow = 2;
$startPage = max(1, $currentPage - $pageWindow);
$endPage = min($totalPages, $currentPage + $pageWindow);

function isDeadlineExpired($deadline)
{
    return strtotime($deadline) < time();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Internships - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/admin_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/delete_modal.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h4>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h4>
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
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="create_users.php">Create Users</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_internships.php">Manage Internships</a></li>
            <li><a href="applications.php">All Applications</a></li>
            <li><a href="appeals.php">Appeals</a></li>
            <li><a href="system_logs.php">System Logs</a></li>
            <li><a href="about.php">About</a></li>
        </div>

        <div class="main-content">
            <h2>Manage Internships</h2>

            <div class="stats-cards">
                <div class="stat-card">
                    <h3><?= $total ?></h3>
                    <p>Total</p>
                </div>
                <div class="stat-card">
                    <h3><?= $pending ?></h3>
                    <p>Pending</p>
                </div>
                <div class="stat-card">
                    <h3><?= $approved ?></h3>
                    <p>Approved</p>
                </div>
                <div class="stat-card">
                    <h3><?= $rejected ?></h3>
                    <p>Rejected</p>
                </div>
            </div>

            <div class="search-section">
                <input type="text" class="search-input" id="searchInput" placeholder="Search by title or company...">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="open">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="title_asc">Title A-Z</option>
                    <option value="title_desc">Title Z-A</option>
                </select>
            </div>

            <div class="table-container">
                <?php if (count($internships) > 0): ?>
                    <table id="internshipsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($internships as $job): ?>
                                <?php $expired = isDeadlineExpired($job['deadline']); ?>
                                <tr data-status="<?= $job['status'] ?>">
                                    <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                    <td>
                                        <span class="company-badge"><?= htmlspecialchars($job['company_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $job['status'] ?>">
                                            <?= ucfirst($job['status']) ?>
                                        </span>
                                    </td>
                                    <td class="deadline <?= $expired ? 'deadline-expired' : '' ?>">
                                        <?= date('M d, Y', strtotime($job['deadline'])) ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_internship.php?id=<?= $job['internship_id'] ?>"
                                                class="btn-action btn-view">View</a>
                                            <a href="#" class="btn-action btn-approve"
                                                onclick="openApproveInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>'); return false;">Approve</a>
                                            <a href="#" class="btn-action btn-delete"
                                                onclick="openRejectInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>'); return false;">Reject</a>
                                            <a href="#" class="btn-action btn-delete"
                                                onclick="openDeleteInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>', '<?= htmlspecialchars($job['email']) ?>'); return false;">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        No internships found.
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Internships pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="pagination-btn" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                    <?php endif; ?>

                    <div class="pagination-numbers">
                        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                            <a class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>" href="?page=<?= $page ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>>
                                <?= $page ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($currentPage < $totalPages): ?>
                        <a class="pagination-btn" href="?page=<?= $currentPage + 1 ?>">Next</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            filterTable();
        });

        document.getElementById('statusFilter').addEventListener('change', function () {
            filterTable();
        });

        document.getElementById('sortFilter').addEventListener('change', function () {
            const sortBy = this.value;
            const tbody = document.querySelector('#internshipsTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                switch (sortBy) {
                    case 'newest':
                    case 'oldest':
                        const indexA = Array.from(tbody.children).indexOf(a);
                        const indexB = Array.from(tbody.children).indexOf(b);
                        return sortBy === 'newest' ? indexB - indexA : indexA - indexB;

                    case 'title_asc':
                        const titleA = a.cells[0]?.textContent.trim() || '';
                        const titleB = b.cells[0]?.textContent.trim() || '';
                        return titleA.localeCompare(titleB);

                    case 'title_desc':
                        const titleC = a.cells[0]?.textContent.trim() || '';
                        const titleD = b.cells[0]?.textContent.trim() || '';
                        return titleD.localeCompare(titleC);

                    default:
                        return 0;
                }
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
            filterTable();
        });

        function filterTable() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#internshipsTable tbody tr');

            rows.forEach(row => {
                const title = row.cells[0]?.textContent.toLowerCase() || '';
                const company = row.cells[1]?.textContent.toLowerCase() || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = title.includes(searchText) || company.includes(searchText);

                let matchesStatus = false;
                if (statusFilter === 'all') {
                    matchesStatus = true;
                } else if (statusFilter === 'open') {
                    matchesStatus = status === 'approved';
                } else if (statusFilter === 'rejected') {
                    matchesStatus = status === 'rejected';
                } else if (statusFilter === 'pending') {
                    matchesStatus = status === 'pending';
                }

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }
    </script>

    <?php include '../html/logout_modal.html'; ?>
    <?php include '../html/delete_modal.html'; ?>
    <?php include '../html/approve_modal.html'; ?>
    <?php include '../html/reject_modal.html'; ?>

    <script src="../js/logout_modal.js"></script>
    <script src="../js/delete_modal.js"></script>
    <script src="../js/approve_modal.js"></script>
    <script src="../js/reject_modal.js"></script>

    <script src="../js/responsive-nav.js"></script>
</body>

</html>
