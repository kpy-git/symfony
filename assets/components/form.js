import '../styles/components/form.css';
import '../styles/components/button.css';

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (!toggle) {
        return;
    }

    const inputId = toggle.getAttribute('aria-controls');
    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    const isVisible = input.type === 'text';

    input.type = isVisible ? 'password' : 'text';

    toggle.classList.toggle('is-visible', !isVisible);
    toggle.setAttribute('aria-pressed', String(!isVisible));
    toggle.setAttribute(
        'aria-label',
        isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña'
    );
});
