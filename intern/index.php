<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===========================
   FETCH INTERN PROFILE
=========================== */

$stmt = $pdo->prepare("
    SELECT i.*, u.email,
           addr.address_line AS address,
           addr.city,
           addr.province,
           addr.postal_code,
           addr.country
    FROM interns i
    JOIN users u ON i.user_id = u.user_id
    LEFT JOIN addresses addr
        ON addr.entity_id = i.intern_id
        AND addr.entity_type = 'intern'
        AND addr.is_primary = 1
    WHERE i.user_id = ?
");
$stmt->execute([$user_id]);
$intern = $stmt->fetch();

/* ===========================
   CHECK IF PASSWORD IS SET
=========================== */

$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$password_set = !empty($user['password_hash']);
$show_modal = !$password_set;

/* Count unread */
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
    <title>Intern Dashboard</title>
    <link rel="stylesheet" href="../assets/css/intern_index.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <style>
/* Notification badge in sidebar */
.sidebar .badge {
    background-color: #ffffff;
    color: #000000;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 8px;
    border: 1px solid #ffffff;
}
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="text-center">Intern Panel</h4>
        <a href="index.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="browse_internships.php">Browse Internships</a>
        <a href="my_applications.php">My Applications</a>
        <a href="offers.php">Offers</a>
        <a href="contracts.php">Contracts</a>
        <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
        <a href="#" onclick="openLogoutModal()">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($intern['first_name']); ?></h2>
        <p class="text-muted">Here is your profile summary</p>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card-box">
                    <h5>Personal Information</h5>
                    <hr>
                    <p><strong>Full Name:</strong>
                        <?php
                        echo htmlspecialchars(
                            $intern['first_name'] . ' ' .
                            ($intern['middle_name'] ?? '') . ' ' .
                            $intern['last_name']
                        );
                        ?>
                    </p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($intern['email']); ?></p>
                    <p><strong>Contact No:</strong> <?php echo htmlspecialchars($intern['contact_no']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($intern['gender']); ?></p>
                    <p><strong>Birthdate:</strong> <?php echo htmlspecialchars($intern['birthdate']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($intern['age']); ?></p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card-box">
                    <h5>Academic Information</h5>
                    <hr>
                    <p><strong>University:</strong> <?php echo htmlspecialchars($intern['university']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($intern['course']); ?></p>
                    <p><strong>Year Level:</strong> <?php echo htmlspecialchars($intern['year_level']); ?></p>
                    <p><strong>Address:</strong>
                        <?php
                        echo htmlspecialchars(
                            $intern['address'] . ', ' .
                            $intern['city'] . ', ' .
                            $intern['province']
                        );
                        ?>
                    </p>
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
                // Show modal
                const modal = document.getElementById('passwordModal');
                if (modal) {
                    modal.style.display = 'flex';
                }

                // Save password functionality
                document.getElementById('savePasswordBtn').addEventListener('click', function () {
                    const newPass = document.getElementById('new_password').value;
                    const confirmPass = document.getElementById('confirm_password').value;
                    const errorDiv = document.getElementById('passwordError');

                    if (newPass.length < 6) {
                        errorDiv.textContent = 'Password must be at least 6 characters.';
                        return;
                    }

                    if (newPass !== confirmPass) {
                        errorDiv.textContent = 'Passwords do not match.';
                        return;
                    }

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
                    xhr.send('password=' + encodeURIComponent(newPass) + '&csrf_token=' + encodeURIComponent('<?= getCsrfToken() ?>'));
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
