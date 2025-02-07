// Toggle forms
function showLoginForm() {
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('forgotPasswordForm').classList.remove('active');
    document.getElementById('resetPasswordForm').classList.remove('active');
}

function showForgotPassword() {
    document.getElementById('forgotPasswordForm').classList.add('active');
    document.getElementById('loginForm').classList.remove('active');
    document.getElementById('resetPasswordForm').classList.remove('active');
}

function sendResetLink() {
    const email = document.getElementById('email').value.trim();
    if (!email) {
        alert('Please enter your email address.');
        return;
    }
    alert(`Password reset link sent to ${email}.`);
    showLoginForm(); // Return to login form after sending the link
}

function resetPassword() {
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    if (!newPassword || !confirmPassword) {
        alert('Please enter both new password and confirmation.');
        return;
    }

    if (newPassword !== confirmPassword) {
        alert('Passwords do not match.');
        return;
    }

    alert('Password reset successful!');
    showLoginForm(); // Return to login form after resetting the password
}

// Default form on page load
document.addEventListener('DOMContentLoaded', () => {
    showLoginForm();
});

// Toggle Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}