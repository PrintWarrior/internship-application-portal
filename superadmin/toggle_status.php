<?php include '../includes/functions.php'; ?>

<?php
session_start();

function buildStatusEmailBody($status, $email)
{
    $statusHeadings = [
        'active' => 'Your account has been activated',
        'suspended' => 'Your account has been suspended',
        'banned' => 'Your account has been banned',
    ];

    $statusMessages = [
        'active' => 'A superadmin has activated your account. You can now sign in to the Internship Application Portal.',
        'suspended' => 'A superadmin has suspended your account. You will not be able to sign in while the suspension is in effect.',
        'banned' => 'A superadmin has banned your account. This is done for severe violations. You will not be able to sign in or create a new account with this email.',
    ];

    $heading = $statusHeadings[$status] ?? 'Your account status has changed';
    $message = $statusMessages[$status] ?? 'A superadmin has updated your account status.';

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

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
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

$current = $user['status'];

// cycle: active → suspended → banned → active
$newStatus = 'active';

if ($current === 'active') $newStatus = 'suspended';
elseif ($current === 'suspended') $newStatus = 'banned';
elseif ($current === 'banned') $newStatus = 'active';

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->execute([$newStatus, $id]);

$subjects = [
    'active' => 'Your account has been activated',
    'suspended' => 'Your account has been suspended',
    'banned' => 'Your account has been banned',
];

$emailSent = sendEmail(
    $user['email'],
    $subjects[$newStatus] ?? 'Your account status has changed',
    buildStatusEmailBody($newStatus, htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'))
);

if ($emailSent) {
    $_SESSION['feedback_message'] = 'User status updated to ' . $newStatus . ' and notification email sent to ' . $user['email'] . '.';
    $_SESSION['feedback_type'] = 'success';
} else {
    $_SESSION['feedback_message'] = 'User status updated to ' . $newStatus . ', but the notification email could not be sent to ' . $user['email'] . '.';
    $_SESSION['feedback_type'] = 'error';
}

header("Location: manage_users.php");
exit;
