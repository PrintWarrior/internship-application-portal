<?php
require_once 'db.php';
require_once 'phpmailer/vendor/autoload.php'; // adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$GLOBALS['last_email_error'] = null;

function loadAppEnv() {
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $loaded = true;
    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envPath) || !is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

function appEnv($key, $default = null) {
    loadAppEnv();

    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }

    return $default;
}

function appUrl($path = '') {
    $baseUrl = rtrim((string) appEnv('APP_BASE_URL', ''), '/');
    if ($baseUrl === '') {
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;
    }

    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

function sendSecurityHeaders() {
    static $sent = false;

    if ($sent || headers_sent()) {
        return;
    }

    $sent = true;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
}

function startSecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    session_start();
}

function getCsrfToken() {
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function validateCsrfToken($token = null) {
    startSecureSession();

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $submittedToken = $token ?? ($_POST['csrf_token'] ?? '');

    return is_string($submittedToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $submittedToken);
}

function requireValidCsrfToken($options = []) {
    $redirect = $options['redirect'] ?? null;
    $json = (bool) ($options['json'] ?? false);
    $message = $options['message'] ?? 'Invalid request.';

    if (validateCsrfToken()) {
        return true;
    }

    http_response_code(403);

    if ($json) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['error'] = $message;
    }

    if ($redirect) {
        header('Location: ' . $redirect);
        exit;
    }

    exit($message);
}

function safeStoredFilename($filename) {
    $filename = trim((string) $filename);
    if ($filename === '' || basename($filename) !== $filename) {
        return null;
    }

    if (!preg_match('/^[A-Za-z0-9._ -]+$/', $filename)) {
        return null;
    }

    return $filename;
}

function managedDirectoryPath($directory) {
    $path = realpath($directory);
    if ($path !== false) {
        return $path;
    }

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return null;
    }

    return realpath($directory) ?: null;
}

function managedFilePath($directory, $filename) {
    $safeFilename = safeStoredFilename($filename);
    $basePath = managedDirectoryPath($directory);

    if ($safeFilename === null || $basePath === null) {
        return null;
    }

    return $basePath . DIRECTORY_SEPARATOR . $safeFilename;
}

function deleteManagedFile($directory, $filename) {
    $filePath = managedFilePath($directory, $filename);
    if ($filePath === null || !is_file($filePath)) {
        return false;
    }

    return unlink($filePath);
}

function storeUploadedFile(array $file, array $allowedMimeMap, $prefix, $directory, $maxBytes) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
        return null;
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $tmpName) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!is_string($mimeType) || !isset($allowedMimeMap[$mimeType])) {
        return null;
    }

    $extension = $allowedMimeMap[$mimeType];
    $basePath = managedDirectoryPath($directory);
    if ($basePath === null) {
        return null;
    }

    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $basePath . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        return null;
    }

    return $filename;
}

function storeUploadedImage(array $file, $prefix, $directory, $maxBytes = 2097152) {
    return storeUploadedFile($file, [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ], $prefix, $directory, $maxBytes);
}

function storeUploadedPdf(array $file, $prefix, $directory, $maxBytes = 5242880) {
    return storeUploadedFile($file, [
        'application/pdf' => 'pdf',
    ], $prefix, $directory, $maxBytes);
}

function configureMailer(PHPMailer $mail) {
    $host = appEnv('SMTP_HOST', '');
    $username = appEnv('SMTP_USERNAME', '');
    $password = appEnv('SMTP_PASSWORD', '');
    $port = (int) appEnv('SMTP_PORT', 587);
    $secure = appEnv('SMTP_SECURE', 'tls');
    $fromAddress = appEnv('SMTP_FROM_EMAIL', $username);
    $fromName = appEnv('SMTP_FROM_NAME', 'Intern Portal');

    $missing = [];
    if ($host === '') {
        $missing[] = 'SMTP_HOST';
    }
    if ($username === '') {
        $missing[] = 'SMTP_USERNAME';
    }
    if ($password === '') {
        $missing[] = 'SMTP_PASSWORD';
    }
    if ($fromAddress === '') {
        $missing[] = 'SMTP_FROM_EMAIL';
    }

    if ($missing !== []) {
        throw new Exception('SMTP configuration is incomplete: ' . implode(', ', $missing));
    }

    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = $secure;
    $mail->Port = $port;
    $mail->setFrom($fromAddress, $fromName);
}

function setLastEmailError($message) {
    $GLOBALS['last_email_error'] = $message;
}

function getLastEmailError() {
    return $GLOBALS['last_email_error'] ?? null;
}

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    setLastEmailError(null);

    try {
        configureMailer($mail);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    } catch (Exception $e) {
        setLastEmailError($e->getMessage());
        error_log('Email send failed for ' . $to . ': ' . $e->getMessage());
        return false;
    }
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}

function getClientIp() {
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        if (!$candidate) {
            continue;
        }

        $ip = trim(explode(',', $candidate)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'unknown';
}

function ensureRateLimitDirectory() {
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'rate_limits';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    return $directory;
}

function rateLimitFilePath($action, $scope) {
    $safeAction = preg_replace('/[^a-zA-Z0-9_-]/', '_', $action);
    return ensureRateLimitDirectory() . DIRECTORY_SEPARATOR . $safeAction . '_' . hash('sha256', $scope) . '.json';
}

function rateLimitExceeded($action, $scope, $maxAttempts, $windowSeconds) {
    $file = rateLimitFilePath($action, $scope);
    $now = time();
    $attempts = [];

    if (is_file($file)) {
        $existing = json_decode((string) file_get_contents($file), true);
        if (is_array($existing)) {
            $attempts = array_filter($existing, static fn ($timestamp) => is_int($timestamp) && ($timestamp > ($now - $windowSeconds)));
        }
    }

    if (count($attempts) >= $maxAttempts) {
        file_put_contents($file, json_encode(array_values($attempts)), LOCK_EX);
        return true;
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode(array_values($attempts)), LOCK_EX);
    return false;
}

function isRateLimited($action, array $scopes, $maxAttempts, $windowSeconds) {
    foreach ($scopes as $scope) {
        if ($scope === null || $scope === '') {
            continue;
        }

        if (rateLimitExceeded($action, (string) $scope, $maxAttempts, $windowSeconds)) {
            return true;
        }
    }

    return false;
}

function getAdminNotificationRecipients($preferredUserId = null) {
    global $pdo;

    $recipientIds = [];

    if ($preferredUserId) {
        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE user_id = ? AND user_type IN ('admin', 'superadmin') AND status = 'active'
        ");
        $stmt->execute([$preferredUserId]);
        $preferred = $stmt->fetchColumn();

        if ($preferred) {
            $recipientIds[] = (int) $preferred;
        }
    }

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE user_type IN ('admin', 'superadmin') AND status = 'active'
    ");
    $stmt->execute();

    $recipientIds = array_merge($recipientIds, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));

    return array_values(array_unique($recipientIds));
}

function createNotification($admin_id, $message, $action_url, $action_label, $related_user_id) {
    global $pdo;

    $recipientIds = getAdminNotificationRecipients($admin_id);
    if (!$recipientIds) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, action_url, action_label, related_user_id, link)
        VALUES (?, ?, ?, ?, ?, '')
    ");

    foreach ($recipientIds as $recipientId) {
        $stmt->execute([$recipientId, $message, $action_url, $action_label, $related_user_id]);
    }

    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function isAdminAreaUser($userType = null) {
    $userType = $userType ?? ($_SESSION['user_type'] ?? null);
    return in_array($userType, ['admin', 'superadmin'], true);
}

function isSuperAdminUser($userType = null) {
    $userType = $userType ?? ($_SESSION['user_type'] ?? null);
    return $userType === 'superadmin';
}

function requireAdminAreaAccess() {
    if (!isset($_SESSION['user_id']) || !isAdminAreaUser()) {
        header('Location: ../index.php');
        exit;
    }
}

function getAdminAreaLabel() {
    return isSuperAdminUser() ? 'Super Admin' : 'Admin';
}

function requireStaffUser() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
        header('Location: ../index.php');
        exit;
    }
}

function getStaffCompanyContext($userId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            s.staff_id,
            s.user_id,
            s.company_id,
            s.first_name,
            s.last_name,
            s.email AS staff_email,
            s.contact_no AS staff_contact_no,
            s.position,
            s.profile_image AS staff_profile_image,
            c.company_name,
            c.contact_phone,
            c.industry,
            c.contact_person,
            c.website,
            c.description,
            c.profile_image,
            c.contact_email,
            c.created_at,
            c.updated_at,
            addr.address_line AS address,
            addr.city,
            addr.province,
            addr.postal_code,
            addr.country
        FROM staffs s
        JOIN companies c ON s.company_id = c.company_id
        LEFT JOIN addresses addr
            ON addr.entity_id = c.company_id
            AND addr.entity_type = 'company'
            AND addr.is_primary = 1
        WHERE s.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function upsertEntityAddress($entityId, $entityType, array $addressData, $addressType = 'primary') {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT address_id
        FROM addresses
        WHERE entity_id = ? AND entity_type = ? AND is_primary = 1
        LIMIT 1
    ");
    $stmt->execute([$entityId, $entityType]);
    $addressId = $stmt->fetchColumn();

    $payload = [
        $addressType,
        $addressData['address_line'] ?? null,
        $addressData['city'] ?? null,
        $addressData['province'] ?? null,
        $addressData['postal_code'] ?? null,
        $addressData['country'] ?? 'Philippines'
    ];

    if ($addressId) {
        $update = $pdo->prepare("
            UPDATE addresses
            SET address_type = ?, address_line = ?, city = ?, province = ?, postal_code = ?, country = ?
            WHERE address_id = ?
        ");
        $update->execute([...$payload, $addressId]);
        return (int) $addressId;
    }

    $insert = $pdo->prepare("
        INSERT INTO addresses (entity_id, entity_type, address_type, address_line, city, province, postal_code, country, is_primary)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $insert->execute([$entityId, $entityType, ...$payload]);

    return (int) $pdo->lastInsertId();
}

function getCompanyStaffUserIds($companyId) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM staffs
        WHERE company_id = ? AND user_id IS NOT NULL
    ");
    $stmt->execute([$companyId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function notifyCompanyStaff($companyId, $message, $actionUrl = null, $actionLabel = null, $link = '', $relatedUserId = null) {
    global $pdo;

    $userIds = getCompanyStaffUserIds($companyId);
    if (!$userIds) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, action_url, action_label, related_user_id, link)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($userIds as $userId) {
        $stmt->execute([$userId, $message, $actionUrl, $actionLabel, $relatedUserId, $link]);
    }

    return true;
}

function logAction($action, $description = null) {
    global $pdo;

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO system_logs (user_id, action, description)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$_SESSION['user_id'], $action, $description]);
}
?>
