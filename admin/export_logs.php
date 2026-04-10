<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/fpdf186/fpdf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Admins can export all logs except actions performed by superadmins.
$stmt = $pdo->query("
    SELECT l.*, u.email
    FROM system_logs l
    LEFT JOIN users u ON l.user_id = u.user_id
    WHERE (l.user_id IS NULL OR u.user_type IS NULL OR u.user_type != 'superadmin')
    ORDER BY l.created_at DESC
");

$logs = $stmt->fetchAll();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'System Logs Report',0,1,'C');

$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,10,'User',1);
$pdf->Cell(60,10,'Action',1);
$pdf->Cell(60,10,'Date',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);

foreach ($logs as $log) {

$user = $log['email'] ? $log['email'] : 'SYSTEM';

$pdf->Cell(70,8,$user,1);
$pdf->Cell(60,8,$log['action'],1);
$pdf->Cell(60,8,$log['created_at'],1);
$pdf->Ln();

}

$pdf->Output('D','system_logs.pdf');
exit;   
