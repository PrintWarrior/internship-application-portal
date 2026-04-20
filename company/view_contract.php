<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$contract_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Get contract details and verify it belongs to this company
$stmt = $pdo->prepare("
    SELECT ct.*, a.intern_id,
           ir.first_name, ir.last_name, ir.middle_name, ir.contact_no,
           intern_addr.address_line AS intern_address,
           intern_addr.city, intern_addr.province, intern_addr.postal_code,
           ir.university, ir.course, ir.year_level,
           u.email,
           i.title as internship_title, i.description, i.duration, i.allowance,
           i.start_date, i.end_date, i.internship_id,
           c.company_name, c.industry, c.contact_person, company_addr.address_line AS company_address,
           a.application_id, a.status as application_status,
           CONCAT(ir.first_name, ' ', ir.last_name) as intern_name
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    LEFT JOIN addresses company_addr
        ON company_addr.entity_id = c.company_id
        AND company_addr.entity_type = 'company'
        AND company_addr.is_primary = 1
    LEFT JOIN addresses intern_addr
        ON intern_addr.entity_id = ir.intern_id
        AND intern_addr.entity_type = 'intern'
        AND intern_addr.is_primary = 1
    WHERE ct.contract_id = ? AND i.company_id = ?
");
$stmt->execute([$contract_id, $company['company_id']]);
$contract = $stmt->fetch();

if (!$contract) {
    header('Location: contracts.php');
    exit;
}

// Handle contract confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_contract'])) {
    requireValidCsrfToken(['redirect' => 'view_contract.php?id=' . $contract_id]);
    $updateStmt = $pdo->prepare("UPDATE contracts SET hr_confirmed = 1, signed_date = NOW() WHERE contract_id = ?");
    $updateStmt->execute([$contract_id]);

    // Create notification for intern
    $notification_message = "Your contract for '" . $contract['internship_title'] . "' has been confirmed by the company.";
    $stmt2 = $pdo->prepare("SELECT user_id FROM interns WHERE intern_id = ?");
    $stmt2->execute([$contract['intern_id']]);
    $intern_user_id = $stmt2->fetchColumn();

    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notifStmt->execute([$intern_user_id, $notification_message]);

    // Log the action
    logAction('Confirm contract', 'Confirmed contract #' . $contract_id . ' for ' . $contract['intern_name']);

    header("Location: view_contract.php?id=" . $contract_id . "&confirmed=1");
    exit;
}

// Format date function
function formatDate($date, $format = 'F d, Y')
{
    return $date ? date($format, strtotime($date)) : 'Not set';
}

// Calculate contract duration
function getContractDuration($start_date, $end_date)
{
    if (!$start_date || !$end_date)
        return 'Not specified';

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);

    $months = $interval->m + ($interval->y * 12);
    return $months . ' month' . ($months > 1 ? 's' : '');
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contract - <?= htmlspecialchars($contract['intern_name']) ?></title>
    <link rel="stylesheet" href="../assets/css/companycontract_view.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

    <!-- TOP NAV -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h4>Internship Portal - Company</h4>
        </div>

        <div class="topnav-right">
            <a href="notifications.php">
                Notifications <span class="badge"><?= $unread ?></span>
            </a>
            <a href="#" onclick="openLogoutModal()">Logout</a>
        </div>
    </div>

    <div class="wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <a href="index.php">Dashboard</a>
            <a href="profile.php">Company Profile</a>
            <a href="post_internship.php">Post Internship</a>
            <a href="manage_internships.php">My Internships</a>
            <a href="view_applicants.php">View Applicants</a>
            <a href="contracts.php">Contracts</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <?php if (isset($_GET['confirmed'])): ?>
                <div class="success-message">
                    ✓ Contract has been confirmed successfully!
                </div>
            <?php endif; ?>

            <div class="contract-container">
                <div class="contract-header">
                    <h1>EMPLOYMENT CONTRACT</h1>
                    <p>Internship Agreement</p>
                    <div
                        class="contract-status <?= $contract['hr_confirmed'] ? 'status-confirmed' : 'status-pending' ?>">
                        <?= $contract['hr_confirmed'] ? '“ CONFIRMED' : 'PENDING CONFIRMATION' ?>
                    </div>
                </div>

                <div class="contract-body">
                    <div class="contract-meta">
                        <span class="contract-id">Contract #:
                            CT-<?= str_pad($contract['contract_id'], 6, '0', STR_PAD_LEFT) ?></span>
                        <span class="contract-id">Date:
                            <?= formatDate($contract['signed_date'] ?: date('Y-m-d')) ?></span>
                    </div>

                    <!-- Party Information -->
                    <div class="contract-section">
                        <h2>PARTIES</h2>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">THE COMPANY</span>
                                <span class="info-value"><?= htmlspecialchars($contract['company_name']) ?></span>
                                <div style="margin-top: 5px; font-size: 14px; color: #6c757d;">
                                    Represented by: <?= htmlspecialchars($contract['contact_person']) ?><br>
                                    Address: <?= htmlspecialchars($contract['company_address']) ?>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">THE INTERN</span>
                                <span class="info-value"><?= htmlspecialchars($contract['intern_name']) ?></span>
                                <div style="margin-top: 5px; font-size: 14px; color: #6c757d;">
                                    Email: <?= htmlspecialchars($contract['email']) ?><br>
                                    Contact: <?= htmlspecialchars($contract['contact_no']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Internship Details -->
                    <div class="contract-section">
                        <h2>INTERNSHIP DETAILS</h2>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Position</span>
                                <span class="info-value"><?= htmlspecialchars($contract['internship_title']) ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Duration</span>
                                <span class="info-value">
                                    <?= htmlspecialchars($contract['duration'] ?: getContractDuration($contract['start_date'], $contract['end_date'])) ?>
                                </span>
                            </div>

                            <!--<div class="info-item">
                            <span class="info-label">Start Date</span>
                            <span class="info-value"><?= formatDate($contract['start_date']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">End Date</span>
                            <span class="info-value"><?= formatDate($contract['end_date']) ?></span>
                        </div> -->

                            <div class="info-item">
                                <span class="info-label">Allowance</span>
                                <span class="info-value">PHP <?= number_format($contract['allowance'], 2) ?>/month</span>
                            </div>
                        </div>
                    </div>

                    <!-- Intern Information -->
                    <div class="contract-section">
                        <h2>INTERN INFORMATION</h2>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">University</span>
                                <span class="info-value"><?= htmlspecialchars($contract['university']) ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Course</span>
                                <span class="info-value"><?= htmlspecialchars($contract['course']) ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Year Level</span>
                                <span class="info-value"><?= htmlspecialchars($contract['year_level']) ?></span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Address</span>
                                <span class="info-value">
                                    <?= htmlspecialchars($contract['intern_address'] ?: '') ?><br>
                                    <?= htmlspecialchars($contract['city'] ?: '') ?>,
                                    <?= htmlspecialchars($contract['province'] ?: '') ?>
                                    <?= htmlspecialchars($contract['postal_code'] ?: '') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="contract-section">
                        <h2>TERMS AND CONDITIONS</h2>

                        <div class="terms-box">
                            <h3>1. Scope of Work</h3>
                            <p>The Intern agrees to perform the duties and responsibilities as described in the
                                internship position. The Intern shall comply with all company policies, rules, and
                                regulations.</p>

                            <h3>2. Duration</h3>
                            <p>This internship shall commence on <?= formatDate($contract['start_date']) ?> and shall
                                continue until <?= formatDate($contract['end_date']) ?>, unless terminated earlier by
                                either party.</p>

                            <h3>3. Compensation</h3>
                            <p>The Intern shall receive a monthly allowance of
                                PHP <?= number_format($contract['allowance'], 2) ?>, payable on the
                                <?= date('jS', strtotime($contract['start_date'])) ?> day of each month.
                            </p>

                            <h3>4. Confidentiality</h3>
                            <p>The Intern agrees to maintain the confidentiality of all company information and trade
                                secrets encountered during the internship.</p>

                            <h3>5. Termination</h3>
                            <p>Either party may terminate this agreement with a written notice of at least 7 days. The
                                company reserves the right to terminate immediately for cause.</p>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="contract-section">
                        <h2>SIGNATURES</h2>

                        <div class="signature-area">
                            <!-- Company Signature -->
                            <div class="signature-block">
                                <?php if ($contract['hr_confirmed']): ?>
                                    <div class="signature-image">
                                        <!-- If you have company signature image -->
                                        <img src="../assets/img/sign.png" alt="Company Signature"
                                            style="max-width: 200px;">
                                    </div>
                                <?php else: ?>
                                    <div class="signature-line"></div>
                                <?php endif; ?>
                                <div class="info-value"><?= htmlspecialchars($contract['contact_person']) ?></div>
                                <div class="info-label">Company Representative</div>
                                <div class="signature-date">
                                    Date:
                                    <?= $contract['hr_confirmed'] ? formatDate($contract['signed_date']) : '______________' ?>
                                </div>
                            </div>

                            <!-- Intern Signature -->
                            <div class="signature-block">
                                <?php if ($contract['signed_file']): ?>
                                    <!-- Extract signature from PDF or show placeholder -->
                                    <div class="signature-status">
                                        <span class="badge-success">Intern has signed</span>
                                    </div>
                                    <div class="signature-preview">
                                        <a href="../uploads/contracts/<?= $contract['signed_file'] ?>" target="_blank"
                                            class="btn-view">
                                            View Signed Contract
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="signature-line"></div>
                                    <div class="signature-status">
                                       <!-- <span class="badge-warning">Awaiting intern signature</span> -->
                                    </div>
                                <?php endif; ?>
                                <div class="info-value"><?= htmlspecialchars($contract['intern_name']) ?></div>
                                <div class="info-label">Intern</div>
                                <div class="signature-date">
                                    Date:
                                    <?= $contract['signed_date'] ? formatDate($contract['signed_date']) : '______________' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add PDF preview section -->
                    <!--<div class="contract-preview">
                        <h3>Contract Document</h3>
                        <?php if ($contract['signed_file']): ?>
                             
                            <iframe src="../uploads/contracts/<?= $contract['signed_file'] ?>" width="100%"
                                height="600px"></iframe>
                            <a href="../uploads/contracts/<?= $contract['signed_file'] ?>" download class="btn-download">
                                Download Signed Contract
                            </a>
                        <?php elseif ($contract['contract_pdf']): ?>
                            
                            <iframe src="../temp_contracts/<?= $contract['contract_pdf'] ?>" width="100%"
                                height="600px"></iframe>
                            <div class="alert alert-info">
                                This contract is awaiting intern signature.
                            </div>
                        <?php endif; ?>
                    </div>-->

                    <!-- Action Buttons -->
                    <div class="action-buttons">

                        <!--<a href="download_contract.php?id=<?= $contract_id ?>" class="btn-download">
                            <span>ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¥</span> Download PDF
                        </a>`-->

                        <a href="contracts.php" class="btn-back">
                            <span></span> Back to List
                        </a>

                        <?php if ($contract['signed_file']): ?>
                            <a href="download_contract.php?id=<?= $contract_id ?>&type=signed" class="btn-download">
                                <span></span> Download Signed Contract
                            </a>
                        <?php else: ?>
                            <a href="download_contract.php?id=<?= $contract_id ?>&type=unsigned" class="btn-download">
                                <span></span> Download Contract Template
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Logout Modal HTML -->
    <?php include '../html/logout_modal.html'; ?>

    <!-- Include Logout Modal JavaScript -->
    <script src="../js/logout_modal.js"></script>

    <script>
        // Add print optimization
        window.onbeforeprint = function () {
            document.querySelector('.topnav').style.display = 'none';
            document.querySelector('.sidebar').style.display = 'none';
            document.querySelector('.action-buttons').style.display = 'none';
            document.querySelector('.btn-back').style.display = 'none';
            document.querySelector('.success-message')?.style.display = 'none';
        };

        window.onafterprint = function () {
            document.querySelector('.topnav').style.display = 'flex';
            document.querySelector('.sidebar').style.display = 'block';
            document.querySelector('.action-buttons').style.display = 'flex';
            document.querySelector('.btn-back').style.display = 'inline-flex';
            document.querySelector('.success-message')?.style.display = 'block';
        };
    </script>

    <script src="../js/responsive-nav.js"></script>
</body>

</html>
