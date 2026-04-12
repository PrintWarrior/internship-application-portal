<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT c.*, addr.address_line AS address
    FROM companies c
    LEFT JOIN addresses addr
        ON addr.entity_id = c.company_id
        AND addr.entity_type = 'company'
        AND addr.is_primary = 1
    WHERE c.company_id = ?
");
$stmt->execute([$id]);
$company = $stmt->fetch();

if (!$company) {
    logAction("Edit Company", "Attempted to edit non-existent company ID $id");
    die("Company not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['company_name'];
    $industry = $_POST['industry'];
    $phone = $_POST['contact_phone'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("
        UPDATE companies
        SET company_name=?, industry=?, contact_phone=?
        WHERE company_id=?
    ");

    $stmt->execute([$name,$industry,$phone,$id]);
    upsertEntityAddress((int) $id, 'company', [
        'address_line' => $address
    ]);

    logAction("Edit Company", "Updated company ID $id");

    header("Location: manage_companies.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Company</title>
</head>
<body>

<h2>Edit Company</h2>

<form method="POST">

<label>Company Name</label><br>
<input type="text" name="company_name" value="<?= htmlspecialchars($company['company_name']) ?>" required><br><br>

<label>Industry</label><br>
<input type="text" name="industry" value="<?= htmlspecialchars($company['industry']) ?>"><br><br>

<label>Phone</label><br>
<input type="text" name="contact_phone" value="<?= htmlspecialchars($company['contact_phone']) ?>"><br><br>

<label>Address</label><br>
<textarea name="address"><?= htmlspecialchars($company['address']) ?></textarea><br><br>

<button type="submit">Update</button>

</form>

<br>

<a href="manage_companies.php">Back</a>

</body>
</html>
