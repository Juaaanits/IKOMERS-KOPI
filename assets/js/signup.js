document.addEventListener('DOMContentLoaded', () => {
    const fields = [
        { button: document.querySelector('.eye-btn1'), input: document.getElementById('password') },
        { button: document.querySelector('.eye-btn2'), input: document.getElementById('confirm_password') }
    ];

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

    fields.forEach(({ button, input }) => {
        if (!button || !input) {
            return;
        }

        const updateToggle = (isVisible) => {
            button.setAttribute('aria-pressed', String(isVisible));
            button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
            button.innerHTML = isVisible ? icons.hide : icons.show;
        };

        updateToggle(false);

        button.addEventListener('click', (event) => {
            event.preventDefault();
            const shouldShow = input.getAttribute('type') === 'password';
            input.setAttribute('type', shouldShow ? 'text' : 'password');
            updateToggle(shouldShow);
        });
    });
});
