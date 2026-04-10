<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM internships WHERE internship_id=?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    logAction("Edit Internship", "Attempted to edit non-existent internship ID $id");
    die("Internship not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $deadline = $_POST['deadline'];

    $stmt = $pdo->prepare("
        UPDATE internships
        SET title=?, deadline=?
        WHERE internship_id=?
    ");

    $stmt->execute([$title, $deadline, $id]);

    // LOG ACTION
    logAction(
        "Edit Internship",
        "Updated internship ID $id | Title: '{$job['title']}' → '$title', Deadline: {$job['deadline']} → $deadline"
    );

    header("Location: manage_internships.php");
    exit;
}
?>

<form method="POST">

<label>Title</label>
<input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>">

<label>Deadline</label>
<input type="date" name="deadline" value="<?= $job['deadline'] ?>">

<button type="submit">Update</button>

</form>