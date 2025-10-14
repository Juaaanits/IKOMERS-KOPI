document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (!passwordInput || !togglePassword) {
        return;
    }

    const icons = {
        show: `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
               </svg>`,
        hide: `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <line x1="5" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
               </svg>`
    };

    const updateToggle = (isVisible) => {
        togglePassword.setAttribute('aria-pressed', String(isVisible));
        togglePassword.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        togglePassword.innerHTML = isVisible ? icons.hide : icons.show;
    };

    updateToggle(false);

    togglePassword.addEventListener('click', () => {
        const showPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', showPassword ? 'text' : 'password');
        updateToggle(showPassword);
    });
});

