<?php
require_once 'db.php';
require_once 'functions.php';

startSecureSession();
sendSecurityHeaders();

const GENERIC_LOGIN_ERROR = 'Invalid email or password.';
$dummyHash = '$2y$10$wM7s7bQzL6g3s1vA3Q6Yze1O6Gq8mR0Q5JwAq5Y9Y5rYh6VQhYj3K';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken(['redirect' => '../index.php']);

    $email = trim($_POST['email'] ?? '');
    $input = trim($_POST['password'] ?? ''); // password OR OTP
    $normalizedEmail = strtolower($email);

    if (isRateLimited('login', [getClientIp(), 'email:' . $normalizedEmail], 5, 300)) {
        $_SESSION['error'] = GENERIC_LOGIN_ERROR;
        header('Location: ../index.php');
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Keep response and password verification behavior consistent to reduce
        // account-enumeration and scanner response-difference signals.
        password_verify($input, $dummyHash);
        $_SESSION['error'] = GENERIC_LOGIN_ERROR;
        header('Location: ../index.php');
        exit;
    }

    // Check account status only after user is found
    if ($user['status'] !== 'active') {
        $_SESSION['error'] = GENERIC_LOGIN_ERROR;
        header('Location: ../index.php');
        exit;
    }

    // Email must be verified
    if (!$user['verified']) {
        $_SESSION['error'] = GENERIC_LOGIN_ERROR;
        header('Location: ../index.php');
        exit;
    }

    // ===== FIRST LOGIN → OTP =====
    if (!empty($user['first_login'])) {

        $stmt = $pdo->prepare("
            SELECT * FROM verification_tokens
            WHERE user_id = ?
            AND token = ?
            AND type = 'otp'
            AND expiry > NOW()
            AND used = 0
            ORDER BY token_id DESC
            LIMIT 1
        ");
        $stmt->execute([$user['user_id'], $input]);
        $token = $stmt->fetch();

        if ($token) {

            $pdo->prepare("UPDATE verification_tokens SET used = 1 WHERE token_id = ?")
                ->execute([$token['token_id']]);

            $pdo->prepare("UPDATE users SET first_login = 0 WHERE user_id = ?")
                ->execute([$user['user_id']]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['success'] = 'Login successful! Welcome back.';

            switch ($user['user_type']) {
                case 'superadmin':
                case 'admin':
                    $_SESSION['redirect_to'] = 'superadmin/index.php';
                    break;
                case 'staff':
                    $_SESSION['redirect_to'] = 'company/index.php';
                    break;
                case 'intern':
                    $_SESSION['redirect_to'] = 'intern/index.php';
                    break;
            }

            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = GENERIC_LOGIN_ERROR;
            header('Location: ../index.php');
            exit;
        }
    }

    // ===== NORMAL PASSWORD LOGIN =====
    if (!empty($user['password_hash']) && password_verify($input, $user['password_hash'])) {

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['success'] = 'Login successful! Welcome back.';

        switch ($user['user_type']) {
            case 'superadmin':
            case 'admin':
                $_SESSION['redirect_to'] = 'superadmin/index.php';
                break;
            case 'staff':
                $_SESSION['redirect_to'] = 'company/index.php';
                break;
            case 'intern':
                $_SESSION['redirect_to'] = 'intern/index.php';
                break;
        }

        header('Location: ../index.php');
        exit;

    } else {
        $_SESSION['error'] = GENERIC_LOGIN_ERROR;
        header('Location: ../index.php');
        exit;
    }
}
?>
