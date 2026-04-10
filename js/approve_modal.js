// approve_modal.js - Black and White Approve Modal

// ===== APPROVE INTERNSHIP MODAL =====
function openApproveInternshipModal(id, title) {
    document.getElementById('approveInternshipTitle').textContent = title;
    document.getElementById('confirmApproveInternshipBtn').href = 'approve_internship.php?id=' + id;
    document.getElementById('approveInternshipModal').classList.add('active');
}

function closeApproveInternshipModal() {
    document.getElementById('approveInternshipModal').classList.remove('active');
}

// Initialize events when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Close modal when clicking outside
    const approveModal = document.getElementById('approveInternshipModal');
    if (approveModal) {
        approveModal.addEventListener('click', function(event) {
            if (event.target === approveModal) {
                closeApproveInternshipModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('approveInternshipModal');
        if (event.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeApproveInternshipModal();
        }
    });
});