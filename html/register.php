<?php
require_once '../includes/functions.php';
startSecureSession();
sendSecurityHeaders();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="../assets/img/logo.png" alt="Logo" class="logo">
        </div>

        <h2>Choose Registration Type</h2>
        <a href="register_company.php" class="btn">Register as Company (Staff)</a>
        <a href="register_intern.php" class="btn">Register as Intern</a>
        <p>Already have an account? <a href="../index.php">Login</a></p>
    </div>
</body>
</html>
