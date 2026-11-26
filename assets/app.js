import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/js/all.min.js';

import './styles/app.css';
import './styles/layout.css';
import './components/form.js';
import './components/modal.js';
import {showLoadingModal} from "./components/modal.js";

document.addEventListener('DOMContentLoaded', () => {
    createCopyButtons();
});

export const createCopyButtons = (el = document) => {
    el.querySelectorAll('[data-copy]').forEach(item => {
        createCopyButton(item);
    });
    //console.log(document.querySelectorAll('[data-copy]'));
}

export const createCopyButton = (el) => {
    const nuevoElemento = duplicaElementoConBotonParaCopiar(el);
    el.parentNode.replaceChild(nuevoElemento, el);
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
        navigator.clipboard.writeText(event.target.closest('[data-copy]').dataset.copy);
        elIcono.classList.add('copy-ok');
        /*const tooltip = new Tooltip(elIcono.querySelector('span[data-bs-toggle]'))
        // si se hace con data-bs-trigger="click" luego no se puede ocultar manualmente
        tooltip.show()
        setTimeout(() => {
            tooltip.hide();
            elIcono.classList.remove('copy-ok');
        }, 1500);*/
    }
    return elIcono;
}

const appLayout = document.querySelector('.app-layout');
const sidebarToggle = document.querySelector('.header-sidebar-toggle');
const sidebarToggleFooter = document.querySelector('.footer-sidebar-toggle');

if (appLayout && sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        toggleSidebar();
    });
    sidebarToggleFooter.addEventListener('click', () => {
        toggleSidebar();
    });
}

function toggleSidebar() {
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

export const htmlToElement = (html) => {
    const template = document.createElement('template');
    template.innerHTML = html;
    return template.content.firstChild;
}


export const makeFetchPost = async (url, params) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: new Headers({
            'Content-type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json;charset=UTF-8',
        }),
        body: new URLSearchParams(params),
    });

    return response.json();
}

export const makeFetchJSON = async (url, data) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: new Headers({
            'Content-Type': 'application/json;charset=UTF-8',
            'Accept': 'application/json;charset=UTF-8',
        }),
        body: JSON.stringify(data),
    });

    return response.json();
}

const makeFetchCustom = async (url, params, method) => {
    if (Object.keys(params).length) {
        url += `?${new URLSearchParams(params).toString()}`;
    }
    const response = await fetch(url, {
        method,
        headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json;charset=UTF-8',
        },
    });

    return response.json();
}

export const makeFetchGet = async (url, params = {}) => {
    return makeFetchCustom(url, params, 'GET');
}

export const makeFetchDelete = async (url) => {
    return makeFetchCustom(url, {}, 'DELETE');
}

document.addEventListener('click', async (event) => {
    const link = event.target.closest('a[data-loading-navigation]');
    if (!link) {
        return;
    }

    // Respetar el comportamiento de apertura en otra pestaña.
    if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.ctrlKey ||
        event.metaKey ||
        event.shiftKey ||
        event.altKey ||
        link.target === '_blank'
    ) {
        return;
    }

    event.preventDefault();
    showLoadingModal();
    window.location.href = link.href;

});
