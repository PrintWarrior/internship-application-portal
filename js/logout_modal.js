// logout-modal.js
function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}

function logoutUser() {
    window.location.href = "../logout.php";
}