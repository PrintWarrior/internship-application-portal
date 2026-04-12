<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get all contracts
$stmt = $pdo->prepare("
    SELECT ct.*, ir.intern_id, ir.first_name, ir.last_name, u.email, i.title, i.start_date, i.end_date
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    WHERE i.company_id = ?
    ORDER BY ct.signed_date DESC
");
$stmt->execute([$company['company_id']]);
$contracts = $stmt->fetchAll();

// Calculate stats
$total = count($contracts);
$confirmed = count(array_filter($contracts, fn($c) => $c['hr_confirmed'] == 1));
$pending = count(array_filter($contracts, fn($c) => $c['hr_confirmed'] == 0));

// Function to format date
function formatDate($date) {
    return $date ? date('M d, Y', strtotime($date)) : 'Not signed';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contracts</title>
    <link rel="stylesheet" href="../assets/css/contract.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <style>
.notification {
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #000000;
    color: #ffffff;
    padding: 15px 25px;
    border: 2px solid #ffffff;
    box-shadow: 5px 5px 0 rgba(0, 0, 0, 0.3);
    z-index: 1001;
    font-weight: bold;
    text-transform: uppercase;
    animation: slideIn 0.3s ease;
    font-size: 1em;
    letter-spacing: 0.5px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
</head>
<body>

<div id="notification" class="notification"></div>

<script>
function showNotification(message, isSuccess = true) {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    notification.textContent = message;
    notification.style.display = 'block';
    
    if (isSuccess) {
        notification.style.backgroundColor = '#000000';
        notification.style.color = '#ffffff';
        notification.style.borderColor = '#ffffff';
    } else {
        notification.style.backgroundColor = '#ffffff';
        notification.style.color = '#000000';
        notification.style.borderColor = '#000000';
    }
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

<?php if (isset($_SESSION['success'])): ?>
    // Show notification
    setTimeout(function() {
        showNotification('<?= addslashes($_SESSION['success']) ?>');
    }, 100);
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
</script>

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
        <h2>Employment Contracts</h2>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3><?= $total ?></h3>
                <p>Total Contracts</p>
            </div>
            <div class="stat-card">
                <h3><?= $confirmed ?></h3>
                <p>Confirmed</p>
            </div>
            <div class="stat-card">
                <h3><?= $pending ?></h3>
                <p>Pending</p>
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="table-container">
            <?php if (count($contracts) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Intern</th>
                        <th>Internship</th>
                        <th>Signed Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($contracts as $contract): ?>
                    <tr>
                        <td>
                            <a href="view_intern.php?id=<?= $contract['intern_id'] ?? '' ?>" class="intern-name">
                                <?= htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($contract['title']) ?></td>
                        <td><?= formatDate($contract['signed_date']) ?></td>
                        <td>
                            <?php if ($contract['hr_confirmed']): ?>
                                <span class="contract-badge contract-confirmed">✓ Confirmed</span>
                            <?php else: ?>
                                <span class="contract-badge contract-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="view_contract.php?id=<?= $contract['contract_id'] ?>" class="btn-action btn-view">View</a>
                              <!--  <a href="download_contract.php?id=<?= $contract['contract_id'] ?>" class="btn-action btn-download">Download</a> -->
                                <?php if (!$contract['hr_confirmed']): ?>
                                <a href="confirm_contract.php?id=<?= $contract['contract_id'] ?>" class="btn-action btn-sign">Confirm</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-results">
                No contracts available yet.<br>
                <a href="view_applicants.php">View Applicants to Create Contracts</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contract Summary -->
        <?php if (count($contracts) > 0): ?>
        <div class="contract-summary">
            <h3>Contract Summary</h3>
            <div class="summary-stats">
                <div class="summary-item">
                    <div class="summary-label">Confirmation Rate</div>
                    <div class="summary-value">
                        <?= $total > 0 ? round(($confirmed / $total) * 100) : 0 ?>%
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Pending Confirmation</div>
                    <div class="summary-value"><?= $pending ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Latest Contract</div>
                    <div class="summary-value">
                        <?= isset($contracts[0]) ? formatDate($contracts[0]['signed_date']) : 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
</body>
</html>
