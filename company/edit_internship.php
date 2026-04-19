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
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    if ($start_date && $end_date && $end_date < $start_date) {
        $error = 'date_range';
    } else {
        $stmt = $pdo->prepare("
            UPDATE internships 
            SET title = ?, description = ?, requirements = ?, 
                duration = ?, allowance = ?, deadline = ?, start_date = ?, end_date = ?
            WHERE internship_id = ?
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['requirements'],
            $_POST['duration'],
            $_POST['allowance'],
            $_POST['deadline'],
            $start_date,
            $end_date,
            $internship_id
        ]);
        
        // Log the action
        logAction('Update internship', 'Updated internship: ' . $_POST['title'] . ' (ID: ' . $internship_id . ')');
        
        header("Location: view_internship.php?id=" . $internship_id . "&updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Internship - <?= htmlspecialchars($internship['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/company_edit_internship.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyinternship_edit.css">
    <link rel="stylesheet" href="../assets/css/companyedit_internshipmodal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
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
            <a href="view_internship.php?id=<?= $internship_id ?>" class="btn-back">ÃƒÂ¢Ã¢â‚¬Â Ã‚Â Back to Details</a>
        </div>-->

        <div class="form-container">
            <form method="POST">
                <?php if (!empty($error)): ?>
                    <div class="error-message"
                        style="border: 2px solid #ff0000; padding: 10px; margin-bottom: 20px; color: #ff0000; font-weight: bold;">
                        End date must be on or after the start date.
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Internship Title *</label>
                    <input type="text" id="title" name="title" 
                           value="<?= htmlspecialchars($_POST['title'] ?? $internship['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? $internship['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" rows="5"><?= htmlspecialchars($_POST['requirements'] ?? $internship['requirements']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="duration">Duration</label>
                        <input type="text" id="duration" name="duration" 
                               value="<?= htmlspecialchars($_POST['duration'] ?? $internship['duration']) ?>">
                    </div>

                    <div class="form-group half">
                        <label for="allowance">Allowance (PHP)</label>
                        <input type="number" step="0.01" id="allowance" name="allowance" 
                               value="<?= htmlspecialchars($_POST['allowance'] ?? $internship['allowance']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date"
                               value="<?= htmlspecialchars($_POST['start_date'] ?? $internship['start_date']) ?>">
                    </div>

                    <div class="form-group half">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date"
                               value="<?= htmlspecialchars($_POST['end_date'] ?? $internship['end_date']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="deadline">Application Deadline</label>
                    <input type="date" id="deadline" name="deadline" 
                           value="<?= htmlspecialchars($_POST['deadline'] ?? $internship['deadline']) ?>">
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
    <script src="../js/responsive-nav.js"></script>
</body>
</html>
