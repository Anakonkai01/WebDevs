function togglePasswordVisibility(inputId, iconElement) {
    // Get input and icon elements
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');
    // Check if elements exist
    if (!input || !icon) return;

    // Check input type
    if (input.type === "password") {
        // Show password
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        // Hide password
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}