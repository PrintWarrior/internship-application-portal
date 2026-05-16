<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
ensureAccountAppealSchema($pdo);
$panelLabel = getAdminAreaLabel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'appeals.php']);

    $appealId = (int) ($_POST['appeal_id'] ?? 0);
    $decision = trim((string) ($_POST['decision'] ?? ''));
    $adminNotes = trim((string) ($_POST['admin_notes'] ?? ''));

    if ($appealId > 0 && in_array($decision, ['approved', 'rejected'], true)) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.email, u.status AS current_status
            FROM account_appeals a
            JOIN users u ON u.user_id = a.user_id
            WHERE a.appeal_id = ?
            LIMIT 1
        ");
        $stmt->execute([$appealId]);
        $appeal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appeal && $appeal['status'] === 'pending') {
            $pdo->beginTransaction();

            $updateAppealStmt = $pdo->prepare("
                UPDATE account_appeals
                SET status = ?, admin_notes = ?, resolved_by = ?, resolved_at = NOW()
                WHERE appeal_id = ?
            ");
            $updateAppealStmt->execute([$decision, $adminNotes, $_SESSION['user_id'], $appealId]);

            if ($decision === 'approved') {
                $updateUserStmt = $pdo->prepare("
                    UPDATE users
                    SET status = 'active', status_reason = NULL, appeal_allowed = 1
                    WHERE user_id = ?
                ");
                $updateUserStmt->execute([$appeal['user_id']]);
            }

            $pdo->commit();

            $subject = $decision === 'approved'
                ? 'Your account appeal has been approved'
                : 'Your account appeal has been reviewed';
            $message = $decision === 'approved'
                ? 'Your appeal was approved and your account has been restored to active status.'
                : 'Your appeal was reviewed and your current account status remains in place.';
            $notesHtml = $adminNotes !== ''
                ? '<p style="font-size:15px;color:#222;line-height:1.6"><strong>Administrator Notes:</strong><br>' . nl2br(htmlspecialchars($adminNotes, ENT_QUOTES, 'UTF-8')) . '</p>'
                : '';

            sendEmail(
                $appeal['email'],
                $subject,
                "
                <div style='background:#f5f5f5;padding:40px;font-family:Arial,sans-serif'>
                    <div style='max-width:600px;margin:auto;background:#ffffff;padding:30px;border:1px solid #000000'>
                        <h2 style='color:#000000;margin-top:0'>Internship Application Portal</h2>
                        <p style='font-size:18px;color:#000000;font-weight:bold'>$subject</p>
                        <p style='font-size:15px;color:#222222;line-height:1.6'>$message</p>
                        $notesHtml
                    </div>
                </div>
                "
            );

            $_SESSION['feedback_message'] = 'Appeal #' . $appealId . ' has been ' . $decision . '.';
            $_SESSION['feedback_type'] = 'success';
            header('Location: appeals.php');
            exit;
        }
    }

    $_SESSION['feedback_message'] = 'The appeal could not be processed.';
    $_SESSION['feedback_type'] = 'error';
    header('Location: appeals.php');
    exit;
}

$notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifStmt->execute([$_SESSION['user_id']]);
$unread = (int) $notifStmt->fetchColumn();

$itemsPerPage = 9;
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]) ?: 1;

$appealStatsStmt = $pdo->query("
    SELECT
        COUNT(*) AS total_appeals,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_appeals,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_appeals,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_appeals
    FROM account_appeals
");
$appealStats = $appealStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$totalAppealsCount = (int) ($appealStats['total_appeals'] ?? 0);
$pendingAppealsCount = (int) ($appealStats['pending_appeals'] ?? 0);
$approvedAppealsCount = (int) ($appealStats['approved_appeals'] ?? 0);
$rejectedAppealsCount = (int) ($appealStats['rejected_appeals'] ?? 0);

$totalAppealsStmt = $pdo->query("SELECT COUNT(*) FROM account_appeals");
$totalAppeals = (int) $totalAppealsStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalAppeals / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;

$appealsStmt = $pdo->prepare("
    SELECT a.*, u.email, u.user_type
    FROM account_appeals a
    JOIN users u ON u.user_id = a.user_id
    ORDER BY
        CASE WHEN a.status = 'pending' THEN 0 ELSE 1 END,
        a.created_at DESC
    LIMIT :limit OFFSET :offset
");
$appealsStmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$appealsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$appealsStmt->execute();
$appeals = $appealsStmt->fetchAll(PDO::FETCH_ASSOC);

$pageWindow = 2;
$startPage = max(1, $currentPage - $pageWindow);
$endPage = min($totalPages, $currentPage + $pageWindow);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appeals - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/super_manage.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <style>
        .appeals-table {
            min-width: 1280px;
        }

        .appeals-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }

        .appeals-stat-card {
            min-width: 170px;
            padding: 14px 16px;
            border: 2px solid #000000;
            background: #ffffff;
        }

        .appeals-stat-card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .appeals-stat-card span {
            display: inline-block;
            padding: 4px 10px;
            border: 2px solid #000000;
            background: #000000;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            min-width: 52px;
            text-align: center;
        }

        .appeals-table tr.pending-row {
            box-shadow: inset 6px 0 0 #000000;
        }

        .appeal-status-badge {
            display: inline-block;
            padding: 4px 10px;
            border: 2px solid #000000;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background: #ffffff;
            color: #000000;
        }

        .appeal-status-badge.status-pending {
            background: #000000;
            color: #ffffff;
        }

        .appeal-text-box {
            max-width: 280px;
            max-height: 120px;
            overflow: auto;
            padding: 10px;
            border: 2px solid #000000;
            background: #ffffff;
            white-space: pre-wrap;
            line-height: 1.45;
        }

        .appeal-notes-box {
            max-width: 300px;
        }

        .appeal-empty {
            font-style: italic;
            color: #333333;
        }

        .appeal-decision-form {
            min-width: 260px;
        }

        .appeal-decision-form label {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .appeal-decision-form textarea {
            width: 100%;
            min-height: 90px;
            border: 2px solid #000000;
            padding: 10px;
            resize: vertical;
            font: inherit;
            background: #ffffff;
        }

        .appeal-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .appeal-actions button {
            width: auto;
            margin-top: 0;
            padding: 10px 14px;
            border: 2px solid #000000;
            background: #000000;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
        }

        .appeal-actions button:last-child {
            background: #ffffff;
            color: #000000;
        }

        .appeal-meta {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        @media screen and (max-width: 768px) {
            .appeal-text-box,
            .appeal-notes-box,
            .appeal-decision-form {
                min-width: 220px;
            }
        }
    </style>
</head>
<body>
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - <?= htmlspecialchars($panelLabel) ?></h2>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="create_users.php">Create Users</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_internships.php">Manage Internships</a></li>
                <li><a href="applications.php">All Applications</a></li>
                <li><a href="appeals.php" class="active">Appeals</a></li>
                <li><a href="system_logs.php">System Logs</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>Account Appeals</h1>

            <?php if (isset($_SESSION['feedback_message'])): ?>
                <div style="margin-bottom: 20px; padding: 12px 16px; border: 1px solid #000; background: <?= ($_SESSION['feedback_type'] ?? 'success') === 'error' ? '#000' : '#fff' ?>; color: <?= ($_SESSION['feedback_type'] ?? 'success') === 'error' ? '#fff' : '#000' ?>;">
                    <?= htmlspecialchars($_SESSION['feedback_message']) ?>
                </div>
                <?php unset($_SESSION['feedback_message'], $_SESSION['feedback_type']); ?>
            <?php endif; ?>

            <div class="appeals-stats">
                <div class="appeals-stat-card">
                    <strong>Total Appeals</strong>
                    <span><?= $totalAppealsCount ?></span>
                </div>
                <div class="appeals-stat-card">
                    <strong>Pending</strong>
                    <span><?= $pendingAppealsCount ?></span>
                </div>
                <div class="appeals-stat-card">
                    <strong>Approved</strong>
                    <span><?= $approvedAppealsCount ?></span>
                </div>
                <div class="appeals-stat-card">
                    <strong>Rejected</strong>
                    <span><?= $rejectedAppealsCount ?></span>
                </div>
            </div>

            <?php if (!$appeals): ?>
                <div class="table-section" style="padding: 20px;">
                    No appeals have been submitted yet.
                </div>
            <?php else: ?>
                <div class="table-section">
                    <table class="appeals-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Submitted Status</th>
                                <th>Appeal Status</th>
                                <th>Submitted</th>
                                <th>Original Reason</th>
                                <th>Appeal Message</th>
                                <th>Admin Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appeals as $appeal): ?>
                                <tr class="<?= $appeal['status'] === 'pending' ? 'pending-row' : '' ?>">
                                    <td class="appeal-meta">#<?= (int) $appeal['appeal_id'] ?></td>
                                    <td><?= htmlspecialchars($appeal['email']) ?></td>
                                    <td>
                                        <span class="user-type user-type-<?= htmlspecialchars((string) $appeal['user_type']) ?>">
                                            <?= htmlspecialchars(ucfirst((string) $appeal['user_type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="appeal-status-badge">
                                            <?= htmlspecialchars(ucfirst((string) $appeal['submitted_status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="appeal-status-badge status-<?= htmlspecialchars((string) $appeal['status']) ?>">
                                            <?= htmlspecialchars(ucfirst((string) $appeal['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('F d, Y h:i A', strtotime((string) $appeal['created_at'])) ?></td>
                                    <td>
                                        <div class="appeal-text-box">
                                            <?= nl2br(htmlspecialchars((string) ($appeal['reason_snapshot'] ?: 'No reason recorded.'))) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appeal-text-box">
                                            <?= nl2br(htmlspecialchars((string) $appeal['appeal_message'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($appeal['status'] === 'pending'): ?>
                                            <form method="POST" class="appeal-decision-form">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="appeal_id" value="<?= (int) $appeal['appeal_id'] ?>">
                                                <label for="admin_notes_<?= (int) $appeal['appeal_id'] ?>">Admin Notes</label>
                                                <textarea id="admin_notes_<?= (int) $appeal['appeal_id'] ?>" name="admin_notes" placeholder="Add notes for this appeal decision"></textarea>
                                                <div class="appeal-actions">
                                                    <button type="submit" name="decision" value="approved">Approve</button>
                                                    <button type="submit" name="decision" value="rejected">Reject</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <div class="appeal-text-box appeal-notes-box">
                                                <?php if (!empty($appeal['admin_notes'])): ?>
                                                    <?= nl2br(htmlspecialchars((string) $appeal['admin_notes'])) ?>
                                                <?php else: ?>
                                                    <span class="appeal-empty">No admin notes recorded.</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Appeals pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?>" class="pagination-btn">Previous</a>
                        <?php endif; ?>

                        <div class="pagination-numbers">
                            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                                <a
                                    href="?page=<?= $page ?>"
                                    class="pagination-number <?= $page === $currentPage ? 'active' : '' ?>"
                                    <?= $page === $currentPage ? 'aria-current="page"' : '' ?>
                                ><?= $page ?></a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?>" class="pagination-btn">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
