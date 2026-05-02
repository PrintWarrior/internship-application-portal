<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'profile';
$uploadUrl = '../assets/img/profile/';
$defaultImage = 'default.png';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

function setProfileFlash(string $type, string $message): void
{
    $_SESSION['profile_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function redirectProfile(array $params = []): void
{
    $query = $params ? ('?' . http_build_query($params)) : '';
    header('Location: profile.php' . $query);
    exit;
}

function fetchInternProfile(PDO $pdo, int $userId)
{
    $stmt = $pdo->prepare("
        SELECT
            i.*,
            u.email,
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
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buildImageUrl(array $intern, string $uploadDir, string $uploadUrl, string $defaultImage): string
{
    $imageName = trim((string) ($intern['profile_image'] ?? ''));
    if ($imageName === '') {
        $imageName = $defaultImage;
    }

    $imagePath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;
    if (!is_file($imagePath)) {
        $imageName = $defaultImage;
    }

    return $uploadUrl . rawurlencode($imageName);
}

function handleInternImageUpload(PDO $pdo, array $intern, int $userId, string $uploadDir, string $defaultImage): void
{
    if (!isset($_FILES['profile_image'])) {
        setProfileFlash('error', 'No file was selected.');
        redirectProfile();
    }

    $file = $_FILES['profile_image'];
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode !== UPLOAD_ERR_OK) {
        setProfileFlash('error', 'Upload failed. Please choose a JPG or PNG image under 2MB.');
        redirectProfile();
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > 2097152) {
        setProfileFlash('error', 'Upload failed. Please choose a JPG or PNG image under 2MB.');
        redirectProfile();
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        setProfileFlash('error', 'Upload failed. The uploaded file could not be verified.');
        redirectProfile();
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $tmpName) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if (!is_string($mimeType) || !isset($allowedMimeMap[$mimeType])) {
        setProfileFlash('error', 'Only JPG and PNG images are allowed.');
        redirectProfile();
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        setProfileFlash('error', 'Upload failed. Profile image folder is unavailable.');
        redirectProfile();
    }

    if (!is_writable($uploadDir)) {
        setProfileFlash('error', 'Upload failed. Profile image folder is not writable.');
        error_log('Intern profile upload failed: upload directory not writable: ' . $uploadDir);
        redirectProfile();
    }

    $newFilename = 'intern_' . $intern['intern_id'] . '_' . time() . '.' . $allowedMimeMap[$mimeType];
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $newFilename;

    if (!move_uploaded_file($tmpName, $destination)) {
        setProfileFlash('error', 'Upload failed while saving the image.');
        error_log('Intern profile upload failed: move_uploaded_file returned false for user_id=' . $userId . ' destination=' . $destination);
        redirectProfile();
    }

    $update = $pdo->prepare("UPDATE interns SET profile_image = ? WHERE intern_id = ?");
    $update->execute([$newFilename, $intern['intern_id']]);

    $saved = fetchInternProfile($pdo, $userId);
    $savedFilename = $saved['profile_image'] ?? '';

    if ($savedFilename !== $newFilename) {
        @unlink($destination);
        setProfileFlash('error', 'Upload failed while saving the profile record.');
        error_log('Intern profile upload failed: database did not persist filename for user_id=' . $userId . ' expected=' . $newFilename . ' actual=' . $savedFilename);
        redirectProfile();
    }

    $previousImage = trim((string) ($intern['profile_image'] ?? ''));
    if ($previousImage !== '' && $previousImage !== $defaultImage) {
        deleteManagedFile($uploadDir, $previousImage);
    }

    setProfileFlash('success', 'Profile picture uploaded successfully.');
    redirectProfile();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'profile.php']);

    $intern = fetchInternProfile($pdo, $userId);
    if (!$intern) {
        setProfileFlash('error', 'Intern profile record was not found.');
        redirectProfile();
    }

    if (isset($_POST['update_profile_info'])) {
        $stmt = $pdo->prepare("
            UPDATE interns
            SET first_name = ?, middle_name = ?, last_name = ?, contact_no = ?, university = ?, course = ?, year_level = ?
            WHERE intern_id = ?
        ");

        $stmt->execute([
            trim((string) ($_POST['first_name'] ?? '')),
            trim((string) ($_POST['middle_name'] ?? '')),
            trim((string) ($_POST['last_name'] ?? '')),
            trim((string) ($_POST['contact_no'] ?? '')),
            trim((string) ($_POST['university'] ?? '')),
            trim((string) ($_POST['course'] ?? '')),
            trim((string) ($_POST['year_level'] ?? '')),
            $intern['intern_id'],
        ]);

        upsertEntityAddress((int) $intern['intern_id'], 'intern', [
            'address_line' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'province' => trim((string) ($_POST['province'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')) ?: 'Philippines',
        ]);

        setProfileFlash('success', 'Profile updated successfully.');
        redirectProfile();
    }

    if (isset($_POST['update_profile_image'])) {
        handleInternImageUpload($pdo, $intern, $userId, $uploadDir, $defaultImage);
    }

    if (isset($_POST['delete_image'])) {
        $currentImage = trim((string) ($intern['profile_image'] ?? ''));

        if ($currentImage !== '' && $currentImage !== $defaultImage) {
            deleteManagedFile($uploadDir, $currentImage);
        }

        $stmt = $pdo->prepare("UPDATE interns SET profile_image = ? WHERE intern_id = ?");
        $stmt->execute([$defaultImage, $intern['intern_id']]);

        setProfileFlash('success', 'Profile picture deleted successfully.');
        redirectProfile();
    }
}

$intern = fetchInternProfile($pdo, $userId);
if (!$intern) {
    exit('Intern profile not found.');
}

$imageUrl = buildImageUrl($intern, $uploadDir, $uploadUrl, $defaultImage);

$flash = $_SESSION['profile_flash'] ?? null;
unset($_SESSION['profile_flash']);

$notificationStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM notifications
    WHERE user_id = ? AND is_read = 0
");
$notificationStmt->execute([$userId]);
$unread = (int) $notificationStmt->fetchColumn();
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

    <div class="main-content">
        <h2>My Profile</h2>

        <div class="row">
            <div class="col-md-6">
                <div class="profile-image-container">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Profile Image" id="currentProfileImage">

                    <form method="POST" enctype="multipart/form-data">
                        <?= csrf_input() ?>
                        <div class="form-group">
                            <label>Change Profile Picture</label>
                            <input type="file" name="profile_image" accept="image/jpeg,image/png" required>
                        </div>
                        <button type="submit" name="update_profile_image">Upload Picture</button>
                    </form>

                    <form method="POST" style="margin-top:10px;">
                        <?= csrf_input() ?>
                        <button type="submit" name="delete_image">Delete Image</button>
                    </form>
                </div>

                <div class="form-container">
                    <form method="POST">
                        <?= csrf_input() ?>

                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars((string) ($intern['first_name'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars((string) ($intern['middle_name'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars((string) ($intern['last_name'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Contact No</label>
                            <input type="text" name="contact_no" value="<?= htmlspecialchars((string) ($intern['contact_no'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" value="<?= htmlspecialchars((string) ($intern['address'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars((string) ($intern['city'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label>Province</label>
                            <input type="text" name="province" value="<?= htmlspecialchars((string) ($intern['province'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code" value="<?= htmlspecialchars((string) ($intern['postal_code'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label>University</label>
                            <input type="text" name="university" value="<?= htmlspecialchars((string) ($intern['university'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" name="course" value="<?= htmlspecialchars((string) ($intern['course'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Year Level</label>
                            <input type="text" name="year_level" value="<?= htmlspecialchars((string) ($intern['year_level'] ?? '')) ?>">
                        </div>

                        <button type="submit" name="update_profile_info">Update Profile</button>
                    </form>
                </div>
            </div>

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

                        <button type="submit">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../html/logout_modal.html'; ?>
    <script src="../js/logout_modal.js"></script>

    <?php include '../html/internprofile_modal.html'; ?>
    <script src="../js/internprofile_modal.js"></script>

    <?php include '../html/internupdate_modal.html'; ?>
    <script src="../js/internupdate_modal.js"></script>

    <?php include '../html/internpassword_modal.html'; ?>
    <script src="../js/internpassword_modal.js"></script>

    <?php if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    if (typeof showNotification === 'function') {
                        showNotification(
                            <?= json_encode((string) $flash['message']) ?>,
                            <?= json_encode(($flash['type'] ?? 'success') !== 'error') ?>
                        );
                    }
                }, 100);
            });
        </script>
    <?php endif; ?>

    <script src="../js/responsive-nav.js"></script>
</body>

</html>
