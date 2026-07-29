import './styles/app.css';

document.addEventListener('DOMContentLoaded', () => {
    createCopyButtons();
});

export const createCopyButtons = () => {
    document.querySelectorAll('[data-copy]').forEach(el => {
        const nuevoElemento = duplicaElementoConBotonParaCopiar(el);
        el.parentNode.replaceChild(nuevoElemento, el);
    });
    console.log(document.querySelectorAll('[data-copy]'));
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
        // si se hace con data-bs-trigger="click" luego  no se puede ocultar manualmente
        tooltip.show()
        setTimeout(() => {
            tooltip.hide();
            elIcono.classList.remove('copy-ok');
        }, 1500);
    }
    return elIcono;
}
