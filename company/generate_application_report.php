<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/fpdf186/fpdf.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);

// Initialize variables
$filters = [
    'status' => $_GET['status'] ?? 'all',
    'internship_id' => $_GET['internship_id'] ?? 'all',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'export_type' => $_GET['export_type'] ?? '' // 'pdf' or 'csv'
];

// Get all internships for filter dropdown
$stmt = $pdo->prepare("
    SELECT internship_id, title
    FROM internships
    WHERE company_id = ?
    ORDER BY title
");
$stmt->execute([$company['company_id']]);
$internships = $stmt->fetchAll();

// If export is requested, process it
if ($filters['export_type'] && in_array($filters['export_type'], ['pdf', 'csv', 'print'])) {
    try {
        $inTransaction = false;
        $pdo->beginTransaction();
        $inTransaction = true;

        // Build query with filters
        $query = "
            SELECT a.*, i.title, ir.first_name, ir.last_name, ir.contact_no, u.email
            FROM applications a
            JOIN internships i ON a.internship_id = i.internship_id
            JOIN interns ir ON a.intern_id = ir.intern_id
            JOIN users u ON ir.user_id = u.user_id
            WHERE i.company_id = ?
        ";
        $params = [$company['company_id']];

        // Apply filters
        if ($filters['status'] !== 'all') {
            $query .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if ($filters['internship_id'] !== 'all') {
            $query .= " AND a.internship_id = ?";
            $params[] = $filters['internship_id'];
        }

        if ($filters['start_date']) {
            $query .= " AND DATE(a.date_applied) >= ?";
            $params[] = $filters['start_date'];
        }

        if ($filters['end_date']) {
            $query .= " AND DATE(a.date_applied) <= ?";
            $params[] = $filters['end_date'];
        }

        $query .= " ORDER BY a.date_applied DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        // Prepare filter description for logging
        $filter_desc = json_encode([
            'status' => $filters['status'],
            'internship_id' => $filters['internship_id'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'export_type' => $filters['export_type']
        ]);

        // Log report generation in transaction
        $logStmt = $pdo->prepare("
            INSERT INTO report_logs (user_id, company_id, report_type, filters, export_format, record_count, generated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([
            $user_id,
            $company['company_id'],
            'Application Report',
            $filter_desc,
            strtoupper($filters['export_type']),
            count($applications)
        ]);

        $pdo->commit();

        // Generate export
        if ($filters['export_type'] === 'pdf') {
            generateApplicationPDF($applications, $filters, $company);
        } elseif ($filters['export_type'] === 'csv') {
            generateApplicationCSV($applications, $filters, $company);
        } elseif ($filters['export_type'] === 'print') {
            generatePrintView($applications, $filters, $company, $user_id);
        }
        exit;

    } catch (Exception $e) {
        if (isset($inTransaction) && $inTransaction) {
            $pdo->rollBack();
        }
        $error_msg = "Error generating report: " . $e->getMessage();
    }
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Function to generate PDF report
function generateApplicationPDF($applications, $filters, $company) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, 'Application Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Company: ' . htmlspecialchars($company['company_name']), 0, 1);
    $pdf->Cell(0, 5, 'Generated: ' . date('M d, Y H:i A'), 0, 1);
    $pdf->Ln(5);

    // Filters applied
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, 'Filters Applied:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    if ($filters['status'] !== 'all') $pdf->Cell(0, 4, '• Status: ' . $filters['status'], 0, 1);
    if ($filters['internship_id'] !== 'all') $pdf->Cell(0, 4, '• Internship: ' . getInternshipTitle($filters['internship_id']), 0, 1);
    if ($filters['start_date']) $pdf->Cell(0, 4, '• From: ' . date('M d, Y', strtotime($filters['start_date'])), 0, 1);
    if ($filters['end_date']) $pdf->Cell(0, 4, '• To: ' . date('M d, Y', strtotime($filters['end_date'])), 0, 1);
    $pdf->Ln(3);

    // Statistics
    $total = count($applications);
    $pending = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
    $accepted = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
    $offered = count(array_filter($applications, fn($a) => $a['status'] === 'Offered'));
    $declined = count(array_filter($applications, fn($a) => $a['status'] === 'Declined'));

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Summary Statistics:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(50, 4, "Total: $total", 0, 0);
    $pdf->Cell(50, 4, "Pending: $pending", 0, 0);
    $pdf->Cell(50, 4, "Accepted: $accepted", 0, 1);
    $pdf->Cell(50, 4, "Offered: $offered", 0, 0);
    $pdf->Cell(50, 4, "Declined: $declined", 0, 1);
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(40, 7, 'Applicant', 1, 0, 'L', true);
    $pdf->Cell(40, 7, 'Internship', 1, 0, 'L', true);
    $pdf->Cell(30, 7, 'Applied Date', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Contact', 1, 1, 'L', true);

    // Table Data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetFillColor(240, 240, 240);
    $fill = false;

    foreach ($applications as $app) {
        $pdf->Cell(40, 6, substr($app['first_name'] . ' ' . $app['last_name'], 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell(40, 6, substr($app['title'], 0, 20), 1, 0, 'L', $fill);
        $pdf->Cell(30, 6, date('m/d/Y', strtotime($app['date_applied'])), 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $app['status'], 1, 0, 'C', $fill);
        $pdf->Cell(35, 6, substr($app['contact_no'], 0, 15), 1, 1, 'L', $fill);
        $fill = !$fill;
    }

    $filename = 'Application_Report_' . date('Ymd_His') . '.pdf';
    $pdf->Output('D', $filename);
}

// Function to generate CSV report
function generateApplicationCSV($applications, $filters, $company) {
    $filename = 'Application_Report_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Header info
    fputcsv($output, ['Application Report']);
    fputcsv($output, ['Company', $company['company_name']]);
    fputcsv($output, ['Generated', date('M d, Y H:i A')]);
    fputcsv($output, []);

    // Filters
    fputcsv($output, ['Filters Applied:']);
    if ($filters['status'] !== 'all') fputcsv($output, ['Status', $filters['status']]);
    if ($filters['internship_id'] !== 'all') fputcsv($output, ['Internship', getInternshipTitle($filters['internship_id'])]);
    if ($filters['start_date']) fputcsv($output, ['From', date('M d, Y', strtotime($filters['start_date']))]);
    if ($filters['end_date']) fputcsv($output, ['To', date('M d, Y', strtotime($filters['end_date']))]);
    fputcsv($output, []);

    // Statistics
    $total = count($applications);
    $pending = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
    $accepted = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
    $offered = count(array_filter($applications, fn($a) => $a['status'] === 'Offered'));
    $declined = count(array_filter($applications, fn($a) => $a['status'] === 'Declined'));

    fputcsv($output, ['Summary Statistics']);
    fputcsv($output, ['Total', $total]);
    fputcsv($output, ['Pending', $pending]);
    fputcsv($output, ['Accepted', $accepted]);
    fputcsv($output, ['Offered', $offered]);
    fputcsv($output, ['Declined', $declined]);
    fputcsv($output, []);

    // Table Header
    fputcsv($output, ['Applicant', 'Email', 'Internship', 'Applied Date', 'Status', 'Contact Number']);

    // Table Data
    foreach ($applications as $app) {
        fputcsv($output, [
            $app['first_name'] . ' ' . $app['last_name'],
            $app['email'],
            $app['title'],
            date('M d, Y', strtotime($app['date_applied'])),
            $app['status'],
            $app['contact_no']
        ]);
    }

    fclose($output);
}

// Helper function to get internship title
function getInternshipTitle($internship_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT title FROM internships WHERE internship_id = ?");
    $stmt->execute([$internship_id]);
    return $stmt->fetchColumn() ?: 'Unknown';
}

// Function to generate print view
function generatePrintView($applications, $filters, $company, $user_id) {
    $total = count($applications);
    $pending = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
    $accepted = count(array_filter($applications, fn($a) => $a['status'] === 'Accepted'));
    $offered = count(array_filter($applications, fn($a) => $a['status'] === 'Offered'));
    $declined = count(array_filter($applications, fn($a) => $a['status'] === 'Declined'));
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Application Report - Print</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            
            .print-container {
                max-width: 900px;
                margin: 20px auto;
                padding: 30px;
                background: white;
            }
            
            .report-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #333;
                padding-bottom: 15px;
            }
            
            .report-header h1 {
                font-size: 28px;
                margin-bottom: 5px;
                color: #2c3e50;
            }
            
            .report-header p {
                font-size: 12px;
                color: #666;
                margin: 3px 0;
            }
            
            .company-info {
                background: #f5f5f5;
                padding: 15px;
                border-left: 4px solid #4CAF50;
                margin-bottom: 20px;
                border-radius: 3px;
            }
            
            .company-info h3 {
                margin-bottom: 5px;
                color: #2c3e50;
                font-size: 14px;
            }
            
            .company-info p {
                margin: 2px 0;
                font-size: 13px;
            }
            
            .filters-section {
                background: #e3f2fd;
                padding: 12px;
                border-left: 4px solid #2196F3;
                margin-bottom: 20px;
                border-radius: 3px;
            }
            
            .filters-section h3 {
                margin-bottom: 8px;
                color: #1565c0;
                font-size: 13px;
            }
            
            .filters-section p {
                font-size: 12px;
                margin: 2px 0;
                color: #444;
            }
            
            .stats-container {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 15px;
                margin-bottom: 25px;
            }
            
            .stat-box {
                background: white;
                border: 2px solid #e0e0e0;
                padding: 15px;
                text-align: center;
                border-radius: 5px;
                page-break-inside: avoid;
            }
            
            .stat-box h4 {
                font-size: 24px;
                color: #4CAF50;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .stat-box p {
                font-size: 12px;
                color: #666;
            }
            
            .table-section {
                margin-top: 25px;
            }
            
            .table-section h3 {
                margin-bottom: 12px;
                color: #2c3e50;
                font-size: 14px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            
            thead {
                background: #2c3e50;
                color: white;
            }
            
            th {
                padding: 12px;
                text-align: left;
                font-size: 12px;
                font-weight: bold;
                border-bottom: 2px solid #2c3e50;
            }
            
            td {
                padding: 10px 12px;
                font-size: 11px;
                border-bottom: 1px solid #ddd;
            }
            
            tbody tr:nth-child(even) {
                background: #f9f9f9;
            }
            
            tbody tr:hover {
                background: #f0f0f0;
            }
            
            .status-pending {
                background: #fff3cd;
                color: #856404;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }
            
            .status-accepted {
                background: #d4edda;
                color: #155724;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }
            
            .status-offered {
                background: #cfe2ff;
                color: #084298;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }
            
            .status-declined {
                background: #f8d7da;
                color: #721c24;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
            }
            
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
                font-size: 11px;
                color: #666;
                text-align: center;
            }
            
            .no-data {
                text-align: center;
                padding: 30px;
                color: #999;
                font-style: italic;
            }
            
            .print-button {
                text-align: center;
                margin-bottom: 20px;
                no-print: true;
            }
            
            .print-button button {
                padding: 10px 30px;
                background: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                font-weight: bold;
            }
            
            .print-button button:hover {
                background: #45a049;
            }
            
            @media print {
                .print-button {
                    display: none;
                }
                body {
                    margin: 0;
                    padding: 0;
                }
                .print-container {
                    max-width: 100%;
                    margin: 0;
                    padding: 20px;
                }
                .stat-box {
                    page-break-inside: avoid;
                }
                table {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <div class="print-button">
                <button onclick="window.print()">🖨️ Print Report</button>
            </div>
            
            <div class="report-header">
                <h1>📊 Application Report</h1>
                <p>Generated on: <?= date('M d, Y \a\t H:i A') ?></p>
                <p>Report ID: <?= date('Ymd_His') ?></p>
            </div>
            
            <div class="company-info">
                <h3>Company Information</h3>
                <p><strong>Company Name:</strong> <?= htmlspecialchars($company['company_name']) ?></p>
                <p><strong>Contact Email:</strong> <?= htmlspecialchars($company['contact_email']) ?></p>
                <p><strong>Contact Phone:</strong> <?= htmlspecialchars($company['contact_phone']) ?></p>
            </div>
            
            <div class="filters-section">
                <h3>📋 Filters Applied</h3>
                <?php if ($filters['status'] !== 'all'): ?>
                    <p><strong>Status:</strong> <?= htmlspecialchars($filters['status']) ?></p>
                <?php endif; ?>
                <?php if ($filters['internship_id'] !== 'all'): ?>
                    <p><strong>Internship:</strong> <?= htmlspecialchars(getInternshipTitle($filters['internship_id'])) ?></p>
                <?php endif; ?>
                <?php if ($filters['start_date']): ?>
                    <p><strong>From Date:</strong> <?= date('M d, Y', strtotime($filters['start_date'])) ?></p>
                <?php endif; ?>
                <?php if ($filters['end_date']): ?>
                    <p><strong>To Date:</strong> <?= date('M d, Y', strtotime($filters['end_date'])) ?></p>
                <?php endif; ?>
                <?php if ($filters['status'] === 'all' && $filters['internship_id'] === 'all' && !$filters['start_date'] && !$filters['end_date']): ?>
                    <p><em>No filters applied - showing all applications</em></p>
                <?php endif; ?>
            </div>
            
            <div class="stats-container">
                <div class="stat-box">
                    <h4><?= $total ?></h4>
                    <p>Total Applications</p>
                </div>
                <div class="stat-box">
                    <h4><?= $pending ?></h4>
                    <p>Pending</p>
                </div>
                <div class="stat-box">
                    <h4><?= $accepted ?></h4>
                    <p>Accepted</p>
                </div>
                <div class="stat-box">
                    <h4><?= $offered ?></h4>
                    <p>Offered</p>
                </div>
                <div class="stat-box">
                    <h4><?= $declined ?></h4>
                    <p>Declined</p>
                </div>
            </div>
            
            <div class="table-section">
                <h3>📝 Applicants Details</h3>
                <?php if (count($applications) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Email</th>
                                <th>Internship</th>
                                <th>Applied Date</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></td>
                                    <td><?= htmlspecialchars($app['email']) ?></td>
                                    <td><?= htmlspecialchars($app['title']) ?></td>
                                    <td><?= date('M d, Y', strtotime($app['date_applied'])) ?></td>
                                    <td><?= htmlspecialchars($app['contact_no']) ?></td>
                                    <td>
                                        <span class="status-<?= strtolower($app['status']) ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        No applications found matching the selected filters.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer">
                <p>This is an official report generated by the Internship Portal system.</p>
                <p>Generated by Staff ID: <?= htmlspecialchars($user_id) ?> | <?= date('M d, Y H:i:s') ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Application Report</title>
    <link rel="stylesheet" href="../assets/css/company_applicant.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <style>
        .report-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-export {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-export-pdf {
            background: #dc3545;
            color: white;
        }

        .btn-export-pdf:hover {
            background: #c82333;
        }

        .btn-export-csv {
            background: #28a745;
            color: white;
        }

        .btn-export-csv:hover {
            background: #218838;
        }

        .btn-export-print {
            background: #007bff;
            color: white;
        }

        .btn-export-print:hover {
            background: #0056b3;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
            margin-top: 15px;
            border: 1px solid #bee5eb;
        }
    </style>
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
            <a href="staff_profile.php">Staff Profile</a>
            <a href="post_internship.php">Post Internship</a>
            <a href="manage_internships.php">My Internships</a>
            <a href="view_applicants.php">View Applicants</a>
            <a href="generate_application_report.php">Reports</a>
            <a href="contracts.php">Contracts</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Generate Application Report</h2>

            <?php if (isset($error_msg)): ?>
                <div class="error-message"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form method="GET" class="report-form">
                <div class="form-group">
                    <label for="status">Filter by Status:</label>
                    <select id="status" name="status">
                        <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="Pending" <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Accepted" <?= $filters['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="Offered" <?= $filters['status'] === 'Offered' ? 'selected' : '' ?>>Offered</option>
                        <option value="Declined" <?= $filters['status'] === 'Declined' ? 'selected' : '' ?>>Declined</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="internship_id">Filter by Internship:</label>
                    <select id="internship_id" name="internship_id">
                        <option value="all" <?= $filters['internship_id'] === 'all' ? 'selected' : '' ?>>All Internships</option>
                        <?php foreach ($internships as $internship): ?>
                            <option value="<?= $internship['internship_id'] ?>" 
                                <?= $filters['internship_id'] == $internship['internship_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($internship['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">From Date (Optional):</label>
                    <input type="date" id="start_date" name="start_date" 
                        value="<?= htmlspecialchars($filters['start_date']) ?>">
                </div>

                <div class="form-group">
                    <label for="end_date">To Date (Optional):</label>
                    <input type="date" id="end_date" name="end_date" 
                        value="<?= htmlspecialchars($filters['end_date']) ?>">
                </div>

                <div class="export-buttons">
                    <button type="submit" name="export_type" value="pdf" class="btn-export btn-export-pdf">
                        📄 Export as PDF
                    </button>
                    <button type="submit" name="export_type" value="csv" class="btn-export btn-export-csv">
                        📊 Export as CSV
                    </button>
                    <button type="submit" name="export_type" value="print" class="btn-export btn-export-print">
                        🖨️ Print Report
                    </button>
                </div>

                <div class="info-box">
                    <strong>ℹ️ Info:</strong> Reports are logged with timestamps and filters for audit trails. Your staff ID will be recorded with each generated report.
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLogoutModal()">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <div class="modal-buttons">
                <button class="btn-confirm" onclick="confirmLogout()">Yes, Logout</button>
                <button class="btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'block';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('logoutModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>
