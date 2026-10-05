// Browser checks help the user. PHP repeats validation because JS can be bypassed.
document.querySelectorAll('form').forEach((form) => {
    function validateForm() {
        form.querySelectorAll('[data-trim-required]').forEach((field) => {
            field.setCustomValidity(field.value.trim() ? '' : 'Please enter text, not just spaces.');
        });
        const password = form.querySelector('[data-new-password]');
        const confirmation = form.querySelector('[data-confirm-password]');
        if (password) {
            const valid = new TextEncoder().encode(password.value).length <= 72 && !password.value.includes('\0');
            password.setCustomValidity(valid ? '' : 'Use at most 72 bytes and no null characters.');
        }
        if (confirmation) {
            confirmation.setCustomValidity(confirmation.value === password.value ? '' : 'Passwords must match.');
        }
    }
    form.addEventListener('input', validateForm);
    form.addEventListener('submit', (event) => {
        validateForm();
        if (!form.reportValidity()) event.preventDefault();
    });
});
