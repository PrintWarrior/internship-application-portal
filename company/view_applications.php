<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$application_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get application details
$stmt = $pdo->prepare("
    SELECT a.*, 
           i.title as internship_title, 
           i.description as internship_description,
           i.duration,
           i.allowance,
           i.deadline,
           i.status as internship_status,
           c.company_name,
           c.industry,
           ir.first_name, 
           ir.last_name, 
           ir.middle_name,
           ir.contact_no,
           ir.gender,
           ir.birthdate,
           ir.age,
           addr.address_line AS address,
           addr.city,
           addr.province,
           addr.postal_code,
           ir.university,
           ir.course,
           ir.year_level,
           ir.profile_image,
           u.email
    FROM applications a
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    LEFT JOIN addresses addr
        ON addr.entity_id = ir.intern_id
        AND addr.entity_type = 'intern'
        AND addr.is_primary = 1
    WHERE a.application_id = ? AND i.company_id = ?
");
$stmt->execute([$application_id, $company['company_id']]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: view_applicants.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $new_status = $_POST['action'];
    $old_status = $application['status'];
    
    // Update application status
    $updateStmt = $pdo->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
    $updateStmt->execute([$new_status, $application_id]);
    
    // Create notification for the intern
    $notification_message = "Your application for '" . $application['internship_title'] . "' has been " . strtolower($new_status);
    
    // Get intern's user_id
    $stmt2 = $pdo->prepare("SELECT user_id FROM interns WHERE intern_id = ?");
    $stmt2->execute([$application['intern_id']]);
    $intern_user_id = $stmt2->fetchColumn();
    
    // Insert notification
    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notifStmt->execute([$intern_user_id, $notification_message]);
    
    // Log the action
    logAction('Update application status', 
        'Changed application #' . $application_id . ' for ' . $application['first_name'] . ' ' . $application['last_name'] . 
        ' from ' . $old_status . ' to ' . $new_status);
    
    // Redirect to refresh the page with success message
    header("Location: view_applications.php?id=" . $application_id . "&success=1");
    exit;
}

// Function to get status class
function getStatusClass($status)
{
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details</title>
    <link rel="stylesheet" href="../assets/css/company_application.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyapplication_modal.css">
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
            <?php if (isset($_GET['success'])): ?>
                <!-- Hide this div as we'll use notification instead -->
                <div class="success-message" style="display: none;">
                    Application status updated successfully!
                </div>
            <?php endif; ?>

            <!-- Application Summary Card -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="summary-title">Application #<?= $application_id ?></span>
                    <span class="status-badge <?= getStatusClass($application['status']) ?>"><?= $application['status'] ?></span>
                </div>
                <div class="summary-body">
                    <div class="summary-item">
                        <span class="summary-label">Applied Date:</span>
                        <span class="summary-value"><?= date('F d, Y', strtotime($application['date_applied'])) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Last Updated:</span>
                        <span class="summary-value"><?= date('F d, Y', strtotime($application['date_applied'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="details-grid">
                <!-- Left Column - Applicant Information -->
                <div class="details-card">
                    <h3>Applicant Information</h3>

                    <div class="profile-image-container">
                        <?php
                        $imagePath = "../assets/img/profile/" . ($application['profile_image'] ?? 'default.png');
                        if (!file_exists($imagePath)) {
                            $imagePath = "../assets/img/profile/default.png";
                        }
                        ?>
                        <img src="<?= $imagePath ?>" alt="Profile Image" class="profile-image">
                    </div>

                    <div class="details-section">
                        <h4>Personal Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Full Name:</span>
                            <span class="detail-value" id="applicantName">
                                <?= htmlspecialchars($application['first_name'] . ' ' .
                                    ($application['middle_name'] ? $application['middle_name'] . ' ' : '') .
                                    $application['last_name']) ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Contact No:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['contact_no']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Gender:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['gender'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Birthdate:</span>
                            <span class="detail-value"><?= $application['birthdate'] ? date('F d, Y', strtotime($application['birthdate'])) : 'Not specified' ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Age:</span>
                            <span class="detail-value"><?= $application['age'] ?? 'Not specified' ?></span>
                        </div>
                    </div>

                    <div class="details-section">
                        <h4>Address</h4>
                        <div class="detail-row">
                            <span class="detail-label">Street:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['address'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">City:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['city'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Province:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['province'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Postal Code:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['postal_code'] ?? 'Not specified') ?></span>
                        </div>
                    </div>

                    <div class="details-section">
                        <h4>Educational Background</h4>
                        <div class="detail-row">
                            <span class="detail-label">University:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['university']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Course:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['course']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Year Level:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['year_level'] ?? 'Not specified') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Internship and Application -->
                <div class="details-card">
                    <h3>Internship Details</h3>

                    <div class="details-section">
                        <h4 id="internshipTitle"><?= htmlspecialchars($application['internship_title']) ?></h4>
                        <div class="company-badge-large"><?= htmlspecialchars($application['company_name']) ?></div>

                        <div class="detail-row">
                            <span class="detail-label">Industry:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['industry'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value"><?= htmlspecialchars($application['duration'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Allowance:</span>
                            <span class="detail-value">PHP <?= number_format($application['allowance'] ?? 0, 2) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Deadline:</span>
                            <span class="detail-value"><?= date('F d, Y', strtotime($application['deadline'])) ?></span>
                        </div>
                    </div>

                    <div class="details-section">
                        <h4>Description</h4>
                        <div class="description-box">
                            <?= nl2br(htmlspecialchars($application['internship_description'] ?? 'No description provided.')) ?>
                        </div>
                    </div>

                    <div class="details-section">
                        <h4>Update Application Status</h4>

                        <form method="POST" class="status-update-form">
                            <div class="status-actions">
                                <button type="submit" name="action" value="Pending" 
                                    class="btn-status btn-status-pending" 
                                    <?= $application['status'] === 'Pending' ? 'disabled' : '' ?>>Pending</button>
                                    
                                <button type="submit" name="action" value="Offered" 
                                    class="btn-status btn-status-offer" 
                                    <?= $application['status'] === 'Offered' ? 'disabled' : '' ?>>Offer</button>
                                    
                                <!--<button type="submit" name="action" value="Accepted" 
                                    class="btn-status btn-status-accept" 
                                    <?= $application['status'] === 'Accepted' ? 'disabled' : '' ?>>Accept</button>-->
                                    
                                <button type="submit" name="action" value="Rejected" 
                                    class="btn-status btn-status-reject" 
                                    <?= $application['status'] === 'Rejected' ? 'disabled' : '' ?>>Reject</button>
                            </div>
                        </form>

                        <p class="note">Note: Clicking any button will show a confirmation modal before updating the status.</p>
                    </div>

                    <div class="details-section">
                        <h4>Quick Actions</h4>
                        <div class="action-buttons-large">
                            <?php if (in_array($application['status'], ['Accepted', 'Offered'])): ?>
                                <a href="create_contract.php?application_id=<?= $application_id ?>" 
                                   class="btn-action btn-create-contract">Create Contract</a>
                            <?php endif; ?>
                            
                            <?php if ($application['status'] === 'Pending'): ?>
                                <span class="note">Accept or offer to create a contract</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>
    
    <!-- Include Application Modal HTML -->
    <?php include '../html/companyapplication_modal.html'; ?>

    <!-- Include JavaScript files -->
    <script src="../js/logout_modal.js"></script>
    <script src="../js/companyapplication_modal.js"></script>

    <script src="../js/responsive-nav.js"></script>
</body>

</html>
