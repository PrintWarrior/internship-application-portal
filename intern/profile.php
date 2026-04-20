<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

/* ========================
HANDLE PROFILE INFO UPDATE
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_info'])) {
    requireValidCsrfToken(['redirect' => 'profile.php']);

    $stmt = $pdo->prepare("
            UPDATE interns SET
                first_name = ?, middle_name = ?, last_name = ?,
                contact_no = ?,
                university = ?, course = ?, year_level = ?
            WHERE user_id = ?
        ");

    $stmt->execute([
        $_POST['first_name'],
        $_POST['middle_name'],
        $_POST['last_name'],
        $_POST['contact_no'],
        $_POST['university'],
        $_POST['course'],
        $_POST['year_level'],
        $user_id
    ]);

    $internStmt = $pdo->prepare("SELECT intern_id FROM interns WHERE user_id = ?");
    $internStmt->execute([$user_id]);
    $internId = $internStmt->fetchColumn();

    if ($internId) {
        upsertEntityAddress((int) $internId, 'intern', [
            'address_line' => $_POST['address'] ?? null,
            'city' => $_POST['city'] ?? null,
            'province' => $_POST['province'] ?? null,
            'postal_code' => $_POST['postal_code'] ?? null,
            'country' => $_POST['country'] ?? 'Philippines'
        ]);
    }

    header("Location: profile.php?profile_success=1");
    exit;
}


/* ========================
HANDLE PROFILE IMAGE UPLOAD
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_image'])) {
    requireValidCsrfToken(['redirect' => 'profile.php']);

    $stmt = $pdo->prepare("SELECT profile_image FROM interns WHERE user_id=?");
    $stmt->execute([$user_id]);
    $currentImage = $stmt->fetchColumn();

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $newName = storeUploadedImage($_FILES['profile_image'], 'intern', '../assets/img/profile/');
        if ($newName !== null) {

                // delete old image
                if ($currentImage && $currentImage !== 'default.png') {
                    deleteManagedFile('../assets/img/profile/', $currentImage);
                }

                // update db
                $stmt = $pdo->prepare("UPDATE interns SET profile_image=? WHERE user_id=?");
                $stmt->execute([$newName, $user_id]);

                header("Location: profile.php?upload_success=1");
                exit;
        }
    }

    header("Location: profile.php?upload_error=1");
    exit;
}


/* ========================
HANDLE IMAGE DELETE
======================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    requireValidCsrfToken(['redirect' => 'profile.php']);

    $stmt = $pdo->prepare("SELECT profile_image FROM interns WHERE user_id=?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    if ($current && $current !== 'default.png') {
        deleteManagedFile('../assets/img/profile/', $current);
    }

    $stmt = $pdo->prepare("UPDATE interns SET profile_image='default.png' WHERE user_id=?");
    $stmt->execute([$user_id]);

    header("Location: profile.php");
    exit;
}

/* ========================
FETCH PROFILE
======================== */

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

$imagePath = "../assets/img/profile/" . ($intern['profile_image'] ?? 'default.png');
if (!file_exists($imagePath)) {
    $imagePath = "../assets/img/profile/default.png";
}

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
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/intern_profile.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/internprofile_modal.css">
    <link rel="stylesheet" href="../assets/css/internupdate_modal.css">
    <link rel="stylesheet" href="../assets/css/internpassword_modal.css">
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
        <h4>Intern Panel</h4>
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
        <h2>My Profile</h2>

        <div class="row">
            <!-- Left Column - Profile Form -->
            <div class="col-md-6">

                <div class="profile-image-container">
                    <img src="<?php echo $imagePath; ?>" alt="Profile Image" id="currentProfileImage">

                    <!-- Upload Image Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrf_input() ?>
                        <div class="form-group">
                            <label>Change Profile Picture</label>
                            <input type="file" name="profile_image" accept="image/*" required>
                        </div>
                        <button type="submit" name="update_profile_image">Select Image</button>
                    </form>

                    <!-- Delete Image Form -->
                    <form method="POST" style="margin-top:10px;">
                        <?= csrf_input() ?>
                        <button type="submit" name="delete_image">Delete Image</button>
                    </form>
                </div>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrf_input() ?>

                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name"
                                value="<?php echo htmlspecialchars($intern['first_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name"
                                value="<?php echo htmlspecialchars($intern['middle_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name"
                                value="<?php echo htmlspecialchars($intern['last_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Contact No</label>
                            <input type="text" name="contact_no"
                                value="<?php echo htmlspecialchars($intern['contact_no']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address"
                                value="<?php echo htmlspecialchars($intern['address']); ?>">
                        </div>

                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($intern['city']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Province</label>
                            <input type="text" name="province"
                                value="<?php echo htmlspecialchars($intern['province']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code"
                                value="<?php echo htmlspecialchars($intern['postal_code']); ?>">
                        </div>

                        <div class="form-group">
                            <label>University</label>
                            <input type="text" name="university"
                                value="<?php echo htmlspecialchars($intern['university']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" name="course" value="<?php echo htmlspecialchars($intern['course']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Year Level</label>
                            <input type="text" name="year_level"
                                value="<?php echo htmlspecialchars($intern['year_level']); ?>">
                        </div>

                        <button type="submit" name="update_profile_info">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Change Password Form -->
            <div class="col-md-6">
                <div class="form-container">
                    <h3>Change Password</h3>
                    <form action="change_password.php" method="POST" class="password-form">
                        <?= csrf_input() ?>
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required minlength="6">
                            
                        </div>

                        <!-- Confirm password field removed from main form -->
                        <!-- It's now handled in the modal -->

                        <button type="submit">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/internprofile_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/internprofile_modal.js"></script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/internupdate_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/internupdate_modal.js"></script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/internpassword_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/internpassword_modal.js"></script>


    <!-- Check for success messages -->
    <?php if (isset($_GET['profile_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Profile Updated Successfully');
                    }
                }, 100);
            });
        </script>
    <?php elseif (isset($_GET['upload_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Upload Successfully');
                    }
                }, 100);
            });
        </script>
    <?php elseif (isset($_GET['delete_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Delete Successfully');
                    }
                }, 100);
            });
        </script>
    <?php elseif (isset($_GET['password_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Password Changed Successfully');
                    }
                }, 100);
            });
        </script>
    <?php elseif (isset($_GET['password_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification('Current password is incorrect', false);
                    }
                }, 100);
            });
        </script>
    <?php endif; ?>
    <script src="../js/responsive-nav.js"></script>
</body>

</html>
