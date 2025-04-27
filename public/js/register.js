// Function to toggle password visibility
function togglePasswordVisibility(inputId, buttonElement) {
    // Get the input element
    const input = document.getElementById(inputId);
    // Get the icon element
    const icon = buttonElement.querySelector('i');

    // Check if input and icon are found
    if (!input || !icon) {
        return;
    }

    // Check if the type is password
    if (input.type === "password") {
        // Show password
        input.type = "text";
        // Update icon
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        buttonElement.setAttribute('aria-label', 'Ẩn mật khẩu');
    } else {
        // Hide password
        input.type = "password";
        // Update icon
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        buttonElement.setAttribute('aria-label', 'Hiện mật khẩu');
    }
}