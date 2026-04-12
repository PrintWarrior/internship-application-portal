<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['type'])) {
    header('Location: ../html/register.html');
    exit;
}

$type = $_GET['type'];
$email = $_POST['email'] ?? '';
$dbType = $type === 'company' ? 'staff' : $type;

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Fix: Use urlencode for the message
    $message = urlencode('Invalid email format.');
    header("Location: ../html/register_{$type}.html?error=1&message={$message}");
    exit;
}

// Check if email already exists
try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = urlencode('Email already registered.');
        header("Location: ../html/register_{$type}.html?error=1&message={$message}");
        exit;
    }
} catch (Exception $e) {
    $message = urlencode('Database error. Please try again.');
    header("Location: ../html/register_{$type}.html?error=1&message={$message}");
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

    // Send verification email to the new user
    $subject = 'Verify Your Email - Intern Application Portal';
    $body = "
        <html>
            <body>
                <h2>Welcome to Intern Application Portal!</h2>
                <p>Thank you for registering. Please wait a few moments for your verification email to arrive.</p>
                <br>
                <p>Best regards,<br>Intern Application Portal Team</p>
            </body>
        </html>
    ";
    
    try {
        sendEmail($email, $subject, $body);
    } catch (Exception $e) {
        // Log error but don't fail registration
        error_log("Failed to send verification email to $email: " . $e->getMessage());
    }

    $pdo->commit();
    
    // Success redirect with parameters - Updated message to include "check your mail"
    $success_message = urlencode('Registration successful! Please check your mail for verification instructions.');
    header("Location: ../html/register_{$type}.html?success=1&message={$success_message}");
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    // Error redirect with parameters
    $error_message = urlencode($e->getMessage() ?: 'Registration failed. Please try again.');
    header("Location: ../html/register_{$type}.html?error=1&message={$error_message}");
    exit;
}
?>
