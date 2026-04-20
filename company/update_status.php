<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get application details and verify it belongs to this company
$stmt = $pdo->prepare("
    SELECT a.*, i.title, i.internship_id, 
           ir.first_name, ir.last_name, ir.intern_id,
           u.email, c.contract_id
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    LEFT JOIN contracts c ON a.application_id = c.application_id
    WHERE a.application_id = ? AND i.company_id = ?
");
$stmt->execute([$application_id, $company['company_id']]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: view_applicants.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'update_status.php?id=' . $application_id]);
    $new_status = $_POST['status'];
    $old_status = $application['status'];
    
    // Update application status
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
    $stmt->execute([$new_status, $application_id]);
    
    // Create notification for the intern
    $notification_message = "Your application for '" . $application['title'] . "' has been updated to: " . $new_status;
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, link) 
        VALUES (?, ?, ?)
    ");
    
    // Get intern's user_id
    $stmt2 = $pdo->prepare("SELECT user_id FROM interns WHERE intern_id = ?");
    $stmt2->execute([$application['intern_id']]);
    $intern_user_id = $stmt2->fetchColumn();
    
    $stmt->execute([
        $intern_user_id,
        $notification_message,
        'view_application.php?id=' . $application_id
    ]);
    
    // Log the action
    logAction('Update application status', 
        'Changed application #' . $application_id . ' for ' . $application['first_name'] . ' ' . $application['last_name'] . 
        ' from ' . $old_status . ' to ' . $new_status);
    
    // Redirect back with success message
    header("Location: view_applicants.php?status_updated=1");
    exit;
}

// Define available status transitions
$status_options = [
    'Pending' => ['Reviewed', 'Accepted', 'Rejected'],
    'Reviewed' => ['Accepted', 'Rejected', 'Offered'],
    'Accepted' => ['Offered', 'Hired', 'Rejected'],
    'Offered' => ['Hired', 'Rejected', 'Accepted'],
    'Hired' => ['Completed', 'Terminated'],
    'Rejected' => [], // No further updates allowed
    'Completed' => [],
    'Terminated' => []
];

$current_status = $application['status'];
$allowed_statuses = $status_options[$current_status] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Application Status</title>
    <link rel="stylesheet" href="../assets/css/company_update.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
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
            <h2>Update Application Status</h2>
            <a href="view_applicants.php" class="btn-back">Ã¢â€ Â Back to Applicants</a>
        </div> -->

        <div class="update-card">
            <!-- Applicant Info Summary -->
            <div class="applicant-summary">
                <h3><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></h3>
                <p class="internship-title"><?= htmlspecialchars($application['title']) ?></p>
                
                <div class="current-status">
                    <span class="status-label">Current Status:</span>
                    <span class="status-badge <?= getStatusClass($application['status']) ?>">
                        <?= htmlspecialchars($application['status']) ?>
                    </span>
                </div>
            </div>

            <!-- Update Form -->
            <div class="update-form-container">
                <?php if (empty($allowed_statuses) && $current_status !== 'Rejected'): ?>
                    <div class="info-message">
                        <p>No further status updates available for this application.</p>
                        <?php if ($application['contract_id']): ?>
                            <p><a href="contracts.php?id=<?= $application['contract_id'] ?>" class="btn-view-contract">View Contract</a></p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($current_status === 'Rejected'): ?>
                    <div class="info-message warning">
                        <p>This application has been rejected. No further updates are allowed.</p>
                    </div>
                <?php else: ?>
<form method="POST" class="update-form">
    <?= csrf_input() ?>
                        <div class="form-group">
                            <label for="status">Select New Status:</label>
                            <select name="status" id="status" required>
                                <option value="">-- Choose Status --</option>
                                <?php foreach ($allowed_statuses as $status): ?>
                                    <option value="<?= $status ?>"><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($current_status === 'Pending'): ?>
                            <div class="status-info">
                                <strong>Note:</strong> Review the application before accepting or offering.
                            </div>
                        <?php endif; ?>

                        <?php if ($current_status === 'Accepted'): ?>
                            <div class="status-info">
                                <strong>Note:</strong> You can offer a contract to accepted applicants.
                            </div>
                        <?php endif; ?>

                        <?php if ($current_status === 'Offered' && !$application['contract_id']): ?>
                            <div class="status-info warning">
                                <strong>Remember:</strong> Create a contract after offering the position.
                            </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" class="btn-update-status">Update Status</button>
                            <a href="view_applicants.php" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <!--<div class="quick-actions">
                <h4>Quick Actions</h4>
                <div class="action-buttons">
                    <a href="view_applications.php?id=<?= $application_id ?>" class="btn-action btn-view">View Full Application</a>
                    
                    <?php if (!$application['contract_id'] && in_array($current_status, ['Accepted', 'Offered'])): ?>
                        <a href="create_contract.php?application_id=<?= $application_id ?>" class="btn-action btn-create-contract">Create Contract</a>
                    <?php endif; ?>
                    
                    <?php if ($application['contract_id']): ?>
                        <a href="contracts.php?id=<?= $application['contract_id'] ?>" class="btn-action btn-view-contract">View Contract</a>
                    <?php endif; ?>
                </div> -->
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for status CSS class
function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>

<!-- Include Logout Modal HTML -->
<?php include '../html/logout_modal.html'; ?>

<!-- Include Logout Modal JavaScript -->
<script src="../js/logout_modal.js"></script>

<!-- Success message handling -->
<?php if (isset($_GET['updated'])): ?>
<script>
    alert('Status updated successfully!');
</script>
<?php endif; ?>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
