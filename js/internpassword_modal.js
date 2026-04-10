// Password Change Confirmation Modal
let passwordForm = null;
let passwordButton = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing password modal');
    
    initializePasswordModal();
});

function initializePasswordModal() {
    // Get the password form
    passwordForm = document.querySelector('.password-form');
    
    if (!passwordForm) {
        console.log('Password form not found');
        return;
    }
    
    console.log('Password form found');
    setupPasswordForm();
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
    
    // Check for URL parameters for notifications
    checkForNotifications();
}

function setupPasswordForm() {
    // Get the password button
    passwordButton = passwordForm.querySelector('button[type="submit"]');
    
    if (passwordButton) {
        // Change button text
        passwordButton.textContent = 'Review Password';
        
        // Change button type to button to prevent default submission
        passwordButton.type = 'button';
        
        // Remove any existing event listeners
        const newPasswordButton = passwordButton.cloneNode(true);
        passwordButton.parentNode.replaceChild(newPasswordButton, passwordButton);
        passwordButton = newPasswordButton;
        
        passwordButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Review Password button clicked');
            
            if (validatePasswordForm()) {
                // Store the new password value to use in modal
                const newPassword = passwordForm.querySelector('input[name="new_password"]').value;
                sessionStorage.setItem('tempNewPassword', newPassword);
                openPasswordModal();
            }
        });
    }
}

// Validate password form before showing modal
function validatePasswordForm() {
    const currentPassword = passwordForm.querySelector('input[name="current_password"]');
    const newPassword = passwordForm.querySelector('input[name="new_password"]');
    
    // Reset any previous error styling
    [currentPassword, newPassword].forEach(field => {
        if (field) field.style.borderColor = '#000000';
    });
    
    // Check if all fields are filled
    if (!currentPassword.value.trim()) {
        showNotification('Current password is required', false);
        currentPassword.style.borderColor = '#ff0000';
        currentPassword.focus();
        return false;
    }
    
    if (!newPassword.value.trim()) {
        showNotification('New password is required', false);
        newPassword.style.borderColor = '#ff0000';
        newPassword.focus();
        return false;
    }
    
    // Password strength validation
    if (newPassword.value.length < 6) {
        showNotification('New password must be at least 6 characters long', false);
        newPassword.style.borderColor = '#ff0000';
        newPassword.focus();
        return false;
    }
    
    return true;
}

// Modal Functions
function openPasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (!modal) {
        console.log('Password modal not found');
        return;
    }
    
    // Clear previous confirm password field and error
    const confirmField = document.getElementById('confirm_new_password');
    const errorDiv = document.getElementById('passwordMatchError');
    
    if (confirmField) {
        confirmField.value = '';
        confirmField.style.borderColor = '#000000';
    }
    
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
    
    // Enable confirm button
    const confirmBtn = document.getElementById('confirmPasswordChange');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Yes, Change Password';
    }
    
    modal.style.display = 'block';
    
    // Focus on confirm password field
    setTimeout(() => {
        if (confirmField) confirmField.focus();
    }, 100);
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmPasswordChange');
    
    if (confirmBtn) {
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            console.log('Confirm password change clicked');
            
            const confirmField = document.getElementById('confirm_new_password');
            const newPasswordField = passwordForm.querySelector('input[name="new_password"]');
            const errorDiv = document.getElementById('passwordMatchError');
            
            // Get the stored new password
            const storedNewPassword = sessionStorage.getItem('tempNewPassword');
            
            // Check if passwords match
            if (newPasswordField.value !== confirmField.value) {
                errorDiv.style.display = 'block';
                confirmField.style.borderColor = '#ff0000';
                showNotification('Passwords do not match!', false);
                return;
            }
            
            // Show loading state
            this.textContent = 'Changing...';
            this.disabled = true;
            
            // Clear stored password
            sessionStorage.removeItem('tempNewPassword');
            
            // Submit the form
            passwordForm.submit();
            
            // Close modal
            closePasswordModal();
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const passwordModal = document.getElementById('passwordModal');
        if (event.target === passwordModal) {
            closePasswordModal();
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
    
    if (urlParams.has('password_success')) {
        showNotification('Password Changed Successfully');
        // Clean URL
        removeURLParameter('password_success');
    } else if (urlParams.has('password_error')) {
        showNotification('Current password is incorrect', false);
        removeURLParameter('password_error');
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
        const passwordModal = document.getElementById('passwordModal');
        if (passwordModal && passwordModal.style.display === 'block') {
            closePasswordModal();
        }
    }
});

// Make functions global
window.closePasswordModal = closePasswordModal;
window.showNotification = showNotification;