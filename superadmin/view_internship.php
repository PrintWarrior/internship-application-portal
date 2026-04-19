<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdminAreaAccess();
$panelLabel = getAdminAreaLabel();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread = $stmt->fetchColumn();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.industry
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    WHERE i.internship_id = ?
");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    die("Internship not found.");
}

$expired = strtotime($job['deadline']) < time();

function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Internship - <?= htmlspecialchars($panelLabel) ?></title>
    <link rel="stylesheet" href="../assets/css/admininternship_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <div class="header-section">
            <h2><?= htmlspecialchars($job['title']) ?></h2>
            <span class="status-badge <?= getStatusClass($job['status']) ?>">
                <?= ucfirst($job['status']) ?>
            </span>
        </div>

        <div class="company-card">
            <div class="company-name"><?= htmlspecialchars($job['company_name']) ?></div>
            <div class="company-detail">
                <span class="company-label">Industry:</span>
                <span class="company-value"><?= htmlspecialchars($job['industry']) ?></span>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-card">
                <h3>Basic Details</h3>

                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value"><?= htmlspecialchars($job['duration'] ?? 'Not specified') ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Allowance:</span>
                    <span class="detail-value">PHP <?= number_format($job['allowance'] ?? 0, 2) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Positions:</span>
                    <span class="detail-value"><?= htmlspecialchars($job['positions'] ?? 'Not specified') ?></span>
                </div>
            </div>

            <div class="detail-card">
                <h3>Important Dates</h3>

                <div class="detail-row">
                    <span class="detail-label">Posted:</span>
                    <span class="detail-value"><?= date('M d, Y', strtotime($job['created_at'] ?? $job['date_posted'])) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Deadline:</span>
                    <span class="detail-value <?= $expired ? 'deadline-expired' : '' ?>">
                        <?= date('M d, Y', strtotime($job['deadline'])) ?>
                    </span>
                </div>

                <?php if (!empty($job['start_date'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value"><?= date('M d, Y', strtotime($job['start_date'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="description-section">
            <h3>Description</h3>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($job['description'] ?? 'No description provided.')) ?>
            </div>
        </div>

        <?php if (!empty($job['requirements'])): ?>
        <div class="description-section">
            <h3>Requirements</h3>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($job['requirements'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="meta-section">
            <?php
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE internship_id = ?");
            $countStmt->execute([$id]);
            $appCount = $countStmt->fetchColumn();
            ?>
            <div class="meta-item">
                <div class="meta-label">Applications</div>
                <div class="meta-value"><?= $appCount ?></div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="manage_internships.php" class="btn btn-back">Back to List</a>
        </div>
    </div>
    <script src="../js/responsive-nav.js"></script>
</body>
</html>
