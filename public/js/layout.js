document.addEventListener('DOMContentLoaded', function() {
    var logoutLink = document.getElementById('logoutLink');
    var confirmLogoutModal = document.getElementById('confirmLogoutModal');
    if (logoutLink && confirmLogoutModal) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            var modal = new bootstrap.Modal(confirmLogoutModal);
            modal.show();
        });
    }
}); 