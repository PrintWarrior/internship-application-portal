<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/fpdf186/fpdf.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$application_id = isset($_GET['application_id']) ? (int)$_GET['application_id'] : 0;

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Fetch application details
$stmt = $pdo->prepare("
    SELECT a.application_id, a.intern_id,
           i.title AS internship_title, i.description, i.duration, i.allowance,
           i.start_date, i.end_date, i.internship_id,
           c.company_name, c.contact_person, company_addr.address_line AS company_address,
           ir.first_name, ir.last_name, ir.middle_name, ir.contact_no,
           intern_addr.address_line AS intern_address, intern_addr.city, intern_addr.province, intern_addr.postal_code,
           ir.university, ir.course, ir.year_level,
           u.email
    FROM applications a
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
    WHERE a.application_id = ? AND i.company_id = ?
");
$stmt->execute([$application_id, $company['company_id']]);
$application = $stmt->fetch();

if (!$application) {
    die("Invalid application or permission denied.");
}

// Handle contract creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => 'create_contract.php?application_id=' . $application_id]);
    $contract_text = $_POST['contract_text'] ?? '';

    if (empty(trim($contract_text))) {
        $error = "Contract text cannot be empty.";
    } else {
        // Create temp_contracts directory if it doesn't exist
        $temp_dir = '../temp_contracts/';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }

        // Generate PDF filename
        $pdf_filename = 'contract_' . $application_id . '_' . time() . '.pdf';
        $pdf_path = $temp_dir . $pdf_filename;

        // Create PDF with proper formatting
        class ContractPDF extends FPDF
        {
            function Header()
            {
                $this->SetFont('Arial', 'B', 16);
                $this->Cell(0, 10, 'INTERNSHIP EMPLOYMENT CONTRACT', 0, 1, 'C');
                $this->SetFont('Arial', 'I', 11);
                $this->Cell(0, 10, 'Internship Agreement', 0, 1, 'C');
                $this->Ln(10);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }
        }

        // Create PDF instance
        $pdf = new ContractPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);

        // Add contract metadata
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Contract #: CT-' . str_pad($application_id, 6, '0', STR_PAD_LEFT), 0, 1, 'R');
        $pdf->Cell(0, 6, 'Date Created: ' . date('F d, Y'), 0, 1, 'R');
        $pdf->Ln(10);

        // Add the contract text
        $pdf->SetFont('Arial', '', 11);
        $lines = explode("\n", $contract_text);
        foreach ($lines as $line) {
            if (trim($line) == '') {
                $pdf->Ln(5);
            } else {
                $pdf->MultiCell(0, 6, trim($line));
            }
        }

        // Add signature section
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'SIGNATURES', 0, 1, 'L');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(95, 6, 'COMPANY REPRESENTATIVE:', 0, 0, 'L');
        $pdf->Cell(95, 6, 'INTERN:', 0, 1, 'L');
        $pdf->Ln(5);
        
        $pdf->Cell(95, 6, '_________________________', 0, 0, 'L');
        $pdf->Cell(95, 6, '_________________________', 0, 1, 'L');
        $pdf->Cell(95, 6, $application['contact_person'], 0, 0, 'L');
        $pdf->Cell(95, 6, $application['first_name'] . ' ' . $application['last_name'], 0, 1, 'L');
        $pdf->Cell(95, 6, 'Date: ______________', 0, 0, 'L');
        $pdf->Cell(95, 6, 'Date: ______________', 0, 1, 'L');

        // Save PDF
        $pdf->Output('F', $pdf_path);

        // Insert into contracts table
        $insertStmt = $pdo->prepare("
            INSERT INTO contracts (application_id, contract_pdf, contract_file, hr_confirmed, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $insertStmt->execute([$application_id, $pdf_filename, $pdf_filename]);
        
        $contract_id = $pdo->lastInsertId();

        // Create notification for intern
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, link, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $userStmt = $pdo->prepare("SELECT user_id FROM interns WHERE intern_id = ?");
        $userStmt->execute([$application['intern_id']]);
        $intern_user_id = $userStmt->fetchColumn();
        
        $message = "A new contract for '" . $application['internship_title'] . "' is ready for your signature.";
        $notifStmt->execute([$intern_user_id, $message, 'intern/contracts.php']);

        // Set session success message and redirect
        $_SESSION['success'] = "Contract created and sent to intern successfully!";
        header("Location: contracts.php");
        exit;
    }
}

function formatDate($date) {
    return $date ? date('F d, Y', strtotime($date)) : 'Not specified';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Contract</title>
    <link rel="stylesheet" href="../assets/css/company_contract.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="stylesheet" href="../assets/css/companycontract_modal.css">
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Create Contract</h2>
            <a href="view_applicants.php" class="btn-back" style="background-color: #000000; color: white; padding: 8px 15px; text-decoration: none;">Back to Applicants</a>
        </div>

        <div class="contract-container">
            <!-- Applicant Details -->
            <div class="applicant-details">
                <h3 style="margin-top: 0;">Applicant Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value"><?= htmlspecialchars($application['first_name'] . ' ' . $application['last_name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($application['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Internship Position</span>
                        <span class="detail-value"><?= htmlspecialchars($application['internship_title']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Company</span>
                        <span class="detail-value"><?= htmlspecialchars($application['company_name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Allowance</span>
                        <span class="detail-value">PHP <?= number_format($application['allowance'], 2) ?>/month</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">University</span>
                        <span class="detail-value"><?= htmlspecialchars($application['university'] ?: 'Not specified') ?></span>
                    </div>
                </div>
            </div>

            <?php if (isset($error)) : ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 20px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Contract Form -->
            <div class="form-container">
                <button type="button" class="btn-insert-template" onclick="insertTemplate()">
                    Insert Contract Template
                </button>
                
                    <form method="POST">
                        <?= csrf_input() ?>
                    <div class="form-group">
                        <label for="contract_text"><strong>Contract Text:</strong></label>
                        <textarea name="contract_text" id="contract_text" rows="20" style="width: 100%; padding: 10px; border: 2px solid #000000; font-family: monospace;"><?= isset($_POST['contract_text']) ? htmlspecialchars($_POST['contract_text']) : '' ?></textarea>
                    </div>

                    <div class="form-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="submit" style="background-color: #000000; color: white; border: 2px solid #000000; padding: 10px 20px; cursor: pointer; font-weight: bold;">Create Contract PDF</button>
                        <a href="view_applicants.php" style="background-color: #ffffff; color: #000000; padding: 10px 20px; text-decoration: none; border: 2px solid #000000; text-align: center;">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Help Text -->
            <div class="help-text" style="margin-top: 20px; background-color: #f9f9f9; padding: 15px; border: 1px solid #000000;">
                <strong style="font-size: 16px;">Contract Guidelines:</strong>
                <ul style="margin-top: 10px;">
                    <li>Include full names of both parties (Company and Intern)</li>
                    <li>Specify the internship position, start date, and end date</li>
                    <li>Detail the responsibilities and expectations</li>
                    <li>Include compensation details (allowance, benefits)</li>
                    <li>Add confidentiality and termination clauses</li>
                    <li>Leave space for signatures and dates</li>
                </ul>
                <p style="color: #000000; margin-top: 10px;"><em>The contract will be generated as a PDF and sent to the intern for signature.</em></p>
            </div>
        </div>
    </div>
</div>

<script>
function insertTemplate() {
    const template = `INTERNSHIP EMPLOYMENT CONTRACT

THIS INTERNSHIP AGREEMENT (the "Agreement") is entered into on this day, _________________, by and between:

THE COMPANY: ${<?= json_encode($application['company_name']) ?>}
Represented by: ${<?= json_encode($application['contact_person']) ?>}
Address: ${<?= json_encode($application['company_address']) ?>}

AND

THE INTERN: ${<?= json_encode($application['first_name'] . ' ' . $application['last_name']) ?>}
Email: ${<?= json_encode($application['email']) ?>}
Contact: ${<?= json_encode($application['contact_no']) ?>}
University: ${<?= json_encode($application['university']) ?>}
Course: ${<?= json_encode($application['course']) ?>}

1. INTERNSHIP POSITION
The Intern shall undertake an internship position as ${<?= json_encode($application['internship_title']) ?>} at the Company.

2. DURATION
This internship shall commence on ${<?= json_encode(formatDate($application['start_date'])) ?>} and shall continue until ${<?= json_encode(formatDate($application['end_date'])) ?>}, unless terminated earlier.

3. COMPENSATION
The Intern shall receive a monthly allowance of PHP ${<?= json_encode(number_format($application['allowance'], 2)) ?>}.

4. SCOPE OF WORK
The Intern agrees to:
- Perform all assigned duties diligently
- Follow company policies and procedures
- Maintain professional conduct
- Complete assigned tasks within deadlines

5. CONFIDENTIALITY
The Intern shall maintain strict confidentiality of all company information, trade secrets, and proprietary data.

6. TERMINATION
Either party may terminate this agreement with a written notice of 7 days. The Company reserves the right to terminate immediately for just cause.

7. GOVERNING LAW
This Agreement shall be governed by the laws of the Republic of the Philippines.

IN WITNESS WHEREOF, the parties have executed this Agreement on the date first written above.

COMPANY REPRESENTATIVE:                      INTERN:

_________________________                    _________________________
${<?= json_encode($application['contact_person']) ?>}                    ${<?= json_encode($application['first_name'] . ' ' . $application['last_name']) ?>}

Date: ___________________                    Date: ___________________`;
    
    document.getElementById('contract_text').value = template;
}
</script>

<!-- Include Logout Modal HTML -->
<?php include '../html/logout_modal.html'; ?>

<!-- Include Contract Modal HTML -->
<?php include '../html/companycontract_modal.html'; ?>

<!-- Include JavaScript files -->
<script src="../js/logout_modal.js"></script>
<script src="../js/companycontract_modal.js"></script>

<!-- Check for session success message -->
<?php if (isset($_SESSION['success'])): ?>
<script>
    // Wait for DOM to load and show notification
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof showNotification === 'function') {
                showNotification('<?= addslashes($_SESSION['success']) ?>');
            }
        }, 100);
    });
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

    <script src="../js/responsive-nav.js"></script>
</body>
</html>
