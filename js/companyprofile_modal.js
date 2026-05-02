// Profile Image Modals and Notifications
let uploadForm = null;
let deleteForm = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeProfileModals();
});

function initializeProfileModals() {
    // Get the forms
    const forms = document.querySelectorAll('.profile-image-section form');
    if (forms.length >= 2) {
        uploadForm = forms[0]; // First form is upload
        deleteForm = forms[1]; // Second form is delete
        
        // Store original form data
        storeOriginalForms();
        
        // Create new buttons for modal triggers
        createModalTriggers();
    }
    
    // Check for URL parameters for notifications
    checkForNotifications();
    
    // Setup confirm buttons
    setupConfirmButtons();
}

function storeOriginalForms() {
    // Store the original forms' HTML for later use
    if (uploadForm) {
        uploadForm.dataset.originalHtml = uploadForm.innerHTML;
    }
    if (deleteForm) {
        deleteForm.dataset.originalHtml = deleteForm.innerHTML;
    }
}

function createModalTriggers() {
    const imageActions = document.querySelector('.image-actions');
    if (!imageActions) return;
    
    // Clear existing buttons but keep the forms (don't hide them completely)
    // Instead, we'll make them invisible but keep them functional
    
    // Style the original forms to be visually hidden but still functional
    if (uploadForm) {
        uploadForm.style.position = 'absolute';
        uploadForm.style.left = '-9999px';
        uploadForm.style.opacity = '0';
        uploadForm.style.height = '0';
        uploadForm.style.overflow = 'hidden';
    }
    
    if (deleteForm) {
        deleteForm.style.position = 'absolute';
        deleteForm.style.left = '-9999px';
        deleteForm.style.opacity = '0';
        deleteForm.style.height = '0';
        deleteForm.style.overflow = 'hidden';
    }
    
    // Create container for our custom UI
    const customUI = document.createElement('div');
    customUI.className = 'custom-image-actions';
    customUI.style.marginTop = '10px';
    
    // Create file input wrapper
    const fileInputWrapper = document.createElement('div');
    fileInputWrapper.className = 'file-input-wrapper';
    fileInputWrapper.style.marginBottom = '10px';
    
    // Create file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.id = 'modalFileInput';
    fileInput.name = 'profile_image';
    fileInput.accept = 'image/jpeg,image/png,image/jpg';
    
    // Create upload trigger button
    const uploadTrigger = document.createElement('button');
    uploadTrigger.type = 'button';
    uploadTrigger.textContent = 'Upload Image';
    uploadTrigger.onclick = openUploadModal;
    uploadTrigger.style.marginRight = '10px';
    
    // Create delete trigger button
    const deleteTrigger = document.createElement('button');
    deleteTrigger.type = 'button';
    deleteTrigger.textContent = 'Delete Image';
    deleteTrigger.onclick = openDeleteModal;
    
    // Assemble the elements
    fileInputWrapper.appendChild(fileInput);
    customUI.appendChild(fileInputWrapper);
    customUI.appendChild(uploadTrigger);
    customUI.appendChild(deleteTrigger);
    
    // Add to image actions
    imageActions.appendChild(customUI);
}

// Upload Modal Functions
function openUploadModal() {
    const fileInput = document.getElementById('modalFileInput');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('Please select an image first', false);
        return;
    }
    
    // Store the selected file in a data attribute for later use
    const modal = document.getElementById('uploadModal');
    modal.dataset.hasFile = 'true';
    
    // Create a File object reference
    window.selectedFile = fileInput.files[0];
    
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

// Confirm Actions
function setupConfirmButtons() {
    const confirmUploadBtn = document.getElementById('confirmUpload');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    if (confirmUploadBtn) {
        // Remove any existing event listeners
        confirmUploadBtn.replaceWith(confirmUploadBtn.cloneNode(true));
        const newConfirmUploadBtn = document.getElementById('confirmUpload');
        
        newConfirmUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the file input from our custom UI
            const fileInput = document.getElementById('modalFileInput');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                showNotification('Please select an image first', false);
                closeUploadModal();
                return;
            }
            
            // Create a new FormData object
            const formData = new FormData();
            formData.append('profile_image', fileInput.files[0]);
            formData.append('upload_image', '1');
            const uploadCsrfInput = uploadForm.querySelector('input[name="csrf_token"]');
            if (uploadCsrfInput && uploadCsrfInput.value) {
                formData.append('csrf_token', uploadCsrfInput.value);
            }
            
            // Create a fetch request to submit the form
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Upload failed', false);
                closeUploadModal();
            });
        });
    }
    
    if (confirmDeleteBtn) {
        // Remove any existing event listeners
        confirmDeleteBtn.replaceWith(confirmDeleteBtn.cloneNode(true));
        const newConfirmDeleteBtn = document.getElementById('confirmDelete');
        
        newConfirmDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Create a new FormData object
            const formData = new FormData();
            formData.append('delete_image', '1');
            const deleteCsrfInput = deleteForm.querySelector('input[name="csrf_token"]');
            if (deleteCsrfInput && deleteCsrfInput.value) {
                formData.append('csrf_token', deleteCsrfInput.value);
            }
            
            // Create a fetch request to submit the form
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Delete failed', false);
                closeDeleteModal();
            });
        });
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const uploadModal = document.getElementById('uploadModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === uploadModal) {
        closeUploadModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}

// Notification function
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
