// Offer Acceptance/Decline Confirmation Modal
let currentForm = null;
let currentAction = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing offer modal');
    
    initializeOfferModals();
});

function initializeOfferModals() {
    // Get all offer forms
    const offerForms = document.querySelectorAll('.offer-actions form');
    
    if (offerForms.length === 0) {
        console.log('No offer forms found');
        return;
    }
    
    console.log(`Found ${offerForms.length} offer forms`);
    
    // Setup each offer form
    offerForms.forEach(form => {
        setupOfferForm(form);
    });
    
    // Setup confirm button
    setupConfirmButton();
    
    // Setup click outside to close
    setupClickOutside();
    
    // Check for URL parameters for notifications
    checkForNotifications();
}

function setupOfferForm(form) {
    // Get the button in this form
    const button = form.querySelector('button');
    const action = form.querySelector('input[name="offer_action"]')?.value;
    
    if (button && action) {
        // Get the row containing this form
        const row = button.closest('tr');
        if (row) {
            // Get all cells in the row
            const cells = row.querySelectorAll('td');
            
            // Cell indices: 0 = Title, 1 = Company, 2 = Offer Date, 3 = Allowance, 4 = Actions
            const title = cells[0]?.textContent.trim().replace('NEW', '').trim() || 'Unknown';
            const company = cells[1]?.textContent.trim() || 'Unknown';
            const date = cells[2]?.textContent.trim() || 'Unknown';
            const allowance = cells[3]?.textContent.trim() || 'Not specified';
            
            console.log('Offer details:', { title, company, date, allowance, action });
            
            // Store data in form
            form.dataset.title = title;
            form.dataset.company = company;
            form.dataset.date = date;
            form.dataset.allowance = allowance;
            form.dataset.action = action;
        }
        
        // Store the application_id for this form
        const applicationId = form.querySelector('input[name="application_id"]')?.value;
        if (applicationId) {
            form.dataset.applicationId = applicationId;
        }
        
        // Change button type to button to prevent default submission
        button.type = 'button';
        
        // Remove any existing event listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const action = form.dataset.action;
            console.log(`${action} button clicked for:`, form.dataset.title);
            
            // Store the form reference for confirmation
            currentForm = form;
            currentAction = action;
            
            // Update modal with offer details
            updateModalDetails(form);
            
            // Open modal
            openOfferModal();
        });
    }
}

// Update modal with offer details
function updateModalDetails(form) {
    const title = form.dataset.title || 'Unknown';
    const company = form.dataset.company || 'Unknown';
    const date = form.dataset.date || 'Unknown';
    const allowance = form.dataset.allowance || 'Not specified';
    const action = form.dataset.action;
    
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const summaryTitle = document.getElementById('summaryTitle');
    const summaryCompany = document.getElementById('summaryCompany');
    const summaryDate = document.getElementById('summaryDate');
    const summaryAllowance = document.getElementById('summaryAllowance');
    const confirmBtn = document.getElementById('confirmOffer');
    
    console.log('Updating modal with:', { title, company, date, allowance, action });
    
    if (action === 'accept') {
        if (modalTitle) modalTitle.textContent = 'Accept Offer';
        if (modalMessage) modalMessage.textContent = 'Are you sure you want to accept this internship offer?';
        if (confirmBtn) confirmBtn.textContent = 'Yes, Accept Offer';
    } else {
        if (modalTitle) modalTitle.textContent = 'Decline Offer';
        if (modalMessage) modalMessage.textContent = 'Are you sure you want to decline this internship offer?';
        if (confirmBtn) confirmBtn.textContent = 'Yes, Decline Offer';
    }
    
    if (summaryTitle) summaryTitle.textContent = title;
    if (summaryCompany) summaryCompany.textContent = company;
    if (summaryDate) summaryDate.textContent = date;
    if (summaryAllowance) summaryAllowance.textContent = allowance;
}

// Modal Functions
function openOfferModal() {
    const modal = document.getElementById('offerModal');
    if (modal) {
        modal.style.display = 'block';
    } else {
        console.log('Offer modal not found');
    }
}

function closeOfferModal() {
    const modal = document.getElementById('offerModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Setup confirm button
function setupConfirmButton() {
    const confirmBtn = document.getElementById('confirmOffer');
    
    if (confirmBtn) {
        // Remove any existing event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            console.log('Confirm button clicked for action:', currentAction);
            
            if (!currentForm) {
                console.log('No form found');
                return;
            }
            
            // Show loading state
            this.textContent = currentAction === 'accept' ? 'Accepting...' : 'Declining...';
            this.disabled = true;
            
            // Create a hidden input to ensure the action is sent
            let actionInput = currentForm.querySelector('input[name="offer_action"]');
            if (actionInput) {
                actionInput.value = currentAction;
            }
            
            // Submit the form
            console.log('Submitting form for application ID:', currentForm.dataset.applicationId);
            currentForm.submit();
            
            // Close modal
            closeOfferModal();
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const offerModal = document.getElementById('offerModal');
        if (offerModal && event.target === offerModal) {
            closeOfferModal();
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
    
    if (urlParams.has('accept_success')) {
        showNotification('Offer Accepted Successfully');
        // Clean URL
        removeURLParameter('accept_success');
    } else if (urlParams.has('decline_success')) {
        showNotification('Offer Declined Successfully');
        removeURLParameter('decline_success');
    } else if (urlParams.has('error')) {
        showNotification('An error occurred. Please try again.', false);
        removeURLParameter('error');
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
        const offerModal = document.getElementById('offerModal');
        if (offerModal && offerModal.style.display === 'block') {
            closeOfferModal();
        }
    }
});

// Make functions global
window.closeOfferModal = closeOfferModal;
window.showNotification = showNotification;