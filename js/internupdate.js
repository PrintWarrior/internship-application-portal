// Update Profile Information Confirmation Modal
let profileForm = null;
let updateButton = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing update profile modal');
    
    initializeUpdateModal();
});

function initializeUpdateModal() {
    // Get the profile form (the one with update_profile_info button)
    profileForm = document.querySelector('.form-container form:first-of-type');
    
    if (!profileForm) {
        console.log('Profile form not found');
        return;
    }
    
    console.log('Profile form found');
    setupProfileForm();
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
    
    // Check for URL parameters for notifications
    checkForNotifications();
}

function setupProfileForm() {
    // Get the update button
    updateButton = profileForm.querySelector('button[name="update_profile_info"]');
    
    if (updateButton) {
        // Change button text
        updateButton.textContent = 'Review Changes';
        
        // Change button type to button to prevent default submission
        updateButton.type = 'button';
        
        // Remove any existing event listeners
        const newUpdateButton = updateButton.cloneNode(true);
        updateButton.parentNode.replaceChild(newUpdateButton, updateButton);
        updateButton = newUpdateButton;
        
        updateButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Review Changes button clicked');
            
            // Validate form
            if (validateForm()) {
                updateProfileSummary();
                openUpdateModal();
            }
        });
    }
}

// Validate form
function validateForm() {
    const firstName = profileForm.querySelector('input[name="first_name"]');
    const lastName = profileForm.querySelector('input[name="last_name"]');
    const contactNo = profileForm.querySelector('input[name="contact_no"]');
    const university = profileForm.querySelector('input[name="university"]');
    const course = profileForm.querySelector('input[name="course"]');
    
    // Reset error styling
    [firstName, lastName, contactNo, university, course].forEach(field => {
        if (field) field.style.borderColor = '#000000';
    });
    
    // Validate required fields
    if (!firstName.value.trim()) {
        showNotification('First name is required', false);
        firstName.style.borderColor = '#ff0000';
        firstName.focus();
        return false;
    }
    
    if (!lastName.value.trim()) {
        showNotification('Last name is required', false);
        lastName.style.borderColor = '#ff0000';
        lastName.focus();
        return false;
    }
    
    if (!contactNo.value.trim()) {
        showNotification('Contact number is required', false);
        contactNo.style.borderColor = '#ff0000';
        contactNo.focus();
        return false;
    }
    
    if (!university.value.trim()) {
        showNotification('University is required', false);
        university.style.borderColor = '#ff0000';
        university.focus();
        return false;
    }
    
    if (!course.value.trim()) {
        showNotification('Course is required', false);
        course.style.borderColor = '#ff0000';
        course.focus();
        return false;
    }
    
    return true;
}

// Update profile summary in modal
function updateProfileSummary() {
    const firstName = profileForm.querySelector('input[name="first_name"]').value;
    const middleName = profileForm.querySelector('input[name="middle_name"]').value;
    const lastName = profileForm.querySelector('input[name="last_name"]').value;
    const contactNo = profileForm.querySelector('input[name="contact_no"]').value;
    const university = profileForm.querySelector('input[name="university"]').value;
    const course = profileForm.querySelector('input[name="course"]').value;
    
    const fullName = `${firstName} ${middleName ? middleName + ' ' : ''}${lastName}`;
    
    const summaryName = document.getElementById('summaryName');
    const summaryContact = document.getElementById('summaryContact');
    const summaryUniversity = document.getElementById('summaryUniversity');
    const summaryCourse = document.getElementById('summaryCourse');
    
    if (summaryName) summaryName.textContent = fullName;
    if (summaryContact) summaryContact.textContent = contactNo;
    if (summaryUniversity) summaryUniversity.textContent = university;
    if (summaryCourse) summaryCourse.textContent = course;
}

// Modal Functions
function openUpdateModal() {
    const modal = document.getElementById('updateProfileModal');
    if (modal) {
        modal.style.display = 'block';
    } else {
        console.log('Update modal not found');
    }
}

function closeUpdateModal() {
    const modal = document.getElementById('updateProfileModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmUpdate');
    
    if (confirmBtn) {
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            console.log('Confirm update clicked');
            
            // Show loading state
            this.textContent = 'Updating...';
            this.disabled = true;
            
            // Change button back to submit type temporarily
            const originalButton = profileForm.querySelector('button[name="update_profile_info"]');
            if (originalButton) {
                originalButton.type = 'submit';
            }
            
            // Submit the form
            profileForm.submit();
            
            // Close modal
            closeUpdateModal();
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const updateModal = document.getElementById('updateProfileModal');
        if (updateModal && event.target === updateModal) {
            closeUpdateModal();
        }
    };
}

// Show notification
function showNotification(message, isSuccess = true) {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    notification.textContent = message;
    notification.style.display = 'block';
    
    if (isSuccess) {
        notification.style.backgroundColor = '#000000';
        notification.style.color = '#ffffff';
        notification.style.borderColor = '#ffffff';
    } else {
        notification.style.backgroundColor = '#ffffff';
        notification.style.color = '#000000';
        notification.style.borderColor = '#000000';
    }
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Check for URL parameters on page load
function checkForNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('profile_success')) {
        showNotification('Profile Updated Successfully');
        // Clean URL
        removeURLParameter('profile_success');
    } else if (urlParams.has('upload_success')) {
        showNotification('Upload Successfully');
        removeURLParameter('upload_success');
    } else if (urlParams.has('delete_success')) {
        showNotification('Delete Successfully');
        removeURLParameter('delete_success');
    }
}

// Remove URL parameter without page reload
function removeURLParameter(param) {
    const url = new URL(window.location.href);
    url.searchParams.delete(param);
    window.history.replaceState({}, document.title, url.toString());
}

// Handle keyboard events (Escape key to close modal)
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const updateModal = document.getElementById('updateProfileModal');
        if (updateModal && updateModal.style.display === 'block') {
            closeUpdateModal();
        }
    }
});

// Make functions global
window.closeUpdateModal = closeUpdateModal;
window.showNotification = showNotification;