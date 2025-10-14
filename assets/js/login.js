document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.password-toggle, [data-password-toggle]');

    if (!toggles.length) {
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

    const getTargetInput = (button) => {
        const targetId = button.dataset.passwordToggle;
        if (targetId) {
            const byId = document.getElementById(targetId);
            if (byId) {
                return byId;
            }
        }

        const wrapper = button.closest('.password-wrapper');
        if (wrapper) {
            const input = wrapper.querySelector('input[type="password"], input[type="text"]');
            if (input) {
                return input;
            }
        }

        return null;
    };

    const updateToggle = (button, input) => {
        const isVisible = input.type === 'text';
        button.setAttribute('aria-pressed', String(isVisible));
        button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        button.innerHTML = isVisible ? icons.hide : icons.show;
    };

    toggles.forEach((toggle) => {
        const passwordInput = getTargetInput(toggle);

        if (passwordInput) {
            updateToggle(toggle, passwordInput);
        }
    });

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.password-toggle, [data-password-toggle]');

        if (!toggle || !toggle.matches('button')) {
            return;
        }

        const passwordInput = getTargetInput(toggle);

        if (!passwordInput) {
            return;
        }

        event.preventDefault();
        const shouldShow = passwordInput.type === 'password';
        passwordInput.type = shouldShow ? 'text' : 'password';
        updateToggle(toggle, passwordInput);
    });
});
