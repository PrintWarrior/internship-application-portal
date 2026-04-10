<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get company ID and name
$company = getStaffCompanyContext($user_id);
$company_id = $company['company_id'];
$company_name = $company['company_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert internship
    $stmt = $pdo->prepare("
        INSERT INTO internships 
        (company_id, title, description, requirements, duration, allowance, deadline, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $company_id,
        $_POST['title'],
        $_POST['description'],
        $_POST['requirements'],
        $_POST['duration'],
        $_POST['allowance'],
        $_POST['deadline']
    ]);

    $internship_id = $pdo->lastInsertId();

    // Notify all admins
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();

    $message = "Company '" . $company_name . "' has posted a new internship: " . $_POST['title'];

    foreach ($admins as $admin) {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, link)
            VALUES (?, ?, ?)
        ");
        $link = "../admin/view_internship.php?id=" . $internship_id;
        $stmt->execute([$admin['user_id'], $message, $link]);
    }

    // Log the action
    $logDescription = "Created new internship: " . $_POST['title'] . " (ID: " . $internship_id . ")";
    logAction('Create internship', $logDescription);

    // Redirect with success parameter to the same page
header("Location: post_internship.php?post_success=1");
exit;

}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Post Internship</title>
    <link rel="stylesheet" href="../assets/css/company_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyinternship_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
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
            <a href="staff_profile.php">Staff Profile</a>
            <a href="post_internship.php">Post Internship</a>
            <a href="manage_internships.php">My Internships</a>
            <a href="view_applicants.php">View Applicants</a>
            <a href="contracts.php">Contracts</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Post Internship</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"
                    style="border: 2px solid #ff0000; padding: 10px; margin-bottom: 20px; color: #ff0000; font-weight: bold;">
                    Failed to post internship. Please try again.
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="title">Internship Title *</label>
                        <input type="text" id="title" name="title" placeholder="e.g. Web Developer Intern" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"
                            placeholder="Describe the internship role, responsibilities, and what the intern will learn..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="requirements">Requirements</label>
                        <textarea id="requirements" name="requirements" rows="4"
                            placeholder="List the required skills, qualifications, and prerequisites..."></textarea>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="duration">Duration</label>
                            <input type="text" id="duration" name="duration" placeholder="e.g. 3 months, 6 months">
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label for="allowance">Allowance (₱)</label>
                            <input type="number" step="0.01" id="allowance" name="allowance" placeholder="e.g. 5000.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Application Deadline</label>
                        <input type="date" id="deadline" name="deadline" min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Post Internship</button>
                        <!--<button type="reset" class="reset-btn" onclick="return confirm('Clear all fields?')">Clear Form</button>-->
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Internship Modal HTML -->
    <?php include '../html/companyinternship_modal.html'; ?>

    <!-- Include JavaScript files -->
    <script src="../js/logout_modal.js"></script>
    <script src="../js/companyinternship_modal.js"></script>

    <?php if (isset($_GET['post_success'])): ?>
        <script>
            showNotification('Internship Posted Successfully');
        </script>
    <?php endif; ?>
</body>

</html>
