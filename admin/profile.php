<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = "../assets/img/profile/";
$default_image = "default.png";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ========================
HANDLE PROFILE INFO UPDATE
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_info'])) {

    $stmt = $pdo->prepare("
        UPDATE admin_profiles SET
            admin_fname = ?, admin_lname = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $_POST['admin_fname'],
        $_POST['admin_lname'],
        $user_id
    ]);

    header("Location: profile.php?profile_success=1");
    exit;
}

/* ========================
HANDLE PROFILE IMAGE UPLOAD
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_image'])) {

    $stmt = $pdo->prepare("SELECT profile_image FROM admin_profiles WHERE user_id=?");
    $stmt->execute([$user_id]);
    $currentImage = $stmt->fetchColumn();

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $allowedTypes = ['image/jpeg', 'image/png'];

        if (
            in_array($_FILES['profile_image']['type'], $allowedTypes)
            && $_FILES['profile_image']['size'] <= 2 * 1024 * 1024
        ) {

            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $newName = uniqid('admin_', true) . '.' . $ext;
            $uploadPath = $upload_dir . $newName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {

                // delete old image
                if ($currentImage && $currentImage !== $default_image) {
                    $oldPath = $upload_dir . $currentImage;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // update db
                $stmt = $pdo->prepare("UPDATE admin_profiles SET profile_image=? WHERE user_id=?");
                $stmt->execute([$newName, $user_id]);

                header("Location: profile.php?upload_success=1");
                exit;
            }
        }
    }

    header("Location: profile.php?upload_error=1");
    exit;
}

/* ========================
HANDLE IMAGE DELETE
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {

    $stmt = $pdo->prepare("SELECT profile_image FROM admin_profiles WHERE user_id=?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    if ($current && $current !== $default_image) {
        $filePath = $upload_dir . $current;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("UPDATE admin_profiles SET profile_image=? WHERE user_id=?");
    $stmt->execute([$default_image, $user_id]);

    header("Location: profile.php");
    exit;
}

/* ========================
HANDLE PASSWORD CHANGE
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Verify current password
    if (!password_verify($current_password, $user['password_hash'])) {
        $error_password = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_password = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_password = "Password must be at least 6 characters.";
    } else {
        // Update password
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
        $stmt->execute([$hashed, $user_id]);
        $password_success = "Password changed successfully.";
    }
}

/* ========================
FETCH PROFILE
======================== */

$stmt = $pdo->prepare("
    SELECT ap.*, u.email 
    FROM admin_profiles ap
    JOIN users u ON ap.user_id = u.user_id
    WHERE ap.user_id = ?
");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

// If no admin profile exists, create one
if (!$admin) {
    $stmt = $pdo->prepare("INSERT INTO admin_profiles (user_id, admin_fname, admin_lname, profile_image) VALUES (?, '', '', ?)");
    $stmt->execute([$user_id, $default_image]);
    
    $stmt = $pdo->prepare("
        SELECT ap.*, u.email 
        FROM admin_profiles ap
        JOIN users u ON ap.user_id = u.user_id
        WHERE ap.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch();
}

$imagePath = $upload_dir . ($admin['profile_image'] ?? $default_image);
if (!file_exists($imagePath)) {
    $imagePath = $upload_dir . $default_image;
}

// Count unread notifications
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
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../assets/css/admin_profile.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>

<body>

  <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <!-- Replace logo.png with your actual logo -->
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - Admin</h2>
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
        <h2>My Profile</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['profile_success'])): ?>
            <div class="alert alert-success">✓ Profile information updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['upload_success'])): ?>
            <div class="alert alert-success">✓ Profile picture uploaded successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['upload_error'])): ?>
            <div class="alert alert-danger">✗ Error uploading profile picture. Please try again.</div>
        <?php endif; ?>
        
        <?php if (isset($error_password)): ?>
            <div class="alert alert-danger">✗ <?= htmlspecialchars($error_password) ?></div>
        <?php endif; ?>
        
        <?php if (isset($password_success)): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($password_success) ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column - Profile Picture & Info -->
            <div>
                <div class="form-section">
                    <h3>Profile Picture</h3>
                    
                    <div class="profile-image-container">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Profile Image" id="currentProfileImage">
                    </div>

                    <!-- Upload Image Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Change Profile Picture</label>
                            <input type="file" name="profile_image" accept="image/jpeg,image/png" required>
                            <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, PNG (Max 2MB)</small>
                        </div>
                        <div class="button-group">
                            <button type="submit" name="update_profile_image" class="btn btn-primary">Upload Picture</button>
                            <button type="submit" name="delete_image" class="btn btn-danger" onclick="return confirm('Delete current profile picture?')">Delete Picture</button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="form-section">
                    <h3>Account Information</h3>
                    <div class="info-field">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($admin['email']) ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">User Type:</span>
                        <span class="info-value">Administrator</span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php 
                            $stmt = $pdo->prepare("SELECT created_at FROM users WHERE user_id=?");
                            $stmt->execute([$user_id]);
                            $user_data = $stmt->fetch();
                            echo date('F d, Y', strtotime($user_data['created_at']));
                        ?></span>
                    </div>
                </div>
            </div>

            <!-- Right Column - Edit Profile & Password -->
            <div>
                <!-- Edit Profile Information -->
                <div class="form-section">
                    <h3>Profile Information</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="admin_fname">First Name</label>
                            <input type="text" id="admin_fname" name="admin_fname" value="<?= htmlspecialchars($admin['admin_fname'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="admin_lname">Last Name</label>
                            <input type="text" id="admin_lname" name="admin_lname" value="<?= htmlspecialchars($admin['admin_lname'] ?? '') ?>" required>
                        </div>

                        <button type="submit" name="update_profile_info" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="form-section">
                    <h3>Change Password</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- LOGOUT MODAL -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLogoutModal()">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <div class="modal-buttons">
                <button onclick="closeLogoutModal()" class="btn btn-secondary">Cancel</button>
                <a href="../logout.php" class="btn btn-danger" style="text-decoration: none;">Logout</a>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'block';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('logoutModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>

</body>

</html>
