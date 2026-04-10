// Apply for Internship Confirmation Modal
let currentForm = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing apply modal');
    
    initializeApplyModals();
});

function initializeApplyModals() {
    // Get all apply forms (not just buttons)
    const applyForms = document.querySelectorAll('form');
    
    if (applyForms.length === 0) {
        console.log('No apply forms found');
        return;
    }
    
    console.log(`Found ${applyForms.length} apply forms`);
    
    // Setup each apply form
    applyForms.forEach(form => {
        setupApplyForm(form);
    });
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
    
    // Check for URL parameters for notifications
    checkForNotifications();
}

function setupApplyForm(form) {
    // Get the apply button in this form
    const applyButton = form.querySelector('button[name="apply"]');
    
    if (applyButton) {
        // Store internship details for this form
        const row = applyButton.closest('tr');
        if (row) {
            const title = row.querySelector('td:first-child strong')?.textContent.trim() || 'Unknown';
            const company = row.querySelector('td:nth-child(2)')?.textContent.trim() || 'Unknown';
            const deadline = row.querySelector('td:nth-child(3)')?.textContent.trim() || 'Unknown';
            
            // Store data in form
            form.dataset.title = title;
            form.dataset.company = company;
            form.dataset.deadline = deadline;
        }
        
        // Store the internship_id for this form
        const internshipId = form.querySelector('input[name="internship_id"]')?.value;
        if (internshipId) {
            form.dataset.internshipId = internshipId;
        }
        
        // Change button type to button to prevent default submission
        applyButton.type = 'button';
        
        // Remove any existing event listeners
        const newButton = applyButton.cloneNode(true);
        applyButton.parentNode.replaceChild(newButton, applyButton);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Apply button clicked for:', form.dataset.title);
            
            // Store the form reference for confirmation
            currentForm = form;
            
            // Update modal with internship details
            updateModalDetails(form);
            
            // Open modal
            openApplyModal();
        });
    }
}

// Update modal with internship details
function updateModalDetails(form) {
    const title = form.dataset.title || 'Unknown';
    const company = form.dataset.company || 'Unknown';
    const deadline = form.dataset.deadline || 'Unknown';
    
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryCompany = document.getElementById('summaryCompany');
    const summaryDeadline = document.getElementById('summaryDeadline');
    
    if (summaryTitle) summaryTitle.textContent = title;
    if (summaryCompany) summaryCompany.textContent = company;
    if (summaryDeadline) summaryDeadline.textContent = deadline;
}

// Modal Functions
function openApplyModal() {
    const modal = document.getElementById('applyModal');
    if (modal) {
        modal.style.display = 'block';
    } else {
        console.log('Apply modal not found');
    }
}

function closeApplyModal() {
    const modal = document.getElementById('applyModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmApply');
    
    if (confirmBtn) {
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            console.log('Confirm apply clicked');
            
            if (!currentForm) {
                console.log('No form found');
                return;
            }
            
            // Show loading state
            this.textContent = 'Applying...';
            this.disabled = true;
            
            // Create a hidden input to ensure the apply parameter is sent
            // The form already has the button with name="apply", but since we changed it to button type,
            // we need to add a hidden input
            let hiddenInput = currentForm.querySelector('input[name="apply_submit"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'apply';
                hiddenInput.value = '1';
                currentForm.appendChild(hiddenInput);
            }
            
            // Submit the form
            console.log('Submitting form for internship ID:', currentForm.dataset.internshipId);
            currentForm.submit();
            
            // Close modal
            closeApplyModal();
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const applyModal = document.getElementById('applyModal');
        if (applyModal && event.target === applyModal) {
            closeApplyModal();
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
    
    if (urlParams.has('apply_success')) {
        showNotification('Application Submitted Successfully');
        // Clean URL
        removeURLParameter('apply_success');
    } else if (urlParams.has('already_applied')) {
        showNotification('You have already applied for this internship', false);
        removeURLParameter('already_applied');
    } else if (urlParams.has('apply_error')) {
        showNotification('Failed to submit application', false);
        removeURLParameter('apply_error');
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
        const applyModal = document.getElementById('applyModal');
        if (applyModal && applyModal.style.display === 'block') {
            closeApplyModal();
        }
    }
});

// Make functions global
window.closeApplyModal = closeApplyModal;
window.showNotification = showNotification;