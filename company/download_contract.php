<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/fpdf186/fpdf.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'signed'; // 'signed' or 'unsigned'

// Get contract details and verify it belongs to this company
$stmt = $pdo->prepare("
    SELECT ct.*, 
           ir.first_name, ir.last_name, ir.middle_name, ir.contact_no, ir.address,
           ir.city, ir.province, ir.postal_code, ir.university, ir.course, ir.year_level,
           u.email,
           i.title as internship_title, i.description, i.duration, i.allowance,
           i.start_date, i.end_date, i.internship_id,
           c.company_name, c.industry, c.contact_person, c.address,
           a.application_id, a.status as application_status,
           CONCAT(ir.first_name, ' ', ir.last_name) as intern_name
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    WHERE ct.contract_id = ? AND i.company_id = ?
");
$stmt->execute([$contract_id, $company['company_id']]);
$contract = $stmt->fetch();

if (!$contract) {
    header('Location: contracts.php');
    exit;
}

// Check if signed version exists and user wants signed version
if ($type === 'signed' && $contract['signed_file']) {
    $signed_file_path = '../uploads/contracts/' . $contract['signed_file'];
    
    if (file_exists($signed_file_path)) {
        // Serve the existing signed PDF
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Signed_Contract_' . $contract['intern_name'] . '_' . date('Y-m-d') . '.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($signed_file_path));
        readfile($signed_file_path);
        exit;
    }
}

// Format date function
function formatDate($date, $format = 'F d, Y') {
    return $date ? date($format, strtotime($date)) : 'Not set';
}

// Create PDF
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo (adjust path as needed)
        // $this->Image('../assets/img/logo.png', 10, 6, 30);
        
        // Title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'INTERNSHIP CONTRACT', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 11);
        $this->Cell(0, 10, 'Internship Agreement', 0, 1, 'C');
        
        // Line break
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        
        // Font
        $this->SetFont('Arial', 'I', 8);
        
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        
        // Generated date
        $this->SetY(-15);
        $this->SetX(-50);
        $this->Cell(0, 10, 'Generated: ' . date('Y-m-d H:i:s'), 0, 0, 'C');
    }

    // Section title
    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->Ln(2);
    }

    // Label and value
    function LabelValue($label, $value, $width = 95)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($width, 6, $label . ':', 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, $value, 0, 1, 'L');
    }
}

// Initialize PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Contract ID and Date
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Contract #: CT-' . str_pad($contract['contract_id'], 6, '0', STR_PAD_LEFT), 0, 1, 'R');
$pdf->Cell(0, 6, 'Date: ' . formatDate($contract['signed_date'] ?: date('Y-m-d')), 0, 1, 'R');
$pdf->Ln(5);

// PARTIES Section
$pdf->SectionTitle('PARTIES');

// Company Information
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'THE COMPANY', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $contract['company_name'] . "\n" .
                     'Represented by: ' . $contract['contact_person'] . "\n" .
                     'Address: ' . $contract['address']);
$pdf->Ln(5);

// Intern Information
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'THE INTERN', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $contract['intern_name'] . "\n" .
                     'Email: ' . $contract['email'] . "\n" .
                     'Contact: ' . $contract['contact_no']);
$pdf->Ln(5);

// INTERNSHIP DETAILS
$pdf->SectionTitle('INTERNSHIP DETAILS');

$pdf->LabelValue('Position', $contract['internship_title']);
$pdf->LabelValue('Duration', $contract['duration'] ?: 'Based on dates below');
//$pdf->LabelValue('Start Date', formatDate($contract['start_date'])); -->
//$pdf->LabelValue('End Date', formatDate($contract['end_date']));
$pdf->LabelValue('Allowance', 'P' . number_format($contract['allowance'], 2) );
$pdf->Ln(5);

// INTERN INFORMATION
$pdf->SectionTitle('INTERN INFORMATION');

$pdf->LabelValue('University', $contract['university'] ?: 'Not specified');
$pdf->LabelValue('Course', $contract['course'] ?: 'Not specified');
$pdf->LabelValue('Year Level', $contract['year_level'] ?: 'Not specified');
$pdf->LabelValue('Address', $contract['city'] . ', ' . $contract['province'] . ' ' . $contract['postal_code']);
$pdf->Ln(5);

// TERMS AND CONDITIONS
$pdf->SectionTitle('TERMS AND CONDITIONS');

$terms = "1. SCOPE OF WORK\n" .
         "The Intern agrees to perform the duties and responsibilities as described in the internship position. " .
         "The Intern shall comply with all company policies, rules, and regulations.\n\n" .
         
         "2. DURATION\n" .
         "This internship shall commence on " . formatDate($contract['start_date'], 'F j, Y') . 
         " and shall continue until " . formatDate($contract['end_date'], 'F j, Y') . 
         ", unless terminated earlier by either party.\n\n" .
         
         "3. COMPENSATION\n" .
         "The Intern shall receive a monthly allowance of P" . number_format($contract['allowance'], 2) . 
         ", payable on the " . date('jS', strtotime($contract['start_date'])) . " day of each month.\n\n" .
         
         "4. CONFIDENTIALITY\n" .
         "The Intern agrees to maintain the confidentiality of all company information and trade secrets " .
         "encountered during the internship.\n\n" .
         
         "5. TERMINATION\n" .
         "Either party may terminate this agreement with a written notice of at least 7 days. " .
         "The company reserves the right to terminate immediately for cause.";

$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $terms);
$pdf->Ln(5);

// SIGNATURES
$pdf->SectionTitle('SIGNATURES');

// Company signature
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'COMPANY REPRESENTATIVE', 0, 0, 'L');
$pdf->Cell(95, 6, 'INTERN', 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, '_________________________', 0, 0, 'L');
$pdf->Cell(95, 6, '_________________________', 0, 1, 'L');

$pdf->Cell(95, 6, $contract['contact_person'], 0, 0, 'L');
$pdf->Cell(95, 6, $contract['intern_name'], 0, 1, 'L');

$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(95, 6, 'Date: ' . ($contract['hr_confirmed'] ? formatDate($contract['signed_date']) : '______________'), 0, 0, 'L');
$pdf->Cell(95, 6, 'Date: ' . ($contract['signed_date'] ? formatDate($contract['signed_date']) : '______________'), 0, 1, 'L');

$pdf->Ln(10);

// Footer note
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 5, 'This contract is electronically generated and is valid without physical signatures.', 0, 1, 'C');

// Output the PDF
$pdf->Output('D', 'Contract_' . $contract['intern_name'] . '_' . date('Y-m-d') . '.pdf');
$filename = $contract['signed_file'] ? 'Signed_Contract_' : 'Unsigned_Contract_';
$filename .= $contract['intern_name'] . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
exit;
?>
