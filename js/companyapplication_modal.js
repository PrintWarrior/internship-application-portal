// Application Status Confirmation Modal
let selectedStatus = '';
let selectedButton = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing application modal');
    
    const modal = document.getElementById('applicationModal');
    const confirmBtn = document.getElementById('confirmAction');
    const notification = document.getElementById('notification');
    const form = document.querySelector('.status-update-form');
    
    if (!modal) {
        console.log('Modal not found');
        return;
    }
    
    // Get applicant and internship info for summary
    const applicantName = document.querySelector('.detail-row .detail-value')?.textContent.trim() || 'Unknown';
    const internshipTitle = document.querySelector('h4')?.textContent.trim() || 'Unknown';
    const currentStatus = document.querySelector('.status-badge')?.textContent.trim() || 'Unknown';
    
    // Store in data attributes
    modal.dataset.applicant = applicantName;
    modal.dataset.internship = internshipTitle;
    modal.dataset.currentStatus = currentStatus;
    
    console.log('Modal initialized with:', { applicantName, internshipTitle, currentStatus });
    
    // Attach click handlers to status buttons
    const statusButtons = document.querySelectorAll('.btn-status');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.disabled) {
                return;
            }
            
            selectedStatus = this.value;
            selectedButton = this;
            
            console.log('Status button clicked:', selectedStatus);
            
            // Update modal content
            document.getElementById('statusText').textContent = selectedStatus;
            document.getElementById('modalTitle').textContent = 
                selectedStatus === 'Offered' ? 'Offer Position' :
                selectedStatus === 'Accepted' ? 'Accept Application' :
                selectedStatus === 'Rejected' ? 'Reject Application' : 
                'Update Application Status';
            
            document.getElementById('modalMessage').innerHTML = 
                `Are you sure you want to mark this application as "<span style="font-weight: bold;">${selectedStatus}</span>"?`;
            
            // Update summary
            document.getElementById('summaryApplicant').textContent = applicantName;
            document.getElementById('summaryInternship').textContent = internshipTitle;
            document.getElementById('summaryCurrentStatus').textContent = currentStatus;
            
            const newStatusSpan = document.getElementById('summaryNewStatus');
            newStatusSpan.textContent = selectedStatus;
            newStatusSpan.setAttribute('data-status', selectedStatus);
            
            // Show modal
            modal.style.display = 'block';
        });
    });
    
    // Handle confirm button
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            console.log('Confirm button clicked for status:', selectedStatus);
            
            if (!selectedStatus || !selectedButton) {
                console.log('No status selected');
                return;
            }
            
            // Show loading state
            confirmBtn.textContent = 'Processing...';
            confirmBtn.disabled = true;
            
            // Create a hidden form to submit
            const hiddenForm = document.createElement('form');
            hiddenForm.method = 'POST';
            hiddenForm.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = selectedStatus;
            
            hiddenForm.appendChild(actionInput);
            document.body.appendChild(hiddenForm);
            
            // Submit the form
            hiddenForm.submit();
            
            // Close modal
            closeApplicationModal();
        });
    }
    
    // Handle cancel button
    window.closeApplicationModal = function() {
        modal.style.display = 'none';
        // Reset confirm button
        if (confirmBtn) {
            confirmBtn.textContent = 'Yes, Proceed';
            confirmBtn.disabled = false;
        }
        selectedStatus = '';
        selectedButton = null;
    };
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            closeApplicationModal();
        }
    };
    
    // Handle Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeApplicationModal();
        }
    });
    
    // Show notification function
    window.showNotification = function(message, isSuccess = true) {
        console.log('Showing notification:', message);
        
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
    };
    
    // Check for success parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        console.log('Success parameter found');
        
        // Determine message based on status change
        let successMessage = 'Application status updated successfully!';
        
        // You can customize message based on session or hidden field
        // For now, use generic message
        setTimeout(() => {
            showNotification(successMessage);
        }, 100);
        
        // Clean URL - remove the parameter but keep the id
        const newUrl = window.location.pathname + '?id=' + urlParams.get('id');
        window.history.replaceState({}, document.title, newUrl);
    }
});

// Also handle the existing success message div
document.addEventListener('DOMContentLoaded', function() {
    const successDiv = document.querySelector('.success-message');
    if (successDiv && successDiv.textContent.includes('successfully')) {
        // Hide the div and show notification instead
        const message = successDiv.textContent.trim();
        successDiv.style.display = 'none';
        showNotification(message);
    }
});