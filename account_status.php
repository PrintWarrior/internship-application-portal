<?php
require_once 'includes/functions.php';

startSecureSession();
sendSecurityHeaders();
ensureAccountAppealSchema($pdo);

$restrictedUserId = (int) ($_SESSION['restricted_user_id'] ?? 0);

if ($restrictedUserId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT user_id, email, user_type, status, status_reason, appeal_allowed
    FROM users
    WHERE user_id = ?
    LIMIT 1
");
$stmt->execute([$restrictedUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    unset($_SESSION['restricted_user_id'], $_SESSION['restricted_user_type']);
    header('Location: index.php');
    exit;
}

if ($user['status'] === 'active') {
    unset($_SESSION['restricted_user_id'], $_SESSION['restricted_user_type']);
    $_SESSION['success'] = 'Your account is active again. Please sign in.';
    header('Location: index.php');
    exit;
}

$flash = $_SESSION['account_status_flash'] ?? null;
unset($_SESSION['account_status_flash']);

$pendingAppealStmt = $pdo->prepare("
    SELECT *
    FROM account_appeals
    WHERE user_id = ? AND status = 'pending'
    ORDER BY created_at DESC
    LIMIT 1
");
$pendingAppealStmt->execute([$restrictedUserId]);
$pendingAppeal = $pendingAppealStmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'account_status.php']);

    if (!empty($user['appeal_allowed']) && !$pendingAppeal) {
        $appealMessage = trim((string) ($_POST['appeal_message'] ?? ''));

        if ($appealMessage === '') {
            $_SESSION['account_status_flash'] = [
                'type' => 'error',
                'message' => 'Please explain why you are requesting an appeal.',
            ];
            header('Location: account_status.php');
            exit;
        }

        $insertStmt = $pdo->prepare("
            INSERT INTO account_appeals (user_id, submitted_status, reason_snapshot, appeal_message)
            VALUES (?, ?, ?, ?)
        ");
        $insertStmt->execute([
            $restrictedUserId,
            $user['status'],
            $user['status_reason'],
            $appealMessage,
        ]);

        $_SESSION['account_status_flash'] = [
            'type' => 'success',
            'message' => 'Your appeal has been submitted. An administrator will review it.',
        ];
        header('Location: account_status.php');
        exit;
    }
}

$statusTitle = $user['status'] === 'banned' ? 'Account Banned' : 'Account Suspended';
$statusReason = trim((string) ($user['status_reason'] ?? ''));
if ($statusReason === '') {
    $statusReason = 'No specific reason was recorded by the administrator.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($statusTitle) ?></title>
    <link rel="icon" href="assets/img/icon.png" type="image/x-icon">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #000000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .status-shell {
            width: 100%;
            max-width: 760px;
            border: 2px solid #000000;
            background: #ffffff;
            box-shadow: 8px 8px 0 #000000;
            padding: 32px;
        }

        h1 {
            font-size: 30px;
            text-transform: uppercase;
            margin-bottom: 12px;
            border-bottom: 3px solid #000000;
            padding-bottom: 12px;
        }

        .lead,
        .meta,
        .reason-box,
        .pending-box,
        .flash {
            margin-bottom: 18px;
        }

        .meta strong,
        .reason-box strong,
        .pending-box strong {
            display: inline-block;
            margin-bottom: 6px;
        }

        .reason-box,
        .pending-box,
        .flash,
        textarea {
            border: 2px solid #000000;
        }

        .reason-box,
        .pending-box,
        .flash {
            padding: 16px;
        }

        .flash.success {
            background: #000000;
            color: #ffffff;
        }

        .flash.error {
            background: #ffffff;
            color: #000000;
        }

        form {
            margin-top: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            padding: 12px;
            font: inherit;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            background: #f3f3f3;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .btn,
        .btn-link {
            display: inline-block;
            padding: 12px 18px;
            border: 2px solid #000000;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: none;
            background: #000000;
            color: #ffffff;
            cursor: pointer;
        }

        .btn-link {
            background: #ffffff;
            color: #000000;
        }

        .btn:hover,
        .btn-link:hover {
            background: #ffffff;
            color: #000000;
        }

        .btn-link:hover {
            background: #000000;
            color: #ffffff;
        }

        @media (max-width: 640px) {
            .status-shell {
                padding: 22px;
                box-shadow: none;
            }

            h1 {
                font-size: 24px;
            }

            .btn,
            .btn-link {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="status-shell">
        <h1><?= htmlspecialchars($statusTitle) ?></h1>
        <p class="lead">This account cannot access the portal at the moment.</p>

        <?php if ($flash): ?>
            <div class="flash <?= ($flash['type'] ?? 'success') === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
            </div>
        <?php endif; ?>

        <div class="meta">
            <p><strong>Account Email:</strong> <?= htmlspecialchars((string) $user['email']) ?></p>
            <p><strong>Current Status:</strong> <?= htmlspecialchars(ucfirst((string) $user['status'])) ?></p>
        </div>

        <div class="reason-box">
            <strong>Administrator Reason</strong>
            <p><?= nl2br(htmlspecialchars($statusReason)) ?></p>
        </div>

        <?php if ($pendingAppeal): ?>
            <div class="pending-box">
                <strong>Appeal Pending</strong>
                <p>Your appeal was submitted on <?= date('F d, Y h:i A', strtotime((string) $pendingAppeal['created_at'])) ?>.</p>
                <p><?= nl2br(htmlspecialchars((string) $pendingAppeal['appeal_message'])) ?></p>
            </div>
        <?php elseif (!empty($user['appeal_allowed'])): ?>
            <form method="POST">
                <?= csrf_input() ?>
                <label for="appeal_message">Appeal Message</label>
                <textarea id="appeal_message" name="appeal_message" placeholder="Explain why you believe this status should be reviewed." required></textarea>
                <div class="actions">
                    <button type="submit" class="btn">Submit Appeal</button>
                    <a href="logout.php" class="btn-link">Logout</a>
                </div>
            </form>
        <?php else: ?>
            <div class="pending-box">
                <strong>Appeal Unavailable</strong>
                <p>This account action is marked as final and cannot be appealed through the portal.</p>
            </div>
            <div class="actions">
                <a href="logout.php" class="btn-link">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
