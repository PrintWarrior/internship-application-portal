// reject_modal.js - Black and White Reject Modal

// ===== REJECT INTERNSHIP MODAL =====
function openRejectInternshipModal(id, title) {
    document.getElementById('rejectInternshipTitle').textContent = title;
    document.getElementById('confirmRejectInternshipBtn').href = 'reject_internship.php?id=' + id;
    document.getElementById('rejectInternshipModal').classList.add('active');
}

function closeRejectInternshipModal() {
    document.getElementById('rejectInternshipModal').classList.remove('active');
}

// Initialize events when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Close modal when clicking outside
    const rejectModal = document.getElementById('rejectInternshipModal');
    if (rejectModal) {
        rejectModal.addEventListener('click', function(event) {
            if (event.target === rejectModal) {
                closeRejectInternshipModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('rejectInternshipModal');
        if (event.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeRejectInternshipModal();
        }
    });
});