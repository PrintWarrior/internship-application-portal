<?php
require_once 'db.php';
require_once 'phpmailer/vendor/autoload.php'; // adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'internshipapplicationportal@gmail.com';
    $mail->Password = 'fqwzszpjofuhlqzf';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('internshipapplicationportal@gmail.com', 'Intern Portal');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    return $mail->send();
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}

function getAdminNotificationRecipients($preferredUserId = null) {
    global $pdo;

    if ($preferredUserId) {
        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE user_id = ? AND user_type = 'admin' AND status = 'active'
        ");
        $stmt->execute([$preferredUserId]);
        $preferred = $stmt->fetchColumn();

        if ($preferred) {
            return [$preferred];
        }
    }

    $stmt = $pdo->query("
        SELECT user_id
        FROM users
        WHERE user_type = 'admin' AND status = 'active'
    ");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function createNotification($admin_id, $message, $action_url, $action_label, $related_user_id) {
    global $pdo;

    $recipientIds = getAdminNotificationRecipients($admin_id);
    if (!$recipientIds) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, action_url, action_label, related_user_id, link)
        VALUES (?, ?, ?, ?, ?, '')
    ");

    foreach ($recipientIds as $recipientId) {
        $stmt->execute([$recipientId, $message, $action_url, $action_label, $related_user_id]);
    }

    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireStaffUser() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
        header('Location: ../index.php');
        exit;
    }
}

function getStaffCompanyContext($userId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            s.staff_id,
            s.user_id,
            s.company_id,
            s.first_name,
            s.last_name,
            s.email AS staff_email,
            s.contact_no AS staff_contact_no,
            s.position,
            s.profile_image AS staff_profile_image,
            c.company_name,
            c.contact_phone,
            c.address,
            c.industry,
            c.contact_person,
            c.website,
            c.description,
            c.profile_image,
            c.city,
            c.province,
            c.postal_code,
            c.country,
            c.contact_email,
            c.created_at,
            c.updated_at
        FROM staffs s
        JOIN companies c ON s.company_id = c.company_id
        WHERE s.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCompanyStaffUserIds($companyId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM staffs
        WHERE company_id = ? AND user_id IS NOT NULL
    ");
    $stmt->execute([$companyId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function notifyCompanyStaff($companyId, $message, $actionUrl = null, $actionLabel = null, $link = '', $relatedUserId = null) {
    global $pdo;

    $userIds = getCompanyStaffUserIds($companyId);
    if (!$userIds) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, action_url, action_label, related_user_id, link)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($userIds as $userId) {
        $stmt->execute([$userId, $message, $actionUrl, $actionLabel, $relatedUserId, $link]);
    }

    return true;
}

function logAction($action, $description = null) {
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO system_logs (user_id, action, description)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$_SESSION['user_id'], $action, $description]);
}
?>
