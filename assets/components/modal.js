const AppModal = (() => {
    const modal = document.getElementById('app-modal');

    if (!modal) {
        return {
            show: () => {
            },
            showConfirm: () => {
            },
            close: () => {
            }
        };
    }

    const title = modal.querySelector('#app-modal-title');
    const message = modal.querySelector('#app-modal-message');
    const icon = modal.querySelector('#app-modal-icon');

    const acceptButton = modal.querySelector('.app-modal__accept');
    const cancelButton = modal.querySelector('.app-modal__cancel');
    const closeButton = modal.querySelector('.app-modal__close');
    const backdrop = modal.querySelector('.app-modal__backdrop');

    const types = {
        success: {
            icon: 'fa-circle-check',
            title: 'Operación realizada'
        },
        error: {
            icon: 'fa-circle-xmark',
            title: 'Ha ocurrido un error'
        },
        warning: {
            icon: 'fa-triangle-exclamation',
            title: 'Atención'
        },
        info: {
            icon: 'fa-circle-info',
            title: 'Información'
        }
    };

    let previousFocus = null;
    let onConfirm = null;
    let confirmMode = false;
    let isProcessing = false;

    function close() {
        if (
            !modal.classList.contains('is-visible') ||
            isProcessing
        ) {
            return;
        }

        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('modal-open');

        onConfirm = null;
        confirmMode = false;

        cancelButton.hidden = true;

        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    }

    function prepare(options = {}) {
        const type = types[options.type] ? options.type : 'info';
        const config = types[type];

        title.textContent = options.title || config.title;
        message.innerHTML = `<div class="message message--${type}" role="${type}">
                <i class="message__icon fa-solid ${config.icon}" aria-hidden="true"></i>
                <div class="message__content">
                    ${options.message || ''}
                </div>
            </div>`;

        previousFocus = document.activeElement;

        modal.classList.remove(
            'app-modal__dialog--sm',
            'app-modal__dialog--xl',
            'app-modal__dialog--xxl'
        );

        const size = ['sm', 'xl', 'xxl'].includes(options.size)
            ? options.size
            : '';

        if (size !== '') {
            modal.classList.add(`app-modal__dialog--${size}`);
        }

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('modal-open');
    }

    function show(options = {}, buttonText) {
        if (isProcessing) {
            return;
        }

        confirmMode = false;
        onConfirm = null;

        cancelButton.hidden = true;

        acceptButton.hidden = false;
        acceptButton.disabled = false;
        acceptButton.textContent = buttonText;

        prepare(options);

        acceptButton.focus();
    }

    function showConfirm(
        options = {},
        callback = () => {
        },
        cancelText = 'Cerrar',
        confirmText = 'Confirmar'
    ) {
        if (isProcessing) {
            return;
        }

        confirmMode = true;
        onConfirm = callback;

        cancelButton.hidden = false;
        cancelButton.disabled = false;
        cancelButton.textContent = cancelText;

        acceptButton.hidden = false;
        acceptButton.disabled = false;
        acceptButton.textContent = confirmText;

        prepare(options);

        acceptButton.focus();
    }

    function setProcessing(processing) {
        isProcessing = processing;

        acceptButton.disabled = processing;
        cancelButton.disabled = processing;
        closeButton.disabled = processing;

        if (processing) {
            acceptButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Procesando...
            `;
        }
    }

    async function confirm() {
        if (!confirmMode || isProcessing) {
            return;
        }

        const callback = onConfirm;
        const confirmText = acceptButton.textContent;

        setProcessing(true);

        try {
            await callback();

            setProcessing(false);
            close();

        } catch (error) {
            console.error('Error en la operación:', error);

            setProcessing(false);
            close();

            showMessageModal({
                type: 'error',
                title: 'No se ha podido completar la operación',
                message: error instanceof Error
                    ? error.message
                    : 'Ha ocurrido un error inesperado. Inténtalo de nuevo.'
            });

        } finally {
            // Restablecemos el botón para futuras utilizaciones.
            acceptButton.innerHTML = '';
            acceptButton.textContent = confirmText;
        }
    }

    acceptButton.addEventListener('click', () => {
        if (confirmMode) {
            confirm();
        } else {
            close();
        }
    });

    cancelButton.addEventListener('click', close);
    closeButton.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-visible')) {
            return;
        }

        if (event.key === 'Escape') {
            if (!isProcessing) {
                close();
            }
        }

        if (event.key === 'Tab') {
            event.preventDefault();

            if (isProcessing) {
                acceptButton.focus();
                return;
            }

            if (confirmMode) {
                if (document.activeElement === acceptButton) {
                    cancelButton.focus();
                } else {
                    acceptButton.focus();
                }
            } else {
                acceptButton.focus();
            }
        }
    });

    return {show, showConfirm, close};
})();

export const showMessageModal = (options, buttonText = 'Cerrar') => {
    AppModal.show(options, buttonText);
}

export const showConfirmModal = (
    options,
    callback,
    cancelText = 'Cerrar',
    confirmText = 'Confirmar'
) => {
    AppModal.showConfirm(options, callback, cancelText, confirmText);
}

const loadingModalElement = document.getElementById('modal-loading');

export const showLoadingModal = ()  => {
    loadingModalElement.classList.add('is-visible');
    loadingModalElement.setAttribute('aria-hidden', 'false');
}

export const hideLoadingModal = () => {
    loadingModalElement.classList.remove('is-visible');
    loadingModalElement.setAttribute('aria-hidden', 'true');
}
