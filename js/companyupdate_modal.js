// Company Update Profile Confirmation Modal
let updateForm = null;
let updateButton = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeUpdateModal();
});

function initializeUpdateModal() {
    // Get the update form - the one with update_profile button
    updateForm = document.querySelector('button[name="update_profile"]').closest('form');
    
    if (!updateForm) {
    console.error("Update form not found!");
    return;
}
    
    // Check for URL parameters for notifications
    checkForNotifications();
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
}

function setupUpdateForm() {
    // Get the update button
    updateButton = updateForm.querySelector('button[name="update_profile"]');
    
    if (updateButton) {
        // Store the original form submission
        updateForm.onsubmit = function(e) {
            e.preventDefault(); // Prevent normal form submission
            if (validateForm()) {
                openUpdateModal();
            }
            return false;
        };
        
        // Change button type to button to prevent default submission
        updateButton.type = 'button';
        updateButton.onclick = function(e) {
            e.preventDefault();
            if (validateForm()) {
                openUpdateModal();
            }
        };
    }
}

// Modal Functions
function openUpdateModal() {
    document.getElementById('updateProfileModal').style.display = 'block';
}

function closeUpdateModal() {
    document.getElementById('updateProfileModal').style.display = 'none';
}

// Validate form
function validateForm() {
    const companyName = updateForm.querySelector('input[name="company_name"]');
    
    if (!companyName.value.trim()) {
        showNotification('Company Name is required', false);
        companyName.focus();
        return false;
    }
    
    return true;
}

// Setup confirm button
function setupConfirmButton() {
    const confirmUpdateBtn = document.getElementById('confirmUpdate');

    if (!confirmUpdateBtn) {
        console.error("Confirm button not found!");
        return;
    }

    confirmUpdateBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (!updateForm) {
            console.error("Update form not found!");
            return;
        }

        const formData = new FormData(updateForm);

        formData.set('update_profile', '1');

        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = window.location.href;

        for (let pair of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            tempForm.appendChild(input);
        }

        document.body.appendChild(tempForm);
        tempForm.submit();
    });
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const updateModal = document.getElementById('updateProfileModal');
        
        if (event.target === updateModal) {
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
    
    if (urlParams.has('update_success')) {
        showNotification('Company Information Updated Successfully');
        // Clean URL by removing the parameter
        removeURLParameter('update_success');
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
        if (updateModal.style.display === 'block') {
            closeUpdateModal();
        }
    }
});