export class ButtonController {
    static setLoading(button, loading = true, text = null) {
        if (!button) {
            return;
        }

        const textElement = button.querySelector('.btn__text');

        if (loading) {
            // Evitamos guardar nuevamente el estado original.
            if (!button.dataset.loading) {
                button.dataset.originalText =
                    textElement?.textContent ?? '';

                button.dataset.originalDisabled =
                    String(button.disabled);

                button.dataset.loading = 'true';
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

            if (textElement) {
                textElement.textContent =
                    text ?? button.dataset.loadingText ?? 'Procesando...';
            }

            return;
        }

        // Restauramos el estado original.
        button.disabled =
            button.dataset.originalDisabled === 'true';

        button.removeAttribute('aria-busy');

        if (textElement && button.dataset.originalText !== undefined) {
            textElement.textContent = button.dataset.originalText;
        }

        // Limpiamos los datos temporales.
        delete button.dataset.originalText;
        delete button.dataset.originalDisabled;
        delete button.dataset.loading;
    }
}
