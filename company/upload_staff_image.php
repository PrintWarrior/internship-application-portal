<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireStaffUser();
    requireValidCsrfToken(['redirect' => 'staff_profile.php']);

    $company = getStaffCompanyContext($_SESSION['user_id']);

    $staff_id = $_POST['staff_id'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $newName = storeUploadedImage($_FILES['profile_image'], 'staff', '../assets/img/profile/');
        if ($newName !== null) {
                $stmt = $pdo->prepare("UPDATE staffs SET profile_image=? WHERE staff_id=? AND company_id=?");
                $stmt->execute([$newName, $staff_id, $company['company_id']]);
        }
    }

    header("Location: staff_profile.php");
    exit;
}
