<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get notification count for topnav if needed
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

// Check if deadline expired
$expired = strtotime($job['deadline']) < time();

// Function to get status class
function getStatusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Internship - Admin</title>
    <link rel="stylesheet" href="../assets/css/admininternship_view.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <!-- Header with title and status -->
        <div class="header-section">
            <h2><?= htmlspecialchars($job['title']) ?></h2>
            <span class="status-badge <?= getStatusClass($job['status']) ?>">
                <?= ucfirst($job['status']) ?>
            </span>
        </div>

        <!-- Company Information Card -->
        <div class="company-card">
            <div class="company-name"><?= htmlspecialchars($job['company_name']) ?></div>
            <div class="company-detail">
                <span class="company-label">Industry:</span>
                <span class="company-value"><?= htmlspecialchars($job['industry']) ?></span>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="details-grid">
            <!-- Basic Details -->
            <div class="detail-card">
                <h3>Basic Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value"><?= htmlspecialchars($job['duration'] ?? 'Not specified') ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Allowance:</span>
                    <span class="detail-value">₱<?= number_format($job['allowance'] ?? 0, 2) ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Positions:</span>
                    <span class="detail-value"><?= htmlspecialchars($job['positions'] ?? 'Not specified') ?></span>
                </div>
            </div>

            <!-- Dates -->
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

        <!-- Description Section -->
        <div class="description-section">
            <h3>Description</h3>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($job['description'] ?? 'No description provided.')) ?>
            </div>
        </div>

        <!-- Requirements Section (if exists) -->
        <?php if (!empty($job['requirements'])): ?>
        <div class="description-section">
            <h3>Requirements</h3>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($job['requirements'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Meta Information -->
        <div class="meta-section">
            <!--<div class="meta-item">
                <div class="meta-label">Internship ID</div>
                <div class="meta-value">#<?= $job['internship_id'] ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Company ID</div>
                <div class="meta-value">#<?= $job['company_id'] ?></div>
            </div> -->
            
            <?php
            // Count applications for this internship
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE internship_id = ?");
            $countStmt->execute([$id]);
            $appCount = $countStmt->fetchColumn();
            ?>
            <div class="meta-item">
                <div class="meta-label">Applications</div>
                <div class="meta-value"><?= $appCount ?></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="manage_internships.php" class="btn btn-back">Back to List</a>
           <!-- <a href="edit_internship.php?id=<?= $job['internship_id'] ?>" class="btn btn-edit">Edit Internship</a>
            <a href="#" class="btn btn-delete" onclick="openDeleteModal(<?= $job['internship_id'] ?>, '<?= htmlspecialchars($job['title']) ?>'); return false;">Delete</a> -->
        </div>
    </div>

    <!-- Include delete modal if you have one -->
    <script>
    function openDeleteModal(id, title) {
        if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
            window.location.href = 'delete_internship.php?id=' + id;
        }
    }
    </script>
</body>
</html>