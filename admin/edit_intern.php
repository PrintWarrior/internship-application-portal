<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM interns WHERE intern_id=?");
$stmt->execute([$id]);
$intern = $stmt->fetch();

if(!$intern){
    logAction("Edit Intern", "Attempted to edit non-existent intern ID $id");
    die("Intern not found");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $first = $_POST['first_name'];
    $last  = $_POST['last_name'];

    $stmt = $pdo->prepare("
        UPDATE interns
        SET first_name=?, last_name=?
        WHERE intern_id=?
    ");

    $stmt->execute([$first,$last,$id]);

    // LOG ACTION
    logAction("Edit Intern", "Updated intern ID $id to $first $last");

    header("Location: manage_interns.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Intern</title>
</head>
<body>

<h2>Edit Intern</h2>

<form method="POST">

First Name<br>
<input type="text" name="first_name" value="<?= htmlspecialchars($intern['first_name']) ?>"><br><br>

Last Name<br>
<input type="text" name="last_name" value="<?= htmlspecialchars($intern['last_name']) ?>"><br><br>

<button type="submit">Update</button>

</form>

<br>
<a href="manage_interns.php">Cancel</a>

</body>
</html>