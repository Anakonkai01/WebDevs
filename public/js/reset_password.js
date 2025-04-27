// Function to toggle password visibility
function togglePasswordVisibility(inputId, iconElement) {
    // Get the input and icon elements
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');
    // Check if elements exist
    if (!input || !icon) return;

    // Change input type
    if (input.type === "password") {
        input.type = "text";
        // Change eye icon
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
         icon.classList.replace('fa-eye-slash', 'fa-eye'); // use replace instead of remove and add
    }
}