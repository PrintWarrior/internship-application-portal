<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Mark all as read when viewing (simplified)
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$_SESSION['user_id']]);

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Determine notification type based on message content
function getNotificationType($message)
{
    $message = strtolower($message);
    if (strpos($message, 'application') !== false || strpos($message, 'applied') !== false) {
        return 'application';
    } elseif (strpos($message, 'message') !== false || strpos($message, 'sent') !== false) {
        return 'message';
    } elseif (strpos($message, 'otp') !== false) {
        return 'otp';
    } elseif (strpos($message, 'verification') !== false || strpos($message, 'verify') !== false) {
        return 'verification';
    } else {
        return 'system';
    }
}

// Check for feedback messages from session or URL
$feedback_message = '';
$feedback_type = '';

if (isset($_SESSION['feedback_message'])) {
    $feedback_message = $_SESSION['feedback_message'];
    $feedback_type = $_SESSION['feedback_type'] ?? 'success';
    unset($_SESSION['feedback_message']);
    unset($_SESSION['feedback_type']);
} elseif (isset($_GET['feedback'])) {
    switch ($_GET['feedback']) {
        case 'otp_sent':
            $feedback_message = 'Temporary Password successfully sent!';
            $feedback_type = 'success';
            break;
        case 'verification_sent':
            $feedback_message = 'Verification email successfully sent!';
            $feedback_type = 'success';
            break;
        case 'otp_failed':
            $feedback_message = 'Failed to send Temporary Password. Please try again.';
            $feedback_type = 'error';
            break;
        case 'verification_failed':
            $feedback_message = 'Failed to send verification email. Please try again.';
            $feedback_type = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="../assets/css/admin_notification.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <style>
        /* Feedback Alert Styles */
        .feedback-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            animation: slideIn 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feedback-alert.fade-out {
            animation: fadeOut 0.3s ease-out forwards;
        }

        .feedback-alert.success {
            background: black;
            color: white;
            border-left: 4px solid white;
        }

        .feedback-alert.error {
            background: white;
            color: black;
            border-left: 4px solid black;
        }

        .feedback-alert.info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-left: 4px solid #117a8b;
        }

        .feedback-icon {
            font-size: 24px;
            font-weight: bold;
        }

        .feedback-content {
            flex: 1;
        }

        .feedback-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .feedback-message {
            font-size: 14px;
            line-height: 1.5;
        }

        .feedback-close {
            cursor: pointer;
            font-size: 20px;
            opacity: 0.7;
            transition: opacity 0.2s;
            padding: 0 5px;
        }

        .feedback-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Progress bar for auto-dismiss */
        .feedback-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }

        .feedback-progress-bar {
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            width: 100%;
            animation: progress 5s linear forwards;
        }

        @keyframes progress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }
    </style>
</head>

<body>

    <!-- Feedback Alert -->
    <?php if ($feedback_message): ?>
        <div class="feedback-alert <?= $feedback_type ?>" id="feedbackAlert">
            <div class="feedback-icon">
                <?php if ($feedback_type === 'success'): ?>
                    ✓
                <?php elseif ($feedback_type === 'error'): ?>
                    ✕
                <?php else: ?>
                    ℹ
                <?php endif; ?>
            </div>
            <div class="feedback-content">
                <div class="feedback-title">
                    <?= $feedback_type === 'success' ? 'Success' : ($feedback_type === 'error' ? 'Error' : 'Information') ?>
                </div>
                <div class="feedback-message"><?= htmlspecialchars($feedback_message) ?></div>
            </div>
            <div class="feedback-close" onclick="this.parentElement.remove()">×</div>
            <div class="feedback-progress">
                <div class="feedback-progress-bar"></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Overlay -->
    <div class="modal-overlay">
        <!-- Modal Container -->
        <div class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>Notifications</h2>
                <a href="index.php" class="close-button">&times;</a>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <p>No notifications</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $index => $notif): ?>
                            <?php $type = getNotificationType($notif['message']); ?>
                            <div class="notification-item <?= $index < 3 ? 'unread' : '' ?>">
                                <div class="notification-message">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </div>
                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <?= date('M d, Y - h:i A', strtotime($notif['created_at'])) ?>
                                    </span>
                                    <span class="notification-type <?= $type ?>">
                                        <?= ucfirst($type) ?>
                                    </span>
                                </div>
                                <?php if (!empty($notif['action_url'])): ?>
                                    <div class="notification-actions">
                                        <a href="<?= htmlspecialchars($notif['action_url']) ?>" class="btn btn-primary btn-small">
                                            <?= htmlspecialchars($notif['action_label'] ?? 'View') ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <a href="index.php">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss alert after 5 seconds
        setTimeout(function () {
            const alert = document.getElementById('feedbackAlert');
            if (alert) {
                alert.classList.add('fade-out');
                setTimeout(function () {
                    if (alert && alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    </script>

</body>

</html>