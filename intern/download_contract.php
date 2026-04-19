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
$auto_print = !isset($_GET['autoprint']) || $_GET['autoprint'] !== '0';

// Verify contract belongs to this intern
$stmt = $pdo->prepare("
    SELECT ct.*, 
           ir.first_name, ir.last_name, ir.contact_no, ir.university, ir.course, ir.year_level,
           u.email,
           i.title AS internship_title, i.duration, i.allowance, i.start_date, i.end_date,
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

function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($type === 'unsigned') {
    $intern_name = trim(($contract['first_name'] ?? '') . ' ' . ($contract['last_name'] ?? ''));
    $generated_date = date('F d, Y');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Print Contract <?= h($contract['contract_id']) ?></title>
        <style>
            :root {
                color-scheme: light;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Times New Roman", serif;
                background: #d9d9d9;
                color: #111;
            }

            .print-toolbar {
                max-width: 210mm;
                margin: 20px auto 0;
                padding: 12px 16px;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .print-toolbar button {
                border: 1px solid #111;
                background: #fff;
                padding: 10px 16px;
                font-size: 14px;
                cursor: pointer;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto 24px;
                background: #fff;
                padding: 18mm 16mm;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
            }

            h1, h2, h3, p {
                margin-top: 0;
            }

            h1 {
                text-align: center;
                font-size: 24px;
                letter-spacing: 1px;
                margin-bottom: 8px;
            }

            .subtitle {
                text-align: center;
                font-style: italic;
                margin-bottom: 28px;
            }

            .meta {
                text-align: right;
                margin-bottom: 24px;
                font-size: 14px;
            }

            .section {
                margin-bottom: 20px;
            }

            .section-title {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 10px;
                text-transform: uppercase;
            }

            .details p,
            .terms p {
                margin-bottom: 8px;
                line-height: 1.5;
            }

            .signature-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 28px;
                margin-top: 34px;
            }

            .signature-block {
                padding-top: 42px;
            }

            .signature-line {
                border-top: 1px solid #111;
                padding-top: 8px;
                margin-bottom: 10px;
            }

            .footer-note {
                margin-top: 30px;
                font-size: 13px;
                font-style: italic;
            }

            @media print {
                body {
                    background: #fff;
                }

                .print-toolbar {
                    display: none;
                }

                .page {
                    width: auto;
                    min-height: auto;
                    margin: 0;
                    padding: 0;
                    box-shadow: none;
                }

                @page {
                    size: A4;
                    margin: 15mm;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-toolbar">
            <button type="button" onclick="window.print()">Print Contract</button>
        </div>

        <main class="page">
            <h1>INTERNSHIP CONTRACT</h1>
            <p class="subtitle">Internship Agreement Document</p>

            <div class="meta">
                <div>Contract ID: CT-<?= h(str_pad($contract['contract_id'], 5, '0', STR_PAD_LEFT)) ?></div>
                <div>Generated: <?= h($generated_date) ?></div>
            </div>

            <section class="section details">
                <h2 class="section-title">Company Information</h2>
                <p><strong>Company Name:</strong> <?= h($contract['company_name']) ?></p>
                <p><strong>HR Representative:</strong> <?= h($contract['contact_person']) ?></p>
                <p><strong>Company Address:</strong> <?= h($contract['company_address'] ?: 'Not specified') ?></p>
            </section>

            <section class="section details">
                <h2 class="section-title">Intern Information</h2>
                <p><strong>Intern Name:</strong> <?= h($intern_name) ?></p>
                <p><strong>Email:</strong> <?= h($contract['email']) ?></p>
                <p><strong>Contact:</strong> <?= h($contract['contact_no']) ?></p>
                <p><strong>University:</strong> <?= h($contract['university']) ?></p>
                <p><strong>Course:</strong> <?= h($contract['course']) ?></p>
                <p><strong>Year Level:</strong> <?= h($contract['year_level']) ?></p>
            </section>

            <section class="section details">
                <h2 class="section-title">Internship Details</h2>
                <p><strong>Position:</strong> <?= h($contract['internship_title']) ?></p>
                <p><strong>Duration:</strong> <?= h($contract['duration']) ?></p>
                <p><strong>Start Date:</strong> <?= h(formatDate($contract['start_date'])) ?></p>
                <p><strong>End Date:</strong> <?= h(formatDate($contract['end_date'])) ?></p>
                <p><strong>Monthly Allowance:</strong> PHP <?= h(number_format((float)$contract['allowance'], 2)) ?></p>
            </section>

            <section class="section terms">
                <h2 class="section-title">Terms and Conditions</h2>
                <p>1. The intern agrees to perform duties assigned by the company under supervision.</p>
                <p>2. The internship will run from <?= h(formatDate($contract['start_date'])) ?> until <?= h(formatDate($contract['end_date'])) ?>.</p>
                <p>3. The intern will receive an allowance of PHP <?= h(number_format((float)$contract['allowance'], 2)) ?> per month.</p>
                <p>4. The intern must follow company policies, confidentiality rules, and professional conduct.</p>
                <p>5. Either party may terminate the internship agreement with proper notice if necessary.</p>
            </section>

            <section class="signature-grid">
                <div class="signature-block">
                    <div class="signature-line">Company Representative</div>
                    <div><?= h($contract['contact_person']) ?></div>
                    <div style="margin-top: 18px;">Date: ___________________</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">Intern</div>
                    <div><?= h($intern_name) ?></div>
                    <div style="margin-top: 18px;">Date: ___________________</div>
                </div>
            </section>

            <p class="footer-note">
                This contract is generated electronically by the Internship Portal System. It serves as the official agreement between the intern and the company.
            </p>
        </main>

        <?php if ($auto_print): ?>
            <script>
                window.addEventListener('load', function () {
                    window.print();
                });
            </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

/* Create PDF - A4 size, print-ready */
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

/* TITLE */
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,12,'INTERNSHIP CONTRACT',0,1,'C');

$pdf->SetFont('Arial','I',12);
$pdf->Cell(0,8,'Internship Agreement Document',0,1,'C');

$pdf->Ln(10);

/* CONTRACT INFO */
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,7,'Contract ID: CT-'.str_pad($contract['contract_id'],5,'0',STR_PAD_LEFT),0,1,'R');
$pdf->Cell(0,7,'Generated: '.date('F d, Y'),0,1,'R');

$pdf->Ln(8);

/* COMPANY SECTION */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'COMPANY INFORMATION',0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Company Name: '.$contract['company_name'],0,1);
$pdf->Cell(0,8,'HR Representative: '.$contract['contact_person'],0,1);
$pdf->Cell(0,8,'Company Address: '.$contract['company_address'],0,1);

$pdf->Ln(8);

/* INTERN SECTION */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'INTERN INFORMATION',0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Intern Name: '.$contract['first_name'].' '.$contract['last_name'],0,1);
$pdf->Cell(0,8,'Email: '.$contract['email'],0,1);
$pdf->Cell(0,8,'Contact: '.$contract['contact_no'],0,1);
$pdf->Cell(0,8,'University: '.$contract['university'],0,1);
$pdf->Cell(0,8,'Course: '.$contract['course'],0,1);
$pdf->Cell(0,8,'Year Level: '.$contract['year_level'],0,1);

$pdf->Ln(8);

/* INTERNSHIP DETAILS */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'INTERNSHIP DETAILS',0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Position: '.$contract['internship_title'],0,1);
$pdf->Cell(0,8,'Duration: '.$contract['duration'],0,1);
$pdf->Cell(0,8,'Start Date: '.formatDate($contract['start_date']),0,1);
$pdf->Cell(0,8,'End Date: '.formatDate($contract['end_date']),0,1);
$pdf->Cell(0,8,'Monthly Allowance: PHP '.number_format($contract['allowance'],2),0,1);

$pdf->Ln(8);

/* TERMS */
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'TERMS AND CONDITIONS',0,1);

$pdf->SetFont('Arial','',11);

$terms = 
"1. The intern agrees to perform duties assigned by the company under supervision.\n\n".
"2. The internship will run from ".formatDate($contract['start_date'])." until ".formatDate($contract['end_date']).".\n\n".
"3. The intern will receive an allowance of PHP ".number_format($contract['allowance'],2)." per month.\n\n".
"4. The intern must follow company policies, confidentiality rules, and professional conduct.\n\n".
"5. Either party may terminate the internship agreement with proper notice if necessary.";

$pdf->MultiCell(0,7,$terms);

$pdf->Ln(12);

/* SIGNATURE SECTION */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(95,12,'Company Representative',0,0,'L');
$pdf->Cell(95,12,'Intern',0,1,'L');

$pdf->SetFont('Arial','',12);
$pdf->Cell(95,12,'_________________________',0,0);
$pdf->Cell(95,12,'_________________________',0,1);

$pdf->Cell(95,8,$contract['contact_person'],0,0);
$pdf->Cell(95,8,$contract['first_name'].' '.$contract['last_name'],0,1);

$pdf->Cell(95,12,'Date: ___________________',0,0);
$pdf->Cell(95,12,'Date: ___________________',0,1);

$pdf->Ln(12);

/* FOOTER NOTE */
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,7,"This contract is generated electronically by the Internship Portal System. It serves as the official agreement between the intern and the company.");

$filename = $contract['signed_file'] ? 'Signed_Contract_' : 'Unsigned_Contract_';
$filename .= $contract_id . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
exit;
?>
