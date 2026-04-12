<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/fpdf186/fpdf.php'; // For PDF generation
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get contract details
$stmt = $pdo->prepare("
    SELECT ct.*, a.intern_id,
           ir.first_name, ir.last_name, u.email,
           i.title, i.start_date, i.end_date, i.allowance,
           c.company_name, c.contact_person, addr.address_line AS company_address
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    LEFT JOIN addresses addr
        ON addr.entity_id = c.company_id
        AND addr.entity_type = 'company'
        AND addr.is_primary = 1
    WHERE ct.contract_id = ? AND i.company_id = ?
");
$stmt->execute([$contract_id, $company['company_id']]);
$contract = $stmt->fetch();

if (!$contract) {
    header('Location: contracts.php');
    exit;
}

// Generate PDF with signature placeholders
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'INTERNSHIP EMPLOYMENT CONTRACT', 0, 1, 'C');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Contract content
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'PARTIES', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 7, "This Contract is entered into by and between:");
$pdf->Ln(5);

// Company details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, "THE COMPANY:", 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, $contract['company_name'], 0, 1);
$pdf->Cell(0, 7, "Represented by: " . $contract['contact_person'], 0, 1);
$pdf->Cell(0, 7, "Address: " . $contract['company_address'], 0, 1);
$pdf->Ln(5);

// Intern details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, "THE INTERN:", 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, $contract['first_name'] . ' ' . $contract['last_name'], 0, 1);
$pdf->Cell(0, 7, "Email: " . $contract['email'], 0, 1);
$pdf->Ln(10);

// Internship details
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'INTERNSHIP DETAILS', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, "Position: " . $contract['title'], 0, 1);
$pdf->Cell(0, 7, "Start Date: " . date('F d, Y', strtotime($contract['start_date'])), 0, 1);
$pdf->Cell(0, 7, "End Date: " . date('F d, Y', strtotime($contract['end_date'])), 0, 1);
$pdf->Cell(0, 7, "Allowance: ₱" . number_format($contract['allowance'], 2) . "/month", 0, 1);
$pdf->Ln(10);

// Terms and Conditions
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'TERMS AND CONDITIONS', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 7, "1. The Intern agrees to perform the duties assigned by the Company.");
$pdf->MultiCell(0, 7, "2. The Intern shall comply with all company policies and regulations.");
$pdf->MultiCell(0, 7, "3. This internship shall commence on the start date and end on the end date specified above.");
$pdf->MultiCell(0, 7, "4. The Intern shall receive the monthly allowance as specified above.");
$pdf->MultiCell(0, 7, "5. The Intern agrees to maintain confidentiality of all company information.");
$pdf->Ln(15);

// SIGNATURE SECTION - Placeholders for intern signature
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'SIGNATURES', 0, 1);
$pdf->Ln(5);

// Company signature (pre-filled)
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, "COMPANY REPRESENTATIVE:", 0, 1);
$pdf->Ln(5);
$pdf->Cell(0, 7, "_________________________", 0, 1);
$pdf->Cell(0, 7, $contract['contact_person'], 0, 1);
$pdf->Cell(0, 7, "Date: " . date('F d, Y'), 0, 1);
$pdf->Ln(10);

// Intern signature (empty for intern to sign)
$pdf->Cell(0, 7, "INTERN:", 0, 1);
$pdf->Ln(5);
$pdf->Cell(0, 7, "_________________________", 0, 1); // Signature line
$pdf->Cell(0, 7, "Printed Name: _________________________", 0, 1);
$pdf->Cell(0, 7, "Date: _________________________", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 7, "Please sign and date above, then upload the signed PDF through the internship portal.");

// Save PDF temporarily
$pdf_filename = 'contract_' . $contract_id . '_' . time() . '.pdf';
$pdf_path = '../temp_contracts/' . $pdf_filename;
$pdf->Output('F', $pdf_path);

// Update contract with PDF path
$stmt = $pdo->prepare("UPDATE contracts SET contract_pdf = ?, contract_file = ? WHERE contract_id = ?");
$stmt->execute([$pdf_filename, $pdf_filename, $contract_id]);

// Create notification for intern
$stmt = $pdo->prepare("
    SELECT user_id FROM interns WHERE intern_id = ?
");
$stmt->execute([$contract['intern_id']]);
$intern_user_id = $stmt->fetchColumn();

$message = "A new contract for '" . $contract['title'] . "' is ready for your signature. Please review and sign.";
$notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
$notifStmt->execute([$intern_user_id, $message, 'intern/contracts.php']);

// Redirect back with success message
header("Location: view_contract.php?id=" . $contract_id . "&sent=1");
exit;
?>
