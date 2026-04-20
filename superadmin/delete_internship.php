<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_internships.php');
    exit;
}

requireValidCsrfToken(['redirect' => 'manage_internships.php']);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

$stmt = $pdo->prepare("DELETE FROM applications WHERE internship_id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("DELETE FROM internships WHERE internship_id = ?");
$stmt->execute([$id]);

logAction("Delete Internship", "Superadmin deleted internship ID $id");

header('Location: manage_internships.php');
exit;
