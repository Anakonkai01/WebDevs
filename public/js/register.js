// public/js/register.js

/**
 * Toggle password visibility for an input field.
 * @param {string} inputId The ID of the password input field.
 * @param {HTMLElement} buttonElement The button element that was clicked.
 */
function togglePasswordVisibility(inputId, buttonElement) {
    const input = document.getElementById(inputId);
    const icon = buttonElement.querySelector('i'); // Find the icon inside the button

    if (!input || !icon) {
        console.error("Could not find input or icon for password toggle.");
        return;
    }

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        buttonElement.setAttribute('aria-label', 'Ẩn mật khẩu'); // Update accessibility label
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
         buttonElement.setAttribute('aria-label', 'Hiện mật khẩu'); // Update accessibility label
    }
}

// Optional: Client-side validation can be added here if desired,
// but server-side validation is essential.