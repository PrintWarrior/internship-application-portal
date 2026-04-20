// Black and White Modal JavaScript - bw_modal.js

// Store current IDs globally
let currentInternId = null;
let currentCompanyId = null;
let currentInternshipId = null;


// ===============================
// INTERN DELETE
// ===============================
function openDeleteModal(internId, firstName, lastName, email) {

    currentInternId = internId;
    currentCompanyId = null;
    currentInternshipId = null;

    document.getElementById('modalInternName').textContent = firstName + ' ' + lastName;
    document.getElementById('modalInternEmail').textContent = email;

    document.getElementById('deleteModal').style.display = 'flex';
}


// ===============================
// COMPANY DELETE
// ===============================
function openDeleteCompanyModal(companyId, companyName, email) {

    currentCompanyId = companyId;
    currentInternId = null;
    currentInternshipId = null;

    document.getElementById('modalInternName').textContent = companyName;
    document.getElementById('modalInternEmail').textContent = email;

    document.getElementById('deleteModal').style.display = 'flex';
}


// ===============================
// INTERNSHIP DELETE
// ===============================
function openDeleteInternshipModal(internshipId, title, companyName) {

    currentInternshipId = internshipId;
    currentInternId = null;
    currentCompanyId = null;

    document.getElementById('modalInternName').textContent = title;
    document.getElementById('modalInternEmail').textContent = companyName;

    document.getElementById('deleteModal').style.display = 'flex';
}


// ===============================
// CLOSE MODAL
// ===============================
function closeDeleteModal() {

    document.getElementById('deleteModal').style.display = 'none';

    currentInternId = null;
    currentCompanyId = null;
    currentInternshipId = null;

}


// ===============================
// INITIALIZE EVENTS
// ===============================
document.addEventListener('DOMContentLoaded', function() {

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const deleteForm = document.getElementById('deleteModalForm');
    const deleteInput = document.getElementById('deleteModalId');

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!deleteForm || !deleteInput) {
                return;
            }

            if (currentInternId) {
                deleteForm.action = 'delete_intern.php';
                deleteInput.value = currentInternId;
            } else if (currentCompanyId) {
                deleteForm.action = 'delete_company.php';
                deleteInput.value = currentCompanyId;
            } else if (currentInternshipId) {
                deleteForm.action = 'delete_internship.php';
                deleteInput.value = currentInternshipId;
            }
        });
    }


    // Close modal when clicking outside
    window.addEventListener('click', function(event) {

        const modal = document.getElementById('deleteModal');

        if (event.target === modal) {
            closeDeleteModal();
        }

    });


    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {

        const modal = document.getElementById('deleteModal');

        if (event.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeDeleteModal();
        }

    });

});
