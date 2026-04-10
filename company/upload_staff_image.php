<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireStaffUser();

    $company = getStaffCompanyContext($_SESSION['user_id']);

    $staff_id = $_POST['staff_id'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {

        $allowed = ['image/jpeg','image/png'];

        if (in_array($_FILES['profile_image']['type'], $allowed)
            && $_FILES['profile_image']['size'] <= 2 * 1024 * 1024) {

            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $newName = uniqid('staff_', true) . '.' . $ext;
            $path = '../assets/img/profile/' . $newName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $path)) {

                $stmt = $pdo->prepare("UPDATE staffs SET profile_image=? WHERE staff_id=? AND company_id=?");
                $stmt->execute([$newName, $staff_id, $company['company_id']]);
            }
        }
    }

    header("Location: staff_profile.php");
    exit;
}
