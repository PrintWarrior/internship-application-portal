<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_id = ?");
$stmt->execute([$id]);
$companyExists = (bool) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT user_id FROM staffs WHERE company_id = ?");
$stmt->execute([$id]);
$staffUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($companyExists) {

    // delete company
    $stmt = $pdo->prepare("DELETE FROM companies WHERE company_id=?");
    $stmt->execute([$id]);

    // delete linked staff user login(s)
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
    foreach ($staffUserIds as $staffUserId) {
        if ($staffUserId) {
            $stmt->execute([$staffUserId]);
        }
    }

    logAction("Delete Company", "Deleted company ID $id");
}

header("Location: manage_companies.php");
exit;
