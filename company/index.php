<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$company_id = $company['company_id'] ?? null;
$company_name = $company['company_name'] ?? 'Company';

// Notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Dashboard stats
$totalInternships = 0;
$totalApplications = 0;

if ($company_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $totalInternships = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM applications a
        JOIN internships i ON a.internship_id = i.internship_id
        WHERE i.company_id = ?
    ");
    $stmt->execute([$company_id]);
    $totalApplications = $stmt->fetchColumn();
}

// Check if password is set
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$password_set = !empty($user['password_hash']);
$show_modal = !$password_set;

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM notifications
WHERE user_id=? AND is_read=0
");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="../assets/css/company_index.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
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
            <a href="staff_profile.php">Staff Profile</a>
            <a href="post_internship.php">Post Internship</a>
            <a href="manage_internships.php">My Internships</a>
            <a href="view_applicants.php">View Applicants</a>
            <a href="contracts.php">Contracts</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content">
            <h2>Welcome, <?= htmlspecialchars($company_name) ?></h2>

            <div class="cards">
                <div class="card">
                    <h3><?= $totalInternships ?></h3>
                    <p>Posted Internships</p>
                </div>

                <div class="card">
                    <h3><?= $totalApplications ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
        </div>

    </div>

    <!-- PASSWORD MODAL - CUSTOM BLACK AND WHITE -->
    <?php if ($show_modal): ?>
        <div class="modal-overlay" id="passwordModal">
            <div class="modal-container">
                <div class="modal-header">
                    <h5>Set Your Password</h5>
                </div>
                <div class="modal-body">
                    <form id="passwordForm">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" class="form-control" id="new_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" required>
                        </div>
                        <div id="passwordError" class="text-danger"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn" id="savePasswordBtn">Save Password</button>
                </div>
            </div>
        </div>

        <script>
            // Custom modal JavaScript without Bootstrap
            document.addEventListener('DOMContentLoaded', function () {
                // Show modal if password not set
                const modal = document.getElementById('passwordModal');
                if (modal) {
                    modal.classList.add('active');
                }

                // Save password functionality
                document.getElementById('savePasswordBtn').addEventListener('click', function () {
                    const newPass = document.getElementById('new_password').value;
                    const confirmPass = document.getElementById('confirm_password').value;
                    const errorDiv = document.getElementById('passwordError');

                    // Validate password
                    if (newPass.length < 6) {
                        errorDiv.textContent = 'Password must be at least 6 characters.';
                        return;
                    }

                    if (newPass !== confirmPass) {
                        errorDiv.textContent = 'Passwords do not match.';
                        return;
                    }

                    errorDiv.textContent = '';

                    // AJAX request
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'set_password.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    location.reload();
                                } else {
                                    errorDiv.textContent = response.error || 'An error occurred.';
                                }
                            } catch (e) {
                                errorDiv.textContent = 'Invalid response from server.';
                            }
                        } else {
                            errorDiv.textContent = 'Server error. Please try again.';
                        }
                    };

                    xhr.onerror = function () {
                        errorDiv.textContent = 'Network error. Please check your connection.';
                    };

                    xhr.send('password=' + encodeURIComponent(newPass));
                });

                // Allow Enter key to submit
                document.getElementById('new_password').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('savePasswordBtn').click();
                    }
                });

                document.getElementById('confirm_password').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('savePasswordBtn').click();
                    }
                });
            });
        </script>
    <?php endif; ?>
        <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
    <script src="../js/responsive-nav.js"></script>
</body>

</html>
