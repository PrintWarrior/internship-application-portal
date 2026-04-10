<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// Get internship info first
$stmt = $pdo->prepare("SELECT title FROM internships WHERE internship_id=?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    logAction("Approve Internship", "Attempted to approve non-existent internship ID $id");
    header("Location: manage_internships.php");
    exit;
}

// Approve internship
$stmt = $pdo->prepare("UPDATE internships SET status='approved' WHERE internship_id=?");
$stmt->execute([$id]);

$message = "Your internship '{$job['title']}' has been approved by the admin.";
// Notify all staff users under the company
$stmt = $pdo->prepare("SELECT company_id FROM internships WHERE internship_id = ?");
$stmt->execute([$id]);
$companyId = $stmt->fetchColumn();

if ($companyId) {
    notifyCompanyStaff($companyId, "Your internship has been approved by the admin.");
}

// Log action
logAction(
    "Approve Internship",
    "Approved internship '{$job['title']}' (ID $id)"
);

header("Location: manage_internships.php");
exit;
