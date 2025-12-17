/**
 * Generic Form Validation Script
 * 
 * This script automatically attaches to all forms with the class 'needs-validation'.
 * It intercepts the submit event and checks for HTML5 validation constraints.
 * If invalid, it prevents submission and shows visual feedback.
 */

document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Add novalidate to prevent default browser validation bubbles
        // so we can style them ourselves if needed, or just use this script to manage focus.
        // However, for this requirement "Ensure forms have proper JS validation",
        // we will rely on the Constraint Validation API but intercept it.
        
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Find the first invalid field and focus it
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }

            form.classList.add('was-validated');
        }, false);
    });
});
