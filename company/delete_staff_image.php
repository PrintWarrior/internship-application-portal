<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireStaffUser();
    requireValidCsrfToken(['redirect' => 'staff_profile.php']);

    $company = getStaffCompanyContext($_SESSION['user_id']);

    $staff_id = $_POST['staff_id'];

    // get current image
    $stmt = $pdo->prepare("SELECT profile_image FROM staffs WHERE staff_id=? AND company_id=?");
    $stmt->execute([$staff_id, $company['company_id']]);
    $image = $stmt->fetchColumn();

    if ($image && $image !== 'default.png') {
        deleteManagedFile('../assets/img/profile/', $image);
    }

    $stmt = $pdo->prepare("UPDATE staffs SET profile_image='default.png' WHERE staff_id=? AND company_id=?");
    $stmt->execute([$staff_id, $company['company_id']]);

    header("Location: staff_profile.php");
    exit;
}
