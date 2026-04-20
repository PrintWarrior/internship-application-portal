<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contracts.php');
    exit;
}

requireValidCsrfToken(['redirect' => 'contracts.php']);

$contract_id = (int)$_POST['contract_id'];

// Verify contract belongs to this intern - FIXED: using correct column name 'title'
$stmt = $pdo->prepare("
    SELECT ct.*, a.intern_id, i.title, i.internship_id
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    WHERE ct.contract_id = ? AND ir.user_id = ?
");
$stmt->execute([$contract_id, $user_id]);
$contract = $stmt->fetch();

if (!$contract) {
    $_SESSION['error'] = "Contract not found.";
    header('Location: contracts.php');
    exit;
}

if ($contract['signed_file']) {
    $_SESSION['error'] = "Contract already signed.";
    header('Location: contracts.php');
    exit;
}

// Handle file upload
if (isset($_FILES['signed_file']) && $_FILES['signed_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/contracts/';
    $filename = storeUploadedPdf($_FILES['signed_file'], 'signed_contract_' . $contract_id, $upload_dir);

    if ($filename === null) {
        $_SESSION['error'] = "Invalid file. Only PDF uploads up to 5MB are allowed.";
        header('Location: contracts.php');
        exit;
    }

    // Update database
    $updateStmt = $pdo->prepare("
        UPDATE contracts 
        SET signed_file = ?, signed_date = NOW() 
        WHERE contract_id = ?
    ");
    $updateStmt->execute([$filename, $contract_id]);

    // Get company for notification
    $companyStmt = $pdo->prepare("
        SELECT i.company_id, i.title as internship_title
        FROM contracts ct
        JOIN applications a ON ct.application_id = a.application_id
        JOIN internships i ON a.internship_id = i.internship_id
        WHERE ct.contract_id = ?
    ");
    $companyStmt->execute([$contract_id]);
    $companyData = $companyStmt->fetch();

    if ($companyData) {
        $message = "An intern has signed a contract for '" . $companyData['internship_title'] . "'. Please review and confirm.";
        notifyCompanyStaff($companyData['company_id'], $message, null, null, 'company/contracts.php');
    }

    $_SESSION['success'] = "Contract signed and submitted successfully!";
} else {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize directive.",
        UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE directive.",
        UPLOAD_ERR_PARTIAL => "File was only partially uploaded.",
        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION => "File upload stopped by extension."
    ];
    
    $error_code = isset($_FILES['signed_file']['error']) ? $_FILES['signed_file']['error'] : UPLOAD_ERR_NO_FILE;
    $error_message = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : "Unknown upload error.";
    
    $_SESSION['error'] = "Upload failed: " . $error_message;
}

header('Location: contracts.php');
exit;
?>
