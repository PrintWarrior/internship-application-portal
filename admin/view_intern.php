<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT i.*, u.email, u.created_at
    FROM interns i
    JOIN users u ON i.user_id = u.user_id
    WHERE i.intern_id = ?
");

$stmt->execute([$id]);
$intern = $stmt->fetch();

if (!$intern) {
    die("Intern not found.");
}

// Default profile image if none uploaded
$profilePic = !empty($intern['profile_image'])
    ? "../assets/img/profile/" . $intern['profile_image']
    : "../assets/img/profile/default.png";
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Intern</title>

    <style>
        body {
            font-family: Arial;
            background: #fff;
        }

        .container {
            width: 700px;
            margin: auto;
            margin-top: 40px;
            border: 1px solid black;
            padding: 30px;
        }

        .profile-section {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .profile-section img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 2px solid black;
        }

        .info {
            line-height: 1.8;
        }

        label {
            font-weight: bold;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 8px 14px;
            border: 1px solid black;
            text-decoration: none;
            color: black;
        }
    </style>
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">

</head>

<body>

    <div class="container">

        <h2>Intern Profile</h2>

        <div class="profile-section">

            <img src="<?= $profilePic ?>">

            <div class="info">

                <!--<p><label>ID:</label> <?= $intern['intern_id'] ?></p>-->
                <p><label>Name:</label> <?= htmlspecialchars($intern['first_name'] . ' ' . $intern['last_name']) ?></p>
                <p><label>Email:</label> <?= htmlspecialchars($intern['email']) ?></p>
                <p><label>Contact:</label> <?= htmlspecialchars($intern['contact_no'] ?? 'N/A') ?></p>

            </div>

        </div>

        <hr>

        <h3>Education Information</h3>

        <p><label>University:</label> <?= htmlspecialchars($intern['university'] ?? 'N/A') ?></p>
        <p><label>Course:</label> <?= htmlspecialchars($intern['course'] ?? 'N/A') ?></p>
        <p><label>Year Level:</label> <?= htmlspecialchars($intern['year_level'] ?? 'N/A') ?></p>

        <hr>

        <h3>Address</h3>

        <p><label>City:</label> <?= htmlspecialchars($intern['city'] ?? 'N/A') ?></p>
        <p><label>Province:</label> <?= htmlspecialchars($intern['province'] ?? 'N/A') ?></p>
        <!--<p><label>Country:</label> <?= htmlspecialchars($intern['country'] ?? 'N/A') ?></p> -->

        <hr>

        <h3>Account Info</h3>

        <p><label>Joined:</label> <?= date('M d, Y', strtotime($intern['created_at'])) ?></p>

        <a href="manage_interns.php" class="back-btn">Back</a>

    </div>

</body>

</html>