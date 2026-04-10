<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get internship details - verify it belongs to this company
$stmt = $pdo->prepare("
    SELECT i.* FROM internships i
    WHERE i.internship_id = ? AND i.company_id = ?
");
$stmt->execute([$internship_id, $company['company_id']]);
$internship = $stmt->fetch();

if (!$internship) {
    header('Location: manage_internships.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE internships 
        SET title = ?, description = ?, requirements = ?, 
            duration = ?, allowance = ?, deadline = ?
        WHERE internship_id = ?
    ");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['requirements'],
        $_POST['duration'],
        $_POST['allowance'],
        $_POST['deadline'],
        //$_POST['status'],
        $internship_id
    ]);
    
    // Log the action
    logAction('Update internship', 'Updated internship: ' . $_POST['title'] . ' (ID: ' . $internship_id . ')');
    
    header("Location: view_internship.php?id=" . $internship_id . "&updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Internship - <?= htmlspecialchars($internship['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/company_edit_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyinternship_edit.css">
    <link rel="stylesheet" href="../assets/css/companyedit_internshipmodal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>
<div id="notification" class="notification"></div>
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
            <h2>Edit Internship</h2>
            <a href="view_internship.php?id=<?= $internship_id ?>" class="btn-back">← Back to Details</a>
        </div>-->

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Internship Title *</label>
                    <input type="text" id="title" name="title" 
                           value="<?= htmlspecialchars($internship['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?= htmlspecialchars($internship['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" rows="5"><?= htmlspecialchars($internship['requirements']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="duration">Duration</label>
                        <input type="text" id="duration" name="duration" 
                               value="<?= htmlspecialchars($internship['duration']) ?>">
                    </div>

                    <div class="form-group half">
                        <label for="allowance">Allowance (₱)</label>
                        <input type="number" step="0.01" id="allowance" name="allowance" 
                               value="<?= htmlspecialchars($internship['allowance']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="deadline">Application Deadline</label>
                    <input type="date" id="deadline" name="deadline" 
                           value="<?= htmlspecialchars($internship['deadline']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <a href="manage_internships.php?id=<?= $internship_id ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Logout Modal HTML -->
<?php include '../html/logout_modal.html'; ?>

<!-- Include Edit Internship Modal HTML -->
<?php include '../html/companyedit_internshipmodal.html'; ?>

<!-- Include JavaScript files -->
<script src="../js/logout_modal.js"></script>
<script src="../js/companyedit_internshipmodal.js"></script>

<?php if (isset($_GET['updated'])): ?>
<script>
    // Show notification if redirected from update
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof showNotification === 'function') {
                showNotification('Internship Updated Successfully');
            }
        }, 100);
    });
</script>
<?php endif; ?>
</body>
</html>
