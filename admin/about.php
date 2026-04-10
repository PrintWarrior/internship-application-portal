<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get admin info from users table
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get or create admin profile
$stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$adminProfile = $stmt->fetch();

// If no profile exists, create one
if (!$adminProfile) {
    $insertStmt = $pdo->prepare("INSERT INTO admin_profiles (user_id, profile_image, full_name, title) VALUES (?, 'default.png', ?, ?)");
    $insertStmt->execute([$user_id, 'Administrator', 'System Administrator']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $adminProfile = $stmt->fetch();
}

// Get notification count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetchColumn();

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    
    $upload_dir = '../assets/img/profile/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $file = $_FILES['profile_image'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'admin_profile_' . $user_id . '_' . time() . '.' . $ext;
            $uploadPath = $upload_dir . $newName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if exists and not default
                $oldImage = $adminProfile['profile_image'] ?? '';
                if ($oldImage && $oldImage !== 'default.png' && file_exists($upload_dir . $oldImage)) {
                    unlink($upload_dir . $oldImage);
                }
                
                // Update database
                $updateStmt = $pdo->prepare("UPDATE admin_profiles SET profile_image = ? WHERE user_id = ?");
                $updateStmt->execute([$newName, $user_id]);
                
                // Refresh admin profile data
                $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $adminProfile = $stmt->fetch();
                
                header("Location: about.php?upload_success=1");
                exit;
            }
        }
    }
    header("Location: about.php?upload_error=1");
    exit;
}

// Handle image delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $upload_dir = '../assets/img/profile/';
    $currentImage = $adminProfile['profile_image'] ?? '';
    
    if ($currentImage && $currentImage !== 'default.png') {
        $filePath = $upload_dir . $currentImage;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    $updateStmt = $pdo->prepare("UPDATE admin_profiles SET profile_image = 'default.png' WHERE user_id = ?");
    $updateStmt->execute([$user_id]);
    
    // Refresh admin profile data
    $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $adminProfile = $stmt->fetch();
    
    header("Location: about.php?delete_success=1");
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'] ?? '';
    $title = $_POST['title'] ?? '';
    $location = $_POST['location'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $github = $_POST['github'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $twitter = $_POST['twitter'] ?? '';
    $portfolio = $_POST['portfolio'] ?? '';
    
    $updateStmt = $pdo->prepare("
        UPDATE admin_profiles 
        SET full_name = ?, title = ?, location = ?, bio = ?, 
            github = ?, linkedin = ?, twitter = ?, portfolio = ?
        WHERE user_id = ?
    ");
    $updateStmt->execute([$full_name, $title, $location, $bio, $github, $linkedin, $twitter, $portfolio, $user_id]);
    
    // Refresh admin profile data
    $stmt = $pdo->prepare("SELECT * FROM admin_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $adminProfile = $stmt->fetch();
    
    header("Location: about.php?profile_update_success=1");
    exit;
}

// Get current profile image
$profileImage = $adminProfile['profile_image'] ?? 'default.png';
$imagePath = "../assets/img/profile/" . $profileImage;
if (!file_exists($imagePath)) {
    $imagePath = "../assets/img/profile/default.png";
}

// Set default values if empty
$full_name = $adminProfile['full_name'] ?? 'John Michael Santos';
$title = $adminProfile['title'] ?? 'Full Stack Developer & System Architect';
$location = $adminProfile['location'] ?? 'Manila, Philippines';
$bio = $adminProfile['bio'] ?? 'I am a passionate Full Stack Developer with over 5 years of experience in building robust web applications. I specialize in creating scalable and efficient systems that solve real-world problems. This Internship Portal is one of my proudest projects, designed to bridge the gap between talented students and forward-thinking companies.';
$github = $adminProfile['github'] ?? '#';
$linkedin = $adminProfile['linkedin'] ?? '#';
$twitter = $adminProfile['twitter'] ?? '#';
$portfolio = $adminProfile['portfolio'] ?? '#';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin_about.css">
    <link rel="stylesheet" href="../assets/css/logout_modal.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <div class="topnav">
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo">
            <h2>Internship Portal - Admin</h2>
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
            <a href="profile.php">Profile</a>
            <a href="manage_interns.php">Manage Interns</a>
            <!--<a href="manage_companies.php">Manage Companies</a>-->
            <a href="manage_staffs.php">Manage Staffs</a>
            <a href="manage_internships.php">Manage Internships</a>
            <a href="applications.php">All Applications</a>
            <!--<a href="reports.php">Reports & Analytics</a>-->
            <a href="system_logs.php">System Logs</a>
            <a href="about.php">About</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="about-container">
                <!-- Header Section -->
                <div class="about-header">
                    <!--<h1>About The Developer</h1>
                    <div class="header-line"></div>-->
                </div>

                <!-- Profile Section with Image Upload/Delete -->
                <div class="profile-section">
                    <div class="profile-image-wrapper">
                        <img src="<?= $imagePath ?>" alt="Developer Profile Picture" class="profile-image" 
                             id="profileImage" onerror="this.src='../assets/img/profile/default.png'">
                        
                        <!-- Image Upload Form -->
                        <div class="image-actions">
                            <form method="POST" enctype="multipart/form-data" class="upload-form">
                                <!--<label for="profile_image" class="upload-label">Choose Image</label>
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display: none;">
                                <button type="submit" name="upload_image" class="upload-btn">Upload Image</button>-->
                            </form>
                            
                            <!--<form method="POST" class="delete-form">
                                <button type="submit" name="delete_image" class="delete-btn">Delete Image</button>
                            </form>-->
                        </div>
                    </div>
                    <div class="profile-info">
                        <h2>Xavier Ace Clark S. Azcona</h2>
                        <p>Developer</p>
                        <div class="info-details">
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <!--<span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>-->
                                <p>
                                    internshipapplicationportal@gmail.com
                                </p>

                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Location:</span>
                                <!--<span class="detail-value"><?= htmlspecialchars($location) ?></span>-->
                                <p>
                                    Oroquieta City, Misamis Occidental, Philippines
                                </p>
                            </div>
                            <!--<div class="detail-item">
                                <span class="detail-label">Role:</span>
                                <span class="detail-value">Admin</span>
                            </div>-->
                            <!--<div class="detail-item">
                                <span class="detail-label">Joined:</span>
                                <span class="detail-value"><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                            </div>-->
                            <h3>About The Developer</h3>
                            <p>
                                I am Xavier Ace Clark S. Azcona, I go to Northwestern Mindanao State College of Science and Technology, I am currently taking up Bachelor of Science in Information Technology.
                            </p>
                        </div>
                        
                        <!-- Edit Profile Button
                        <button class="edit-profile-btn" onclick="toggleEditForm()">Edit Profile</button>-->
                        
                        <!-- Edit Profile Form 
                        <div id="editProfileForm" class="edit-profile-form" style="display: none;">
                            <form method="POST">
                                <h4>Edit Profile Information</h4>
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" value="<?= htmlspecialchars($title) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" value="<?= htmlspecialchars($location) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Bio</label>
                                    <textarea name="bio" rows="4"><?= htmlspecialchars($bio) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>GitHub URL</label>
                                    <input type="url" name="github" value="<?= htmlspecialchars($github) ?>">
                                </div>
                                <div class="form-group">
                                    <label>LinkedIn URL</label>
                                    <input type="url" name="linkedin" value="<?= htmlspecialchars($linkedin) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Twitter URL</label>
                                    <input type="url" name="twitter" value="<?= htmlspecialchars($twitter) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Portfolio URL</label>
                                    <input type="url" name="portfolio" value="<?= htmlspecialchars($portfolio) ?>">
                                </div>
                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
                                    <button type="button" onclick="toggleEditForm()" class="cancel-btn">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>-->

                <!-- About Section 
                <div class="about-section">
                    <h3>About Me</h3>
                    <p><?= nl2br(htmlspecialchars($bio)) ?></p>
                </div>

                
                <div class="skills-section">
                    <h3>Technical Skills</h3>
                    <div class="skills-grid">
                        <div class="skill-item">
                            <span class="skill-name">PHP</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="skill-item">
                            <span class="skill-name">JavaScript</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="skill-item">
                            <span class="skill-name">HTML/CSS</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 95%"></div>
                            </div>
                        </div>
                        <div class="skill-item">
                            <span class="skill-name">MySQL</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 88%"></div>
                            </div>
                        </div>
                        <div class="skill-item">
                            <span class="skill-name">Python</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="skill-item">
                            <span class="skill-name">Laravel</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="experience-section">
                    <h3>Professional Experience</h3>
                    <div class="experience-item">
                        <div class="exp-header">
                            <span class="exp-title">Senior Full Stack Developer</span>
                            <span class="exp-date">2022 - Present</span>
                        </div>
                        <div class="exp-company">Tech Solutions Inc., Makati City</div>
                        <p class="exp-description">Lead development of enterprise-level web applications, mentor junior developers, and architect scalable database solutions.</p>
                    </div>
                    <div class="experience-item">
                        <div class="exp-header">
                            <span class="exp-title">Web Developer</span>
                            <span class="exp-date">2019 - 2022</span>
                        </div>
                        <div class="exp-company">Digital Innovations Corp., Quezon City</div>
                        <p class="exp-description">Developed and maintained multiple client websites, implemented responsive designs, and optimized database queries for improved performance.</p>
                    </div>
                    <div class="experience-item">
                        <div class="exp-header">
                            <span class="exp-title">Junior Developer</span>
                            <span class="exp-date">2017 - 2019</span>
                        </div>
                        <div class="exp-company">StartUp Hub, BGC, Taguig</div>
                        <p class="exp-description">Assisted in developing web applications, bug fixing, and providing technical support for various projects.</p>
                    </div>
                </div>

                
                <div class="education-section">
                    <h3>Education</h3>
                    <div class="education-item">
                        <div class="edu-header">
                            <span class="edu-degree">Master of Science in Computer Science</span>
                            <span class="edu-date">2020 - 2022</span>
                        </div>
                        <div class="edu-school">University of the Philippines, Diliman</div>
                    </div>
                    <div class="education-item">
                        <div class="edu-header">
                            <span class="edu-degree">Bachelor of Science in Information Technology</span>
                            <span class="edu-date">2013 - 2017</span>
                        </div>
                        <div class="edu-school">Mapúa University, Manila</div>
                    </div>
                </div>

                
                <div class="projects-section">
                    <h3>Key Projects</h3>
                    <div class="projects-grid">
                        <div class="project-item">
                            <h4>Internship Portal System</h4>
                            <p>A comprehensive platform connecting students with internship opportunities, featuring application management, contract generation, and notification systems.</p>
                        </div>
                        <div class="project-item">
                            <h4>E-Commerce Platform</h4>
                            <p>Built a fully functional e-commerce website with payment integration, inventory management, and user authentication.</p>
                        </div>
                        <div class="project-item">
                            <h4>School Management System</h4>
                            <p>Developed a complete school management system including student records, grade management, and reporting features.</p>
                        </div>
                    </div>
                </div>

                
                <div class="contact-section">
                    <h3>Let's Connect</h3>
                    <div class="contact-links">
                        <a href="<?= $github ?>" class="contact-link" target="_blank">GitHub</a>
                        <a href="<?= $linkedin ?>" class="contact-link" target="_blank">LinkedIn</a>
                        <a href="<?= $twitter ?>" class="contact-link" target="_blank">Twitter</a>
                        <a href="<?= $portfolio ?>" class="contact-link" target="_blank">Portfolio</a>
                    </div>
                    <p class="contact-note">Feel free to reach out for collaborations or opportunities!</p>
                </div>-->
            </div>
        </div>
    </div>

    <!-- Include Logout Modal -->
    <?php include '../html/logout_modal.html'; ?>
    <script src="../js/logout_modal.js"></script>
    
    <script>
        // Toggle edit profile form
        function toggleEditForm() {
            const form = document.getElementById('editProfileForm');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
        
        // File input trigger
        document.querySelector('.upload-label')?.addEventListener('click', function() {
            document.getElementById('profile_image').click();
        });
        
        // Preview image before upload
        document.getElementById('profile_image')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('profileImage');
                    if (img) {
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Show notification function
        function showNotification(message, isSuccess = true) {
            let notification = document.getElementById('notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'notification';
                notification.className = 'notification';
                document.body.appendChild(notification);
            }
            notification.textContent = message;
            notification.style.display = 'block';
            notification.style.backgroundColor = isSuccess ? '#000000' : '#ffffff';
            notification.style.color = isSuccess ? '#ffffff' : '#000000';
            notification.style.border = '2px solid ' + (isSuccess ? '#ffffff' : '#000000');
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
        
        // Check for success messages
        <?php if (isset($_GET['upload_success'])): ?>
            showNotification('Image uploaded successfully!');
        <?php elseif (isset($_GET['upload_error'])): ?>
            showNotification('Upload failed. Please try again.', false);
        <?php elseif (isset($_GET['delete_success'])): ?>
            showNotification('Image deleted successfully!');
        <?php elseif (isset($_GET['profile_update_success'])): ?>
            showNotification('Profile updated successfully!');
        <?php endif; ?>
    </script>

</body>
</html>