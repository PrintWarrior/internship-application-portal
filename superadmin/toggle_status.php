<?php
session_start();
require_once '../includes/functions.php';

requireAdminAreaAccess();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_users.php");
    exit;
}

requireValidCsrfToken(['redirect' => 'manage_users.php']);

function buildStatusEmailBody($status, $email)
{
    $statusHeadings = [
        'active' => 'Your account has been activated',
        'suspended' => 'Your account has been suspended',
        'banned' => 'Your account has been banned',
    ];

    $statusMessages = [
        'active' => 'An administrator has activated your account. You can now sign in to the Internship Application Portal.',
        'suspended' => 'An administrator has suspended your account. You will not be able to sign in while the suspension is in effect.',
        'banned' => 'An administrator has banned your account. This is done for severe violations. You will not be able to sign in or create a new account with this email.',
    ];

    $heading = $statusHeadings[$status] ?? 'Your account status has changed';
    $message = $statusMessages[$status] ?? 'An administrator has updated your account status.';

    return "
    <div style='background:#f5f5f5;padding:40px;font-family:Arial,sans-serif'>
        <div style='max-width:600px;margin:auto;background:#ffffff;padding:30px;border:1px solid #000000'>
            <h2 style='color:#000000;margin-top:0'>Internship Application Portal</h2>
            <p style='font-size:18px;color:#000000;font-weight:bold'>$heading</p>
            <p style='font-size:15px;color:#222222;line-height:1.6'>$message</p>
            <p style='font-size:15px;color:#222222;line-height:1.6'>Account email: $email</p>
            <p style='font-size:13px;color:#555555;line-height:1.6;margin-top:24px'>If you need help, please contact the portal administrator.</p>
        </div>
    </div>
    ";
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$requestedStatus = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
$allowedStatuses = ['active', 'suspended', 'banned'];

if ($id <= 0 || !in_array($requestedStatus, $allowedStatuses, true)) {
    header("Location: manage_users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT email, user_type, status FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['user_type'] === 'superadmin') {
    header("Location: manage_users.php");
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->execute([$requestedStatus, $id]);

$subjects = [
    'active' => 'Your account has been activated',
    'suspended' => 'Your account has been suspended',
    'banned' => 'Your account has been banned',
];

$emailSent = sendEmail(
    $user['email'],
    $subjects[$requestedStatus] ?? 'Your account status has changed',
    buildStatusEmailBody($requestedStatus, htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'))
);

if ($emailSent) {
    $_SESSION['feedback_message'] = 'User status updated to ' . $requestedStatus . ' and notification email sent to ' . $user['email'] . '.';
    $_SESSION['feedback_type'] = 'success';
} else {
    $_SESSION['feedback_message'] = 'User status updated to ' . $requestedStatus . ', but the notification email could not be sent to ' . $user['email'] . '.';
    $_SESSION['feedback_type'] = 'error';
}

header("Location: manage_users.php");
exit;
?>
