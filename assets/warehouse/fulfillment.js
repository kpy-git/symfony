import '../styles/warehouse/fulfillment.css';

import qz from 'qz-tray';


document.getElementById('listaPedidos').addEventListener('click', (e) => {
    if (e.target.closest('.item-pedido')) {
        verDetalle(e.target.closest('.item-pedido').dataset.pedido)
    }
});

async function verDetalle(id) {
    // Marcar visualmente el elemento seleccionado en la lista izquierda
    document.querySelectorAll('.item-pedido').forEach(el => el.classList.remove('seleccionado'));
    document.getElementById(`li-${id}`).classList.add('seleccionado');

    const panel = document.getElementById('panelDetalle');
    panel.innerHTML = `
                <div class="contenedor-carga">
                    <div class="spinner"></div>
                    <p>Buscando información del pedido...</p>
                </div>
            `;


    const response = await fetch(`/ajaxOrderDetails?order=${id}`);

    const data = await response.json();

    document.getElementById('panelDetalle').innerHTML = `${data.html}`;
}

document.getElementById('panelDetalle').addEventListener('click', async (e) => {
    if (!e.target.closest('.btn-preparar')) {
        return;
    }

    const btn = e.target.closest('.btn-preparar');
    btn.disabled = true;

    const id = document.querySelector('.detalle-bloque').dataset.id;

    setTimeout(() => {
        const itemLista = document.getElementById(`li-${id}`);
        if (itemLista) {
            itemLista.style.display = 'none';
        }

        const panel = document.getElementById('panelDetalle');
        panel.innerHTML = `
            <div class="vista-vacia" style="flex-direction: column; gap: 10px;">
                <span style="font-size: 3rem;">📦</span>
                <h3 style="color: #27ae60;">¡Pedido #${id} preparado!</h3>
                <p style="color: #555;">Etiqueta de envío generada y enviada a la impresora.</p>
            </div>
        `;
    }, 1000);

    qz.websocket.connect().then(function () {
        alert("Connected!");
    });
});
