<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all contracts with additional details
$stmt = $pdo->prepare("
    SELECT 
        ct.*, 
        i.title,
        c.company_name,
        a.status as application_status
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    WHERE ir.user_id = ?
    ORDER BY ct.signed_date DESC
");
$stmt->execute([$user_id]);
$contracts = $stmt->fetchAll();

// Function to format date
function formatDate($date)
{
    return $date ? date('M d, Y', strtotime($date)) : 'Not signed';
}

/* Count unread */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM notifications 
    WHERE user_id=? AND is_read=0
");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Contracts</title>
    <link rel="stylesheet" href="../assets/css/intern_contract.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <style>
        /* Notification badge in sidebar */
        .sidebar .badge {
            background-color: #ffffff;
            color: #000000;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 8px;
            border: 1px solid #ffffff;
        }
    </style>
    <style>
        .confirmed-yes {
            color: green;
            font-weight: bold;
        }

        .confirmed-pending {
            color: orange;
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4>Intern Panel</h4>
        <a href="index.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="browse_internships.php">Browse Internships</a>
        <a href="my_applications.php">My Applications</a>
        <a href="offers.php">Offers</a>
        <a href="contracts.php">Contracts</a>
        <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
        <a href="#" onclick="openLogoutModal()">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Employment Contracts</h2>

        <div class="table-container">
            <?php if (count($contracts) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Internship Position</th>
                            <th>Company</th>
                            <th>Signed Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($contract['title']) ?></strong></td>
                                <td><?= htmlspecialchars($contract['company_name'] ?? 'N/A') ?></td>
                                <td><?= formatDate($contract['signed_date']) ?></td>
                                <td>
                                    <?php
                                    if (!$contract['signed_file']) {
                                        echo "<em>Pending Signature</em>";
                                    } elseif ($contract['hr_confirmed']) {
                                        echo '<span class="confirmed-yes">Confirmed</span>';
                                    } else {
                                        echo '<span class="confirmed-pending">Signed - Pending HR</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Determine contract state
                                    $has_pdf = !empty($contract['contract_pdf']);
                                    $is_signed = !empty($contract['signed_file']);
                                    $is_confirmed = $contract['hr_confirmed'] == 1;
                                    ?>

                                    <div class="action-group">
                                        <?php if (!$is_signed): ?>
                                            <!-- UNSIGNED CONTRACT - Show options to view and upload -->

                                            <?php if ($has_pdf): ?>
                                                <a href="../temp_contracts/<?= $contract['contract_pdf'] ?>" target="_blank"
                                                    class="btn-view">
                                                    View Contract
                                                </a>
                                                <a href="download_contract.php?id=<?= $contract['contract_id'] ?>&type=unsigned"
                                                    target="_blank" rel="noopener"
                                                    class="btn-download">
                                                    Download / Print
                                                </a>
                                            <?php else: ?>
                                                <span class="badge-warning">Contract not ready</span>
                                            <?php endif; ?>
                                            

                                            <!-- Upload form for signed contract -->
                                            <form action="sign_contract.php" method="POST" enctype="multipart/form-data"
                                                class="sign-form" style="display: inline;">
                                                <input type="hidden" name="contract_id" value="<?= $contract['contract_id'] ?>">

                                                <!-- File input - hidden but accessible -->
                                                <input type="file" name="signed_file" accept=".pdf,application/pdf" required
                                                    id="file-<?= $contract['contract_id'] ?>"
                                                    style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;">

                                                <!-- Label that triggers file input -->
                                                <button type="button" class="btn-upload"
                                                    onclick="document.getElementById('file-<?= $contract['contract_id'] ?>').click(); return false;">
                                                    Upload Signed
                                                </button>
                                            </form>

                                        <?php else: ?>
                                            <!-- SIGNED CONTRACT - Show view/download options -->

                                            <a href="../uploads/contracts/<?= $contract['signed_file'] ?>" target="_blank"
                                                class="btn-view">
                                                View Signed
                                            </a>

                                            <a href="download_contract.php?id=<?= $contract['contract_id'] ?>&type=signed"
                                                class="btn-download">
                                                Download Signed
                                            </a>

                                            <!-- Status badge -->
                                            <?php if ($is_confirmed): ?>
                                                <span class="confirmed-badge">Confirmed</span>
                                            <?php else: ?>
                                                <span class="pending-badge">Pending HR</span>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Contract Summary -->
                <div style="margin-top: 25px; display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; border: 2px solid #000000; padding: 20px;">
                        <h3
                            style="font-size: 16px; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #000000; padding-bottom: 10px;">
                            Contract Summary
                        </h3>
                        <p style="margin-bottom: 10px;">
                            <strong>Total Contracts:</strong> <?= count($contracts) ?><br>
                            <strong>HR Confirmed:</strong>
                            <?= count(array_filter($contracts, fn($c) => $c['hr_confirmed'])) ?><br>
                            <strong>Pending Signature / Staff:</strong>
                            <?= count(array_filter($contracts, fn($c) => !$c['hr_confirmed'] || !$c['signed_file'])) ?>
                        </p>
                    </div>

                    <div style="flex: 1; border: 2px solid #000000; padding: 20px;">
                        <h3
                            style="font-size: 16px; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #000000; padding-bottom: 10px;">
                            Need Help?
                        </h3>
                        <p style="font-size: 13px; margin-bottom: 10px;">
                            For questions about your contracts, please contact the HR department.
                        </p>
                        <a href="#" style="color: #000000; text-decoration: underline; font-weight: bold;">Contact
                            Support</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="no-results">
                    No contracts available at the moment.<br>
                    <a href="offers.php">View Your Offers</a> or
                    <a href="browse_internships.php">Browse Internships</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Trigger file input when upload button is clicked
        document.querySelectorAll('.btn-upload').forEach(button => {
            button.addEventListener('click', function () {
                const fileInput = this.previousElementSibling;
                fileInput.click();
            });
        });

        // Auto-submit form when file is selected
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function () {
                if (this.files.length > 0) {
                    // Validate file size (max 5MB)
                    if (this.files[0].size > 5 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 5MB.');
                        this.value = '';
                        return;
                    }

                    // Confirm upload
                    if (confirm('Are you sure you want to upload this signed contract?')) {
                        this.form.submit();
                    } else {
                        this.value = '';
                    }
                }
            });
        });
    </script>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>
    
</body>

</html>
