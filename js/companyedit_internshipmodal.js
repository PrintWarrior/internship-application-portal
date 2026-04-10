// Edit Internship Confirmation Modal
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing edit internship modal');
    
    const form = document.querySelector('.form-container form');
    const modal = document.getElementById('editInternshipModal');
    const confirmBtn = document.getElementById('confirmEdit');
    const notification = document.getElementById('notification');
    
    if (!form) {
        console.log('Form not found');
        return;
    }
    
    if (!modal) {
        console.log('Modal not found');
        return;
    }
    
    console.log('Form and modal found');
    
    // Get the save button
    const saveBtn = form.querySelector('.btn-save');
    
    if (saveBtn) {
        // Handle save button click
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Save button clicked');
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Update summary with current form values
            updateSummary();
            
            // Show modal
            modal.style.display = 'block';
        });
    }
    
    // Validate form function
    function validateForm() {
        const title = form.querySelector('input[name="title"]');
        
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
    
    // Update summary with current form values
    function updateSummary() {
        const title = form.querySelector('input[name="title"]').value;
        const duration = form.querySelector('input[name="duration"]').value || 'Not specified';
        const allowance = form.querySelector('input[name="allowance"]').value;
        const deadline = form.querySelector('input[name="deadline"]').value || 'Not specified';
        
        document.getElementById('summaryTitle').textContent = title;
        document.getElementById('summaryDuration').textContent = duration;
        document.getElementById('summaryAllowance').textContent = 
            allowance ? '₱' + parseFloat(allowance).toFixed(2) : 'Not specified';
        document.getElementById('summaryDeadline').textContent = deadline;
    }
    
    // Handle confirm button
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            console.log('Confirm button clicked');
            
            // Show loading state
            confirmBtn.textContent = 'Saving...';
            confirmBtn.disabled = true;
            
            // Submit the form
            form.submit();
        });
    }
    
    // Handle cancel button
    window.closeEditModal = function() {
        modal.style.display = 'none';
        // Reset confirm button if needed
        if (confirmBtn) {
            confirmBtn.textContent = 'Yes, Save Changes';
            confirmBtn.disabled = false;
        }
    };
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            closeEditModal();
        }
    };
    
    // Handle Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeEditModal();
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
    if (urlParams.has('updated')) {
        console.log('Updated parameter found');
        setTimeout(() => {
            showNotification('Internship Updated Successfully');
        }, 100);
        
        // Clean URL - remove the parameter but keep the id
        const newUrl = window.location.pathname + '?id=' + urlParams.get('id');
        window.history.replaceState({}, document.title, newUrl);
    }
});

// Add real-time validation
document.addEventListener('input', function(e) {
    if (e.target.matches('input[name="title"]')) {
        e.target.style.borderColor = e.target.value.trim() ? '#00ff00' : '#ff0000';
    }
    if (e.target.matches('input[name="deadline"]')) {
        if (e.target.value) {
            const selectedDate = new Date(e.target.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            e.target.style.borderColor = selectedDate >= today ? '#00ff00' : '#ff0000';
        } else {
            e.target.style.borderColor = '#000000';
        }
    }
});