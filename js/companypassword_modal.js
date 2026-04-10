// Password Change Confirmation Modal
let passwordForm = null;
let passwordButton = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePasswordModal();
});

function initializePasswordModal() {
    // Get the password form
    passwordForm = document.querySelector('form:has(button[name="change_password"])');
    
    if (passwordForm) {
        setupPasswordForm();
    }
    
    // Check for URL parameters for notifications
    checkForNotifications();
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup password match validation
    setupPasswordValidation();
    
    // Setup click outside to close
    setupClickOutside();
}

function setupPasswordForm() {
    // Get the password button
    passwordButton = passwordForm.querySelector('button[name="change_password"]');
    
    if (passwordButton) {
        // Store the original form submission
        passwordForm.onsubmit = function(e) {
            e.preventDefault(); // Prevent normal form submission
            if (validatePasswordForm()) {
                openPasswordModal();
            }
            return false;
        };
        
        // Change button type to button to prevent default submission
        passwordButton.type = 'button';
        passwordButton.onclick = function(e) {
            e.preventDefault();
            if (validatePasswordForm()) {
                openPasswordModal();
            }
        };
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
    
    // Password strength validation (optional)
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
        confirmBtn.textContent = 'Yes';
    }
    
    document.getElementById('passwordModal').style.display = 'block';
    
    // Focus on confirm password field
    setTimeout(() => {
        if (confirmField) confirmField.focus();
    }, 100);
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

// Setup password match validation
function setupPasswordValidation() {
    const confirmField = document.getElementById('confirm_new_password');
    const newPasswordField = passwordForm ? passwordForm.querySelector('input[name="new_password"]') : null;
    const errorDiv = document.getElementById('passwordMatchError');
    const confirmBtn = document.getElementById('confirmPasswordChange');
    
    if (confirmField && newPasswordField && errorDiv && confirmBtn) {
        confirmField.addEventListener('input', function() {
            const newPassword = newPasswordField.value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (newPassword !== confirmPassword) {
                    errorDiv.style.display = 'block';
                    this.style.borderColor = '#ff0000';
                    confirmBtn.disabled = true;
                } else {
                    errorDiv.style.display = 'none';
                    this.style.borderColor = '#00ff00';
                    confirmBtn.disabled = false;
                }
            } else {
                errorDiv.style.display = 'none';
                this.style.borderColor = '#000000';
                confirmBtn.disabled = true;
            }
        });
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmPasswordChange');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const confirmField = document.getElementById('confirm_new_password');
            const newPasswordField = passwordForm.querySelector('input[name="new_password"]');
            const errorDiv = document.getElementById('passwordMatchError');
            
            // Double-check passwords match
            if (newPasswordField.value !== confirmField.value) {
                errorDiv.style.display = 'block';
                confirmField.style.borderColor = '#ff0000';
                showNotification('Passwords do not match!', false);
                return;
            }
            
            // Show loading state
            this.textContent = 'Changing...';
            this.disabled = true;
            
            // Create FormData from the form
            const formData = new FormData(passwordForm);
            
            // Make sure change_password is set
            formData.set('change_password', '1');
            
            // Submit the form normally
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = window.location.href;
            tempForm.style.display = 'none';
            
            // Add all form data to temp form
            for (let pair of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = pair[0];
                input.value = pair[1];
                tempForm.appendChild(input);
            }
            
            // Add to body, submit, and remove
            document.body.appendChild(tempForm);
            tempForm.submit();
            
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
        // Clean URL by removing the parameter
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
        if (passwordModal.style.display === 'block') {
            closePasswordModal();
        }
    }
});