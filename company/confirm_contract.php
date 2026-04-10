<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$company = getStaffCompanyContext($_SESSION['user_id']);
$company_id = $company['company_id'];

// Get the contract ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: contracts.php');
    exit;
}

$contract_id = $_GET['id'];

// First, verify that this contract belongs to this company
$stmt = $pdo->prepare("
    SELECT ct.contract_id
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    WHERE ct.contract_id = ? AND i.company_id = ?
");
$stmt->execute([$contract_id, $company_id]);
$contract = $stmt->fetch();

if (!$contract) {
    // Contract not found or doesn't belong to this company
    header('Location: contracts.php');
    exit;
}

// Update the contract to confirm HR approval
$update = $pdo->prepare("UPDATE contracts SET hr_confirmed = 1 WHERE contract_id = ?");
$update->execute([$contract_id]);

// Optional: create notification for intern
$stmt = $pdo->prepare("
    INSERT INTO notifications (user_id, message) 
    SELECT ir.user_id, CONCAT('Your contract has been confirmed by ', c.company_name)
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    WHERE ct.contract_id = ?
");
$stmt->execute([$contract_id]);

// Redirect back to contracts page with success message
header('Location: contracts.php');
exit;
