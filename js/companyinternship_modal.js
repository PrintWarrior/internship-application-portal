// Post Internship Confirmation Modal
let internshipForm = null;
let postButton = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing internship modal');
    initializeInternshipModal();
});

function initializeInternshipModal() {
    // Get the internship form
    internshipForm = document.querySelector('.form-container form');
    
    if (internshipForm) {
        console.log('Form found');
        setupInternshipForm();
    } else {
        console.log('Form not found');
    }
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
}

function setupInternshipForm() {
    // Get the post button
    postButton = internshipForm.querySelector('button[type="submit"]');
    
    if (postButton) {
        console.log('Submit button found');
        
        // Change button text
        postButton.textContent = 'Review Internship';
        
        // Change button type to button to prevent default submission
        postButton.type = 'button';
        
        // Add click handler
        postButton.onclick = function(e) {
            e.preventDefault();
            console.log('Review button clicked');
            if (validateInternshipForm()) {
                console.log('Form valid, updating summary');
                updateInternshipSummary();
                openInternshipModal();
            }
        };
    }
}

// Update internship summary in modal
function updateInternshipSummary() {
    const title = internshipForm.querySelector('input[name="title"]').value;
    const duration = internshipForm.querySelector('input[name="duration"]').value || 'Not specified';
    const allowance = internshipForm.querySelector('input[name="allowance"]').value;
    const deadline = internshipForm.querySelector('input[name="deadline"]').value || 'Not specified';
    
    // Update summary elements
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryDuration = document.getElementById('summaryDuration');
    const summaryAllowance = document.getElementById('summaryAllowance');
    const summaryDeadline = document.getElementById('summaryDeadline');
    
    if (summaryTitle) summaryTitle.textContent = title;
    if (summaryDuration) summaryDuration.textContent = duration;
    if (summaryAllowance) {
        summaryAllowance.textContent = allowance ? '₱' + parseFloat(allowance).toFixed(2) : 'Not specified';
    }
    if (summaryDeadline) summaryDeadline.textContent = deadline;
}

// Validate internship form
function validateInternshipForm() {
    const title = internshipForm.querySelector('input[name="title"]');
    
    // Reset error styling
    title.style.borderColor = '#000000';
    
    // Check if title is filled
    if (!title.value.trim()) {
        showNotification('Internship title is required', false);
        title.style.borderColor = '#ff0000';
        title.focus();
        return false;
    }
    
    return true;
}

// Modal Functions
function openInternshipModal() {
    const modal = document.getElementById('internshipModal');
    if (modal) {
        console.log('Opening modal');
        modal.style.display = 'block';
    } else {
        console.log('Modal not found');
    }
}

function closeInternshipModal() {
    const modal = document.getElementById('internshipModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmInternship');
    
    if (confirmBtn) {
        console.log('Confirm button found');
        
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Confirm button clicked');
            
            // Show loading state
            this.textContent = 'Posting...';
            this.disabled = true;
            
            // Submit the form
            if (internshipForm) {
                // Change button type back to submit temporarily
                const originalButton = internshipForm.querySelector('button[type="button"]');
                if (originalButton) {
                    originalButton.type = 'submit';
                }
                // Submit the form
                internshipForm.submit();
            }
            
            // Close modal
            closeInternshipModal();
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const modal = document.getElementById('internshipModal');
        if (modal && event.target === modal) {
            closeInternshipModal();
        }
    };
}

// Show notification
function showNotification(message, isSuccess = true) {
    console.log('Showing notification:', message);
    const notification = document.getElementById('notification');
    
    if (!notification) {
        console.log('Notification element not found');
        return;
    }
    
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

// Handle Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('internshipModal');
        if (modal && modal.style.display === 'block') {
            closeInternshipModal();
        }
    }
});

// Make close function global
window.closeInternshipModal = closeInternshipModal;