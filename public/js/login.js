// Function to toggle password visibility
function togglePasswordVisibility(inputId, buttonElement) {
    const input = document.getElementById(inputId);
    const icon = buttonElement.querySelector('i'); // Get icon element

    if (!input || !icon) {
        console.error("Could not find input or icon for password toggle.");
        return;
    }
    // Check password type
    if (input.type === "password") {
        input.type = "text";
        // Change icon
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        buttonElement.setAttribute('aria-label', 'Ẩn mật khẩu'); // Change text
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
         buttonElement.setAttribute('aria-label', 'Hiện mật khẩu'); // Update accessibility label
    }
}
