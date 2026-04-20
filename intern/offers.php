    <?php
    session_start();
    require_once '../includes/db.php';
    require_once '../includes/functions.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'intern') {
        header('Location: ../index.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get all offers
    $stmt = $pdo->prepare("
        SELECT a.*, i.title, i.allowance, c.company_name, i.description, i.deadline
        FROM applications a
        JOIN internships i ON a.internship_id = i.internship_id
        JOIN companies c ON i.company_id = c.company_id
        JOIN interns ir ON a.intern_id = ir.intern_id
        WHERE ir.user_id = ? AND a.status = 'Offered'
        ORDER BY a.date_applied DESC
    ");
    $stmt->execute([$user_id]);
    $offers = $stmt->fetchAll();

    // Handle offer response (accept/decline)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_action'])) {

        $application_id = $_POST['application_id'];
        $action = $_POST['offer_action'];

        if ($action === 'accept') {
            $newStatus = 'Accepted';
        } elseif ($action === 'decline') {
            $newStatus = 'Declined';
        }

        if (isset($newStatus)) {

            // Update application status
            $updateStmt = $pdo->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
            $updateStmt->execute([$newStatus, $application_id]);

            // Get company user and intern info
            $stmt = $pdo->prepare("
                SELECT i.company_id,
                    ir.first_name,
                    ir.last_name,
                    i.title,
                    c.company_name
                FROM applications a
                JOIN internships i ON a.internship_id = i.internship_id
                JOIN companies c ON i.company_id = c.company_id
                JOIN interns ir ON a.intern_id = ir.intern_id
                WHERE a.application_id = ?
            ");

            $stmt->execute([$application_id]);
            $data = $stmt->fetch();

            if ($data) {

                if ($newStatus === 'Accepted') {
                    $message = $data['first_name'] . " " . $data['last_name'] .
                        " accepted your internship offer for " . $data['title'] . ".";
                } else {
                    $message = $data['first_name'] . " " . $data['last_name'] .
                        " declined your internship offer for " . $data['title'] . ".";
                }

                notifyCompanyStaff($data['company_id'], $message, "applications.php", "View Applications");
            }
            
            // Redirect with success parameter
            $redirectParam = $newStatus === 'Accepted' ? 'accept_success' : 'decline_success';
            header("Location: offers.php?" . $redirectParam . "=1");
            exit;
        }
    }

    /* Count unread */
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE user_id=? AND is_read=0
    ");
    $stmt->execute([$user_id]);
    $unread = $stmt->fetchColumn();
    ?>

    <!DOCTYPE html>
    <html>

    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Offers</title>
        <link rel="stylesheet" href="../assets/css/intern_offer.css">
        <link rel="stylesheet" href="../assets/css/logout_modal.css">
        <link rel="stylesheet" href="../assets/css/internoffer_modal.css">
        <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
        <style>
            /* Notification badge in sidebar */
            .sidebar .badge {
                background-color: #ffffff;
                color: #000000;
                padding: 2px 6px;
                font-size: 11px;
                font-weight: bold;
                margin-left: 8px;
                border: 1px solid #ffffff;
            }
        </style>
        <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

    <body>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <h4>Intern Panel</h4>
            <a href="index.php">Dashboard</a>
            <a href="profile.php">My Profile</a>
            <a href="browse_internships.php">Browse Internships</a>
            <a href="my_applications.php">My Applications</a>
            <a href="offers.php">Offers</a>
            <a href="contracts.php">Contracts</a>
            <a href="notifications.php">Notifications <span class="badge"><?= $unread ?></span></a>
            <a href="#" onclick="openLogoutModal()">Logout</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <h2>Internship Offers</h2>

            <div class="table-container">
                <?php if (count($offers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Internship Position</th>
                                <th>Company</th>
                                <th>Offer Date</th>
                                <th>Allowance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offers as $index => $offer): ?>
                                <tr class="<?= $index === 0 ? 'new-offer' : '' ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($offer['title']) ?></strong>
                                        <?php if ($index === 0): ?>
                                            <span class="offer-badge">NEW</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($offer['company_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($offer['date_applied'])) ?></td>
                                    <td>PHP <?= number_format($offer['allowance'], 2) ?></td>
                                    <td>
                                        <div class="offer-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?= $offer['application_id'] ?>">
                                                <input type="hidden" name="offer_action" value="accept">
                                                <button type="submit" class="btn-offer btn-accept">Accept</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?= $offer['application_id'] ?>">
                                                <input type="hidden" name="offer_action" value="decline">
                                                <button type="submit" class="btn-offer btn-decline">Decline</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Additional info section -->
                    <div style="margin-top: 20px; padding: 15px; border: 2px solid #000000; background-color: #f5f5f5;">
                        <p style="font-size: 13px; text-transform: uppercase; font-weight: bold;">
                             You have <?= count($offers) ?> active offer(s). Accept or decline within the specified timeframe.
                        </p>
                    </div>

                <?php else: ?>
                    <div class="no-results">
                        No offers available at the moment.<br>
                        <a href="browse_internships.php">Browse Internships</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Include Logout Modal HTML -->
        <?php include '../html/logout_modal.html'; ?>
        
        <!-- Include Offer Modal HTML -->
        <?php include '../html/internoffer_modal.html'; ?>
        
        <!-- Include JavaScript files -->
        <script src="../js/logout_modal.js"></script>
        <script src="../js/internoffer_modal.js"></script>
        
        <!-- Check for success messages -->
        <?php if (isset($_GET['accept_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof showNotification === 'function') {
                        showNotification('Offer Accepted Successfully');
                    }
                }, 100);
            });
        </script>
        <?php elseif (isset($_GET['decline_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof showNotification === 'function') {
                        showNotification('Offer Declined Successfully');
                    }
                }, 100);
            });
        </script>
        <?php endif; ?>
        <script src="../js/responsive-nav.js"></script>
        
        
</body>

    </html>
