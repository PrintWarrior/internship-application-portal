<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireStaffUser();

$user_id = $_SESSION['user_id'];
$company = getStaffCompanyContext($user_id);

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    
    $staff_id = intval($_POST['staff_id']);
    $field = $_POST['field'];
    $value = trim($_POST['value']);
    
    // Allowed fields to update from the staff profile page
    $allowed_fields = ['first_name', 'last_name', 'email', 'contact_no', 'position'];
    
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Field cannot be updated']);
        exit;
    }
    
    // Validate staff belongs to this company
    $checkStmt = $pdo->prepare("
        SELECT s.* FROM staffs s
        WHERE s.company_id = ? AND s.staff_id = ?
    ");
    $checkStmt->execute([$company['company_id'], $staff_id]);
    $staff = $checkStmt->fetch();
    
    if (!$staff) {
        echo json_encode(['success' => false, 'message' => 'Staff not found']);
        exit;
    }
    
    // Update the field
    $updateStmt = $pdo->prepare("UPDATE staffs SET $field = ? WHERE staff_id = ?");
    $updateStmt->execute([$value, $staff_id]);
    
    echo json_encode(['success' => true, 'message' => 'Updated successfully']);
    exit;
}

// Get company + staff members
$stmt = $pdo->prepare("
    SELECT s.*
    FROM staffs s
    WHERE s.company_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$company['company_id']]);
$staffs = $stmt->fetchAll();

// Notifications
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

function formatStaffDate(?string $date): string
{
    if (empty($date)) {
        return 'Not available';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'Not available';
    }

    return date('M d, Y', $timestamp);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Profile - Company</title>
    <link rel="stylesheet" href="../assets/css/staff_profile.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
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
        <a href="staff_profile.php" class="active">Staff Profile</a>
        <a href="post_internship.php">Post Internship</a>
        <a href="manage_internships.php">My Internships</a>
        <a href="view_applicants.php">View Applicants</a>
        <a href="contracts.php">Contracts</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="content">
        <h2>Staff Profile</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="staff-cards">
            <?php if (count($staffs) > 0): ?>
                <?php foreach ($staffs as $staff): ?>
                    <div class="staff-card" data-staff-id="<?= $staff['staff_id'] ?>">
                        <div class="staff-card-header">
                            <?php
                            $imagePath = "../assets/img/profile/" . ($staff['profile_image'] ?? 'default.png');
                            if (!file_exists($imagePath)) {
                                $imagePath = "../assets/img/profile/default.png";
                            }
                            ?>
                            <img src="<?= $imagePath ?>" alt="Staff Profile" class="profile-image">
                        </div>
                        
                        <div class="staff-card-body">
                            <?php
                            $fullName = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
                            if ($fullName === '') {
                                $fullName = 'Unnamed Staff';
                            }
                            ?>
                            <div class="staff-name">
                                <?= htmlspecialchars($fullName) ?>
                            </div>
                            
                            <!-- PROFILE PHOTO SECTION -->
                            <div class="card-section">
                                <div class="section-title">Profile Photo</div>
                                
                                <form method="POST" enctype="multipart/form-data" 
                                      action="upload_staff_image.php"
                                      class="image-upload-form">
                                    <input type="hidden" name="staff_id" value="<?= $staff['staff_id'] ?>">
                                    <input type="file" name="profile_image" accept="image/*" required>
                                    <button type="submit">Upload Photo</button>
                                </form>
                                
                                <form method="POST" action="delete_staff_image.php"
                                      class="image-upload-form">
                                    <input type="hidden" name="staff_id" value="<?= $staff['staff_id'] ?>">
                                    <button type="submit" class="btn-delete-image">Delete Photo</button>
                                </form>
                            </div>
                            
                            <!-- EDITABLE INFORMATION SECTION -->
                            <div class="card-section">
                                <div class="section-title">Edit Information</div>
                                
                                <div class="info-row">
                                    <span class="info-label">Email Address</span>
                                    <div class="info-value editable-field" data-field="email" data-staff-id="<?= $staff['staff_id'] ?>">
                                        <?= htmlspecialchars($staff['email'] ?? 'Click to add email') ?>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Contact Number</span>
                                    <div class="info-value editable-field" data-field="contact_no" data-staff-id="<?= $staff['staff_id'] ?>">
                                        <?= htmlspecialchars($staff['contact_no'] ?? 'Click to add contact') ?>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Position</span>
                                    <div class="info-value editable-field" data-field="position" data-staff-id="<?= $staff['staff_id'] ?>">
                                        <?= htmlspecialchars($staff['position'] ?? 'Click to add position') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- STAFF DETAILS SECTION -->
                            <div class="card-section">
                                <div class="section-title">Details</div>
                                
                                <div class="info-row">
                                    <span class="info-label">First Name</span>
                                    <div class="info-value editable-field" data-field="first_name" data-staff-id="<?= $staff['staff_id'] ?>">
                                        <?= htmlspecialchars($staff['first_name'] ?: 'Click to add first name') ?>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Last Name</span>
                                    <div class="info-value editable-field" data-field="last_name" data-staff-id="<?= $staff['staff_id'] ?>">
                                        <?= htmlspecialchars($staff['last_name'] ?: 'Click to add last name') ?>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <span class="info-label">Joined Date</span>
                                    <div class="info-value readonly-field">
                                        <?= htmlspecialchars(formatStaffDate($staff['created_at'] ?? null)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No staff members added yet.</p>
                    <a href="add_staff.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Add Staff Member</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<div id="toast" class="toast"></div>

<?php include '../html/logout_modal.html'; ?>
<script src="../js/logout_modal.js"></script>

<script>
// Inline editing functionality
document.addEventListener('DOMContentLoaded', function() {
    const editableFields = document.querySelectorAll('.editable-field');
    
    editableFields.forEach(field => {
        field.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const currentValue = this.innerText;
            const fieldName = this.dataset.field;
            const staffId = this.dataset.staffId;
            const originalText = currentValue === 'Click to add first name' ||
                                 currentValue === 'Click to add last name' ||
                                 currentValue === 'Click to add email' || 
                                 currentValue === 'Click to add contact' || 
                                 currentValue === 'Click to add position' ? '' : currentValue;
            
            // Create input element
            const input = document.createElement('input');
            input.type = fieldName === 'email' ? 'email' : 'text';
            input.value = originalText;
            input.className = 'edit-input';
            
            // Replace content with input
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            // Handle save on blur or enter
            const saveValue = () => {
                const newValue = input.value.trim();
                
                if (newValue !== originalText) {
                    // Send AJAX request
                    const formData = new FormData();
                    formData.append('ajax_update', '1');
                    formData.append('staff_id', staffId);
                    formData.append('field', fieldName);
                    formData.append('value', newValue);
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.innerHTML = newValue || (fieldName === 'first_name' ? 'Click to add first name' :
                                                          fieldName === 'last_name' ? 'Click to add last name' :
                                                          fieldName === 'email' ? 'Click to add email' : 
                                                          fieldName === 'contact_no' ? 'Click to add contact' : 
                                                          'Click to add position');
                            showToast('Updated successfully!', 'success');
                        } else {
                            this.innerHTML = originalText || (fieldName === 'first_name' ? 'Click to add first name' :
                                                              fieldName === 'last_name' ? 'Click to add last name' :
                                                              fieldName === 'email' ? 'Click to add email' : 
                                                              fieldName === 'contact_no' ? 'Click to add contact' : 
                                                              'Click to add position');
                            showToast(data.message || 'Update failed', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.innerHTML = originalText || (fieldName === 'first_name' ? 'Click to add first name' :
                                                          fieldName === 'last_name' ? 'Click to add last name' :
                                                          fieldName === 'email' ? 'Click to add email' : 
                                                          fieldName === 'contact_no' ? 'Click to add contact' : 
                                                          'Click to add position');
                        showToast('Error updating', 'error');
                    });
                } else {
                    // Restore original text
                    this.innerHTML = originalText || (fieldName === 'first_name' ? 'Click to add first name' :
                                                      fieldName === 'last_name' ? 'Click to add last name' :
                                                      fieldName === 'email' ? 'Click to add email' : 
                                                      fieldName === 'contact_no' ? 'Click to add contact' : 
                                                      'Click to add position');
                }
            };
            
            input.addEventListener('blur', saveValue);
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveValue();
                }
            });
        });
    });
});

function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
    toast.className = 'toast show';
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}
</script>

</body>
</html>
