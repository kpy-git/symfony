import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/js/all.min.js';

import './styles/app.css';
import './styles/layout.css';
import './components/form.js';

document.addEventListener('DOMContentLoaded', () => {
    createCopyButtons();
});

export const createCopyButtons = () => {
    document.querySelectorAll('[data-copy]').forEach(el => {
        const nuevoElemento = duplicaElementoConBotonParaCopiar(el);
        el.parentNode.replaceChild(nuevoElemento, el);
    });
    //console.log(document.querySelectorAll('[data-copy]'));
}

const duplicaElementoConBotonParaCopiar = (el) => {
    const elNuevo = document.createElement(el.tagName);

    elNuevo.classList.add(...el.classList);
    Object.assign(elNuevo.dataset, el.dataset);
    if (el.id) {
        elNuevo.id = el.id;
    }
    elNuevo.innerHTML = el.innerHTML;

    const elIcono = creaIconoCopiar();
    elNuevo.appendChild(elIcono);

    return elNuevo;
}

const creaIconoCopiar = () => {
    const elIcono = document.createElement('span');
    elIcono.classList.add('copy');
    elIcono.innerHTML = `<span data-bs-toggle="tooltip" data-bs-title="¡Copiado!" data-bs-trigger="manual"><i class="fas fa-copy"></i><span></span>`;
    elIcono.onclick = (event) => {
        const tooltip = new Tooltip(elIcono.querySelector('span[data-bs-toggle]'))
        navigator.clipboard.writeText(event.target.closest('[data-copy]').dataset.copy);
        elIcono.classList.add('copy-ok');
        // si se hace con data-bs-trigger="click" luego no se puede ocultar manualmente
        tooltip.show()
        setTimeout(() => {
            tooltip.hide();
            elIcono.classList.remove('copy-ok');
        }, 1500);
    }
    return elIcono;
}

const appLayout = document.querySelector('.app-layout');
const sidebarToggle = document.querySelector('.header-sidebar-toggle');
const sidebarToggleFooter = document.querySelector('.footer-sidebar-toggle');

if (appLayout && sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        togogleSidebar();
    });
    sidebarToggleFooter.addEventListener('click', () => {
        togogleSidebar();
    });
}

function togogleSidebar() {
    const isHidden = appLayout.classList.toggle('sidebar-hidden');

    sidebarToggle.setAttribute('aria-expanded', String(!isHidden));
    sidebarToggle.setAttribute(
        'aria-label',
        isHidden ? 'Mostrar menú' : 'Ocultar menú'
    );
}


const userDropdown = document.querySelector('.user-dropdown');

if (userDropdown) {
    const userMenu = userDropdown.querySelector('.user-menu');

    const closeDropdown = () => {
        userDropdown.classList.remove('open');
        userMenu.setAttribute('aria-expanded', 'false');
    };

    userMenu.addEventListener('click', () => {
        const isOpen = userDropdown.classList.toggle('open');

        userMenu.setAttribute('aria-expanded', String(isOpen));
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', (event) => {
        if (!userDropdown.contains(event.target)) {
            closeDropdown();
        }
    });

    // Cerrar con Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
            userMenu.focus();
        }
    });
}
