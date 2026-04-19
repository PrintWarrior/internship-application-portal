<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT title FROM internships WHERE internship_id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    logAction("Approve Internship", "Superadmin attempted to approve non-existent internship ID $id");
    header('Location: manage_internships.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE internships SET status = 'approved' WHERE internship_id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("SELECT company_id FROM internships WHERE internship_id = ?");
$stmt->execute([$id]);
$companyId = $stmt->fetchColumn();

if ($companyId) {
    notifyCompanyStaff($companyId, "Your internship has been approved by the superadmin.");
}

logAction(
    "Approve Internship",
    "Superadmin approved internship '{$job['title']}' (ID $id)"
);

header('Location: manage_internships.php');
exit;
