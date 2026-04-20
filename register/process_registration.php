<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

startSecureSession();
sendSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['type'])) {
    header('Location: ../html/register.php');
    exit;
}

requireValidCsrfToken(['redirect' => '../html/register.php']);

$type = $_GET['type'];
$email = $_POST['email'] ?? '';
$dbType = $type === 'company' ? 'staff' : $type;

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Fix: Use urlencode for the message
    $message = urlencode('Invalid email format.');
    header("Location: ../html/register_{$type}.php?error=1&message={$message}");
    exit;
}

// Check if email already exists
try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = urlencode('Email already registered.');
        header("Location: ../html/register_{$type}.php?error=1&message={$message}");
        exit;
    }
} catch (Exception $e) {
    $message = urlencode('Database error. Please try again.');
    header("Location: ../html/register_{$type}.php?error=1&message={$message}");
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert into users (password NULL, verified 0)
    $stmt = $pdo->prepare("INSERT INTO users (email, user_type, verified) VALUES (?, ?, 0)");
    $stmt->execute([$email, $dbType]);
    $user_id = $pdo->lastInsertId();

    // Insert into specific profile table
    if ($type === 'company' || $type === 'staff') {

    if (empty($_POST['company_name'])) {
        throw new Exception('Company name is required.');
    }

    // Insert company
    $stmt = $pdo->prepare("
        INSERT INTO companies 
        (company_name, contact_phone, industry, contact_person, website, description, contact_email) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['company_name'], 
        $_POST['contact_phone'] ?? null, 
        $_POST['industry'] ?? null,
        $_POST['contact_person'] ?? null,
        $_POST['website'] ?? null,
        $_POST['description'] ?? null,
        $_POST['contact_email'] ?? null
    ]);

    // ✅ GET company_id (VERY IMPORTANT)
    $company_id = $pdo->lastInsertId();
    upsertEntityAddress((int) $company_id, 'company', [
        'address_line' => $_POST['address'] ?? null,
        'city' => $_POST['city'] ?? null,
        'province' => $_POST['province'] ?? null,
        'postal_code' => $_POST['postal_code'] ?? null,
        'country' => $_POST['country'] ?? 'Philippines'
    ]);

    // ✅ INSERT INTO staffs TABLE
    if (!empty($_POST['contact_person'])) {

        // Split name (basic)
        $nameParts = explode(' ', trim($_POST['contact_person']));
        $first_name = $nameParts[0];
        $last_name = $nameParts[1] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO staffs 
            (user_id, company_id, first_name, last_name, email, contact_no, position)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $company_id,
            $first_name,
            $last_name,
            $_POST['contact_email'] ?? null,
            $_POST['contact_phone'] ?? null,
            'Contact Person' // default role
        ]);
    }
}
     elseif ($type === 'intern') {
        // Check if required fields exist
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['contact_no']) || 
            empty($_POST['university']) || empty($_POST['course'])) {
            throw new Exception('Please fill in all required fields.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO interns (
                user_id,
                first_name,
                middle_name,
                last_name,
                gender,
                birthdate,
                age,
                contact_no,
                university,
                course,
                year_level
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_POST['first_name'],
            $_POST['middle_name'] ?? null,
            $_POST['last_name'],
            $_POST['gender'] ?? null,
            $_POST['birthdate'] ?? null,
            $_POST['age'] ?? null,
            $_POST['contact_no'],
            $_POST['university'],
            $_POST['course'],
            $_POST['year_level'] ?? null
        ]);
        $intern_id = $pdo->lastInsertId();
        upsertEntityAddress((int) $intern_id, 'intern', [
            'address_line' => $_POST['address'] ?? null,
            'city' => $_POST['city'] ?? null,
            'province' => $_POST['province'] ?? null,
            'postal_code' => $_POST['postal_code'] ?? null,
            'country' => $_POST['country'] ?? 'Philippines'
        ]);
    } else {
        throw new Exception('Invalid user type.');
    }

    // Create notification for admin (admin user_id = 1)
    $messageType = $type === 'company' ? 'staff' : $type;
    $message = "New $messageType registered: $email";
    $action_url = "send_verification.php?user_id=$user_id";
    $action_label = "Send Verification Link";
    createNotification(1, $message, $action_url, $action_label, $user_id);

    $pdo->commit();

    $subject = 'Registration Received - Intern Application Portal';
    $body = "
        <div style='background:#f2f4f6;padding:40px;font-family:Arial,sans-serif'>
            <div style='max-width:600px;margin:auto;background:white;padding:30px'>
                <h2 style='color:#2c3e50;text-align:center'>Registration Received</h2>
                <p>Hello,</p>
                <p>Your registration for the Internship Application Portal has been received successfully.</p>
                <p>Your account is currently pending administrator approval. After approval, you will receive a separate verification email with the link needed to activate your account.</p>
                <p style='margin-top:25px;font-size:14px;color:#666'>
                    If you did not submit this registration, you can ignore this email.
                </p>
                <hr style='margin:30px 0'>
                <p style='font-size:12px;color:#888'>Internship Application Portal</p>
            </div>
        </div>
    ";

    $mailSent = sendEmail($email, $subject, $body);
    if ($mailSent) {
        $success_message = urlencode('Registration successful! A confirmation email has been sent. Your account is pending administrator approval, and a verification email will be sent after approval.');
    } else {
        $success_message = urlencode('Registration successful! Your account is pending administrator approval. We could not send the registration confirmation email right now, but the account was created.');
    }

    header("Location: ../html/register_{$type}.php?success=1&message={$success_message}");
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    // Error redirect with parameters
    $error_message = urlencode($e->getMessage() ?: 'Registration failed. Please try again.');
    header("Location: ../html/register_{$type}.php?error=1&message={$error_message}");
    exit;
}
?>
