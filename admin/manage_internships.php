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

// Get all internships with company info
$stmt = $pdo->query("
    SELECT i.*, c.company_name, c.industry, u.email
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    LEFT JOIN staffs s ON c.company_id = s.company_id
    LEFT JOIN users u ON s.user_id = u.user_id
    GROUP BY i.internship_id
    ORDER BY i.internship_id DESC
");
$internships = $stmt->fetchAll();

// Calculate stats
$total = count($internships);
$pending = count(array_filter($internships, fn($i) => $i['status'] === 'pending'));
$approved = count(array_filter($internships, fn($i) => $i['status'] === 'approved'));
//$closed = count(array_filter($internships, fn($i) => $i['status'] === 'closed'));
$rejected = count(array_filter($internships, fn($i) => $i['status'] === 'rejected'));

// Function to check if deadline expired
function isDeadlineExpired($deadline) {
    return strtotime($deadline) < time();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Internships - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/delete_modal.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
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
        <a href="#" onclick="openLogoutModal()">Logout</a>
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
        <h2>Manage Internships</h2>

        <!-- Stats Cards -->
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
            <!--<div class="stat-card">
                <h3><?= $closed ?></h3>
                <p>Closed</p>
            </div>-->
            <div class="stat-card">
                <h3><?= $rejected ?></h3>
                <p>Rejected</p>
            </div>
        </div>

        <!-- Search Section -->
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

        <!-- Internships Table -->
        <div class="table-container">
            <?php if (count($internships) > 0): ?>
            <table id="internshipsTable">
                <thead>
                    <tr>
                        <!--<th>ID</th>-->
                        <th>Title</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($internships as $job): 
                        $expired = isDeadlineExpired($job['deadline']);
                    ?>
                    <tr data-status="<?= $job['status'] ?>">
                        <!--<td>#<?= $job['internship_id'] ?></td>-->
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
                                <a href="view_internship.php?id=<?= $job['internship_id'] ?>" class="btn-action btn-view">View</a>
                              <!--  <a href="edit_internship.php?id=<?= $job['internship_id'] ?>" class="btn-action btn-edit">Edit</a> -->
                                <a href="#" class="btn-action btn-approve"onclick="openApproveInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>'); return false;">Approve</a>
                                <a href="#" class="btn-action btn-delete"onclick="openRejectInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>'); return false;">Reject</a>
                                <a href="#" class="btn-action btn-delete"onclick="openDeleteInternshipModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>', '<?= htmlspecialchars($job['email']) ?>'); return false;">Delete</a>
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
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    filterTable();
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

// Sort functionality
document.getElementById('sortFilter').addEventListener('change', function() {
    const sortBy = this.value;
    const tbody = document.querySelector('#internshipsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
            case 'oldest':

                const indexA = Array.from(tbody.children).indexOf(a);
                const indexB = Array.from(tbody.children).indexOf(b);
                return sortBy === 'newest' ? indexB - indexA : indexA - indexB;
                
            case 'title_asc':
                // Title is at index 0 (since ID column is commented out)
                const titleA = a.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                const titleB = b.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                return titleA.localeCompare(titleB);
                
            case 'title_desc':
                const titleC = a.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                const titleD = b.cells[0]?.textContent.replace(/<[^>]*>/g, '').trim() || '';
                return titleD.localeCompare(titleC);
                
            default:
                return 0;
        }
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    
    // Re-apply filters after sorting
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
        
        // Fix: Map filter values to actual status values
        let matchesStatus = false;
        if (statusFilter === 'all') {
            matchesStatus = true;
        } else if (statusFilter === 'open') {
            matchesStatus = status === 'approved';
        } else if (statusFilter === 'rejected') {
            matchesStatus = status === 'rejected';
        }else if (statusFilter === 'pending') {
            matchesStatus = status === 'pending';
        }
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}
</script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/delete_modal.html'; ?>
    <?php include '../html/approve_modal.html'; ?>
    <?php include '../html/reject_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/delete_modal.js"></script>
    <script src="../js/approve_modal.js"></script>
    <script src="../js/reject_modal.js"></script>

</body>
</html>
