<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'create_users.php']);

    $email = trim($_POST['email']);
    $type = $_POST['user_type'];
    $password = $_POST['password'] ?? '';
    $verified = isset($_POST['verified']) ? (int) $_POST['verified'] : 0;
    $firstLogin = isset($_POST['first_login']) ? (int) $_POST['first_login'] : 1;

    if (!in_array($verified, [0, 1], true)) {
        $verified = 0;
    }

    if (!in_array($firstLogin, [0, 1], true)) {
        $firstLogin = 1;
    }

    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        $error = "Email already exists. Please use a different email address.";
    } elseif ($password === '') {
        $error = "Password is required.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO users (email, password_hash, user_type, verified, first_login, status)
             VALUES (?, ?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$email, $passwordHash, $type, $verified, $firstLogin]);
        
        // Log the action
        logAction('Create user', 'Created new ' . $type . ' user: ' . $email);
        
        header("Location: manage_users.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/super_create.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">
                Notifications <span class="badge"><?= $unread ?></span>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- MAIN WRAPPER -->
    <div class="wrapper">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content">
            <h1>Create User</h1>

            <?php if (isset($error)): ?>
                <div class="alert-error" style="border: 2px solid #000000; padding: 12px; margin-bottom: 20px; background-color: #f5f5f5;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-section">
            <form method="POST">
                <?= csrf_input() ?>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter email address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>

                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <select id="user_type" name="user_type">
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="verified">Verified</label>
                        <select id="verified" name="verified">
                            <option value="1">1 - Verified</option>
                            <option value="0">0 - Not Verified</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="first_login">First Login</label>
                        <select id="first_login" name="first_login">
                            <option value="1">1 - Has not logged in yet</option>
                            <option value="0">0 - Has already logged in</option>
                        </select>
                    </div>

                    <button type="submit">Create User</button>
                </form>
            </div>
        </div>

    </div>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
