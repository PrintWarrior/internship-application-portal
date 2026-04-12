<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);

if (!$company) {
    header('Location: ../index.php');
    exit;
}

$upload_dir = "../assets/img/profile/";
$default_image = "default.png";

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* -----------------------
UPDATE PROFILE INFO
-----------------------*/
if (isset($_POST['update_profile'])) {

    $pdo->beginTransaction();

    try {
        // 1. Update company info
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET company_name=?, industry=?, contact_person=?, contact_phone=?, 
                website=?, description=? 
            WHERE company_id=?
        ");

        $stmt->execute([
            $_POST['company_name'],
            $_POST['industry'],
            $_POST['contact_person'],
            $_POST['contact_phone'],
            $_POST['website'],
            $_POST['description'],
            $company['company_id']
        ]);
        upsertEntityAddress((int) $company['company_id'], 'company', [
            'address_line' => $_POST['address'] ?? null,
            'city' => $_POST['city'] ?? null,
            'province' => $_POST['province'] ?? null,
            'postal_code' => $_POST['postal_code'] ?? null,
            'country' => $_POST['country'] ?? 'Philippines'
        ]);
        $company_id = $company['company_id'];

        // 3. Update staff name (VERY IMPORTANT)
        if ($company_id) {

            // Split name into first & last
            $fullName = trim($_POST['contact_person']);
            $nameParts = explode(' ', $fullName, 2);

            $first_name = $nameParts[0];
            $last_name = $nameParts[1] ?? '';

            // Check if staff exists
            $stmt = $pdo->prepare("SELECT staff_id FROM staffs WHERE company_id=?");
            $stmt->execute([$company_id]);

            if ($stmt->fetch()) {

                // UPDATE existing staff
                $stmt = $pdo->prepare("
            UPDATE staffs 
            SET first_name = ?, last_name = ?
            WHERE company_id = ?
        ");

                $stmt->execute([
                    $first_name,
                    $last_name,
                    $company_id
                ]);

            } else {

                // INSERT new staff
                $stmt = $pdo->prepare("
            INSERT INTO staffs (user_id, company_id, first_name, last_name, email, contact_no, position)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

                $stmt->execute([
                    $user_id,
                    $company_id,
                    $first_name,
                    $last_name,
                    $_POST['contact_email'] ?? null,
                    $_POST['contact_phone'] ?? null,
                    'Contact Person'
                ]);
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Profile update error: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();


$upload_success = false;
$delete_success = false;

/* -----------------------
UPLOAD PROFILE IMAGE
-----------------------*/
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {

    $file = $_FILES['profile_image'];

    if ($file['error'] === 0) {

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {

            $newName = "company_" . $company['company_id'] . "_" . time() . "." . $ext;
            move_uploaded_file($file['tmp_name'], $upload_dir . $newName);

            $stmt = $pdo->prepare("UPDATE companies SET profile_image=? WHERE company_id=?");
            $stmt->execute([$newName, $company['company_id']]);

            // Redirect with success parameter
            header("Location: profile.php?upload_success=1");
            exit;
        }
    }

    header("Location: profile.php?upload_error=1");
    exit;
}


/* -----------------------
DELETE PROFILE IMAGE
-----------------------*/
if (isset($_POST['delete_image'])) {

    if ($company['profile_image'] !== $default_image) {

        $file = $upload_dir . $company['profile_image'];

        if (file_exists($file)) {
            unlink($file);
        }

        $stmt = $pdo->prepare("UPDATE companies SET profile_image=? WHERE company_id=?");
        $stmt->execute([$default_image, $company['company_id']]);

        // Redirect with success parameter
        header("Location: profile.php?delete_success=1");
        exit;
    }

    header("Location: profile.php?delete_error=1");
    exit;
}

// Check for success parameters
if (isset($_GET['upload_success'])) {
    $upload_success = true;
} elseif (isset($_GET['delete_success'])) {
    $delete_success = true;
}


/* -----------------------
CHANGE PASSWORD
-----------------------*/
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (password_verify($current, $user['password_hash'])) {

        $newHash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
        $stmt->execute([$newHash, $user_id]);

        // Redirect with success parameter
        header("Location: profile.php?password_success=1");
        exit;

    } else {
        // Redirect with error parameter
        header("Location: profile.php?password_error=1");
        exit;
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Company Profile</title>
    <link rel="stylesheet" href="../assets/css/company_profile.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companyprofile_modal.css">
    <link rel="stylesheet" href="../assets/css/companyupdate_modal.css">
    <link rel="stylesheet" href="../assets/css/password_modal.css">
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
        <div class="container">
            <h2>Company Profile</h2>

            <?php if (isset($message)): ?>
                <p style="color: #000000; font-weight: bold; border: 2px solid #000000; padding: 10px;"><?= $message ?></p>
            <?php endif; ?>

            <!-- PROFILE IMAGE SECTION -->
            <div class="profile-image-section">
                <h3>Company Logo</h3>

                <img src="../assets/img/profile/<?= $company['profile_image'] ?? $default_image ?>" alt="Company Logo"
                    style="max-width: 200px; border: 2px solid #000000;">

                <div class="image-actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_image" required>
                        <button type="submit" name="upload_image">Upload Image</button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="delete_image">Delete Image</button>
                    </form>
                </div>
            </div>

            <hr style="border: 1px solid #000000;">

            <!-- PROFILE DETAILS FORM -->
            <h3>Company Information</h3>

            <form method="POST">
                <div class="form-group">
                    <label>Company Name *</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($company['company_name']) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Industry</label>
                    <input type="text" name="industry" value="<?= htmlspecialchars($company['industry']) ?>">
                </div>

                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person"
                        value="<?= htmlspecialchars($company['contact_person']) ?>">
                </div>

                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($company['contact_phone']) ?>">
                </div>

                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" value="<?= htmlspecialchars($company['website']) ?>">
                </div>

                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($company['address']) ?>">
                </div>

                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($company['city']) ?>">
                </div>

                <div class="form-group">
                    <label>Province</label>
                    <input type="text" name="province" value="<?= htmlspecialchars($company['province']) ?>">
                </div>

                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" name="postal_code" value="<?= htmlspecialchars($company['postal_code']) ?>">
                </div>

                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="<?= htmlspecialchars($company['country']) ?>">
                </div>

                <div class="form-group" style="align-items: flex-start;">
                    <label>Description</label>
                    <textarea name="description"
                        placeholder="Company Description"><?= htmlspecialchars($company['description']) ?></textarea>
                </div>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>

            <hr style="border: 1px solid #000000;">

            <!-- CHANGE PASSWORD -->
            <h3>Change Password</h3>

            <form method="POST" class="password-form">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>

                <button type="submit" name="change_password">Change Password</button>
            </form>
        </div>
    </div>
    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <?php include '../html/companyprofile_modal.html'; ?>

    <script src="../js/companyprofile_modal.js"></script>

    <?php include '../html/companyupdate_modal.html'; ?>

    <script src="../js/companyupdate_modal.js"></script>

    <?php include '../html/companypassword_modal.html'; ?>

    <script src="../js/companypassword_modal.js"></script>

    <script>
        // Notification function (make it global)
        function showNotification(message, isSuccess = true) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.style.display = 'block';

            if (isSuccess) {
                notification.style.backgroundColor = '#000000';
                notification.style.color = '#ffffff';
            } else {
                notification.style.backgroundColor = '#ffffff';
                notification.style.color = '#000000';
                notification.style.borderColor = '#000000';
            }

            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        <?php if (isset($_GET['upload_success'])): ?>
            showNotification('Upload Successfully');
        <?php elseif (isset($_GET['delete_success'])): ?>
            showNotification('Delete Successfully');
        <?php elseif (isset($_GET['update_success'])): ?>
            showNotification('Company Information Updated Successfully');
        <?php elseif (isset($_GET['password_success'])): ?>
            showNotification('Password Changed Successfully');
        <?php elseif (isset($_GET['upload_error'])): ?>
            showNotification('Upload Failed', false);
        <?php elseif (isset($_GET['delete_error'])): ?>
            showNotification('Delete Failed', false);
        <?php elseif (isset($_GET['password_error'])): ?>
            showNotification('Current password is incorrect', false);
        <?php endif; ?>
    </script>
</body>

</html>
