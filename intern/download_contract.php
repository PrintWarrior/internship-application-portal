<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/fpdf186/fpdf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'signed'; // 'signed' or 'unsigned'

// Verify contract belongs to this intern
$stmt = $pdo->prepare("
    SELECT ct.*, 
           ir.first_name, ir.last_name, ir.contact_no, ir.university, ir.course, ir.year_level,
           u.email,
           i.title AS internship_title, i.duration, i.allowance, i.start_date, i.end_date,
           c.company_name, c.contact_person, c.address
    FROM contracts ct
    JOIN applications a ON ct.application_id = a.application_id
    JOIN internships i ON a.internship_id = i.internship_id
    JOIN companies c ON i.company_id = c.company_id
    JOIN interns ir ON a.intern_id = ir.intern_id
    JOIN users u ON ir.user_id = u.user_id
    WHERE ct.contract_id = ? AND ir.user_id = ?
");
$stmt->execute([$contract_id, $user_id]);
$contract = $stmt->fetch();

if (!$contract) {
    die("Unauthorized access.");
}

// Check if signed version exists and user wants signed version
if ($type === 'signed' && $contract['signed_file']) {
    $signed_file_path = '../uploads/contracts/' . $contract['signed_file'];
    
    if (file_exists($signed_file_path)) {
        // Serve the existing signed PDF
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Signed_Contract_' . $contract_id . '_' . date('Y-m-d') . '.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($signed_file_path));
        readfile($signed_file_path);
        exit;
    }
}

/* Helper for formatting date */
function formatDate($date){
    return $date ? date('F d, Y', strtotime($date)) : 'Not specified';
}

/* Create PDF */
$pdf = new FPDF();
$pdf->AddPage();

/* TITLE */
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'INTERNSHIP CONTRACT',0,1,'C');

$pdf->SetFont('Arial','I',11);
$pdf->Cell(0,8,'Internship Agreement Document',0,1,'C');

$pdf->Ln(8);

/* CONTRACT INFO */
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,'Contract ID: CT-'.str_pad($contract['contract_id'],5,'0',STR_PAD_LEFT),0,1,'R');
$pdf->Cell(0,6,'Generated: '.date('F d, Y'),0,1,'R');

$pdf->Ln(5);

/* COMPANY SECTION */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'COMPANY INFORMATION',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,'Company Name: '.$contract['company_name'],0,1);
$pdf->Cell(0,7,'HR Representative: '.$contract['contact_person'],0,1);
$pdf->Cell(0,7,'Company Address: '.$contract['address'],0,1);

$pdf->Ln(5);

/* INTERN SECTION */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'INTERN INFORMATION',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,'Intern Name: '.$contract['first_name'].' '.$contract['last_name'],0,1);
$pdf->Cell(0,7,'Email: '.$contract['email'],0,1);
$pdf->Cell(0,7,'Contact: '.$contract['contact_no'],0,1);
$pdf->Cell(0,7,'University: '.$contract['university'],0,1);
$pdf->Cell(0,7,'Course: '.$contract['course'],0,1);
$pdf->Cell(0,7,'Year Level: '.$contract['year_level'],0,1);

$pdf->Ln(5);

/* INTERNSHIP DETAILS */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'INTERNSHIP DETAILS',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,'Position: '.$contract['internship_title'],0,1);
$pdf->Cell(0,7,'Duration: '.$contract['duration'],0,1);
//$pdf->Cell(0,7,'Start Date: '.formatDate($contract['start_date']),0,1);
//$pdf->Cell(0,7,'End Date: '.formatDate($contract['end_date']),0,1);
$pdf->Cell(0,7,'Allowance: P'.number_format($contract['allowance'],2),0,1);

$pdf->Ln(6);

/* TERMS */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'TERMS AND CONDITIONS',0,1);

$pdf->SetFont('Arial','',10);

$terms = 
"1. The intern agrees to perform duties assigned by the company under supervision.\n\n".
"2. The internship will run from ".formatDate($contract['start_date'])." until ".formatDate($contract['end_date']).".\n\n".
"3. The intern will receive an allowance of P".number_format($contract['allowance'],2)." per month.\n\n".
"4. The intern must follow company policies, confidentiality rules, and professional conduct.\n\n".
"5. Either party may terminate the internship agreement with proper notice if necessary.";

$pdf->MultiCell(0,6,$terms);

$pdf->Ln(10);

/* SIGNATURE SECTION */
$pdf->SetFont('Arial','B',11);
$pdf->Cell(95,8,'Company Representative',0,0,'L');
$pdf->Cell(95,8,'Intern',0,1,'L');

$pdf->SetFont('Arial','',11);
$pdf->Cell(95,8,'_________________________',0,0);
$pdf->Cell(95,8,'_________________________',0,1);

$pdf->Cell(95,8,$contract['contact_person'],0,0);
$pdf->Cell(95,8,$contract['first_name'].' '.$contract['last_name'],0,1);

//$pdf->Cell(95,8,'Date: ___________________',0,0);
//$pdf->Cell(95,8,'Date: ___________________',0,1);

$pdf->Ln(10);

/* FOOTER NOTE */
$pdf->SetFont('Arial','I',9);
$pdf->MultiCell(0,6,"This contract is generated electronically by the Internship Portal System. It serves as the official agreement between the intern and the company.");

$pdf->Output('D','Internship_Contract_'.$contract['contract_id'].'.pdf');
$filename = $contract['signed_file'] ? 'Signed_Contract_' : 'Unsigned_Contract_';
$filename .= $contract_id . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
exit;
?>