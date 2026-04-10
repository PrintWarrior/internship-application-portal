// Profile Image Modals for Intern
let uploadForm = null;
let deleteForm = null;
let selectedFile = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing profile modals');
    
    initializeProfileModals();
});

function initializeProfileModals() {
    // Get the forms
    const forms = document.querySelectorAll('.profile-image-container form');
    
    if (forms.length >= 2) {
        uploadForm = forms[0]; // First form is upload
        deleteForm = forms[1]; // Second form is delete
        
        console.log('Forms found');
        setupUploadForm();
        setupDeleteForm();
    } else {
        console.log('Forms not found');
    }
    
    // Setup modal confirm buttons
    setupConfirmButtons();
    
    // Setup click outside to close
    setupClickOutside();
    
    // Check for URL parameters for notifications
    checkForNotifications();
}

function setupUploadForm() {
    const uploadButton = uploadForm.querySelector('button[type="submit"]');
    const fileInput = uploadForm.querySelector('input[type="file"]');
    
    if (uploadButton && fileInput) {
        // Change button text
        uploadButton.textContent = 'Select Image';
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                selectedFile = this.files[0];
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(selectedFile.type)) {
                    showNotification('Only JPG and PNG files are allowed', false);
                    this.value = '';
                    selectedFile = null;
                    return;
                }
                
                // Validate file size (2MB)
                if (selectedFile.size > 2 * 1024 * 1024) {
                    showNotification('File size must be less than 2MB', false);
                    this.value = '';
                    selectedFile = null;
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('previewImage');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                    }
                };
                reader.readAsDataURL(selectedFile);
                
                // Change button text and behavior
                uploadButton.textContent = 'Review Image';
                uploadButton.type = 'button';
                
                // Remove existing click handler
                const newUploadButton = uploadButton.cloneNode(true);
                uploadButton.parentNode.replaceChild(newUploadButton, uploadButton);
                
                newUploadButton.addEventListener('click', function() {
                    if (selectedFile) {
                        openUploadModal();
                    } else {
                        showNotification('Please select an image first', false);
                    }
                });
            }
        });
    }
}

function setupDeleteForm() {
    const deleteButton = deleteForm.querySelector('button[type="submit"]');
    
    if (deleteButton) {
        // Remove the onclick confirm
        deleteButton.removeAttribute('onclick');
        
        // Change button type to button
        deleteButton.type = 'button';
        
        deleteButton.addEventListener('click', function() {
            console.log('Delete button clicked');
            openDeleteModal();
        });
    }
}

// Upload Modal Functions
function openUploadModal() {
    if (!selectedFile) {
        showNotification('Please select an image first', false);
        return;
    }
    
    document.getElementById('uploadModal').style.display = 'block';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

// Delete Modal Functions
function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Setup confirm buttons
function setupConfirmButtons() {
    const confirmUploadBtn = document.getElementById('confirmUpload');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    if (confirmUploadBtn) {
        confirmUploadBtn.addEventListener('click', function() {
            console.log('Upload confirmed');
            
            // Show loading state
            this.textContent = 'Uploading...';
            this.disabled = true;
            
            // Create FormData
            const formData = new FormData();
            formData.append('profile_image', selectedFile);
            formData.append('update_profile_image', '1');
            
            // Submit via fetch
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url + '?upload_success=1';
                } else {
                    window.location.href = window.location.href + '?upload_success=1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Upload failed. Please try again.', false);
                closeUploadModal();
            });
        });
    }
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            console.log('Delete confirmed');
            
            // Show loading state
            this.textContent = 'Deleting...';
            this.disabled = true;
            
            // Create FormData
            const formData = new FormData();
            formData.append('delete_image', '1');
            
            // Submit via fetch
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url + '?delete_success=1';
                } else {
                    window.location.href = window.location.href + '?delete_success=1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Delete failed. Please try again.', false);
                closeDeleteModal();
            });
        });
    }
}

// Setup click outside to close
function setupClickOutside() {
    window.onclick = function(event) {
        const uploadModal = document.getElementById('uploadModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (event.target === uploadModal) {
            closeUploadModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
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
    
    if (urlParams.has('upload_success')) {
        showNotification('Upload Successfully');
        // Clean URL
        removeURLParameter('upload_success');
    } else if (urlParams.has('delete_success')) {
        showNotification('Delete Successfully');
        // Clean URL
        removeURLParameter('delete_success');
    } else if (urlParams.has('upload_error')) {
        showNotification('Upload Failed', false);
        removeURLParameter('upload_error');
    } else if (urlParams.has('delete_error')) {
        showNotification('Delete Failed', false);
        removeURLParameter('delete_error');
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
        const uploadModal = document.getElementById('uploadModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (uploadModal && uploadModal.style.display === 'block') {
            closeUploadModal();
        }
        if (deleteModal && deleteModal.style.display === 'block') {
            closeDeleteModal();
        }
    }
});

// Make functions global
window.closeUploadModal = closeUploadModal;
window.closeDeleteModal = closeDeleteModal;
window.showNotification = showNotification;