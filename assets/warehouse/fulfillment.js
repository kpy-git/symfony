import '../styles/warehouse/fulfillment.css';

import qz from 'qz-tray';
import {createCopyButtons} from "../app.js";


document.getElementById('listaPedidos').addEventListener('click', async (e) => {
    if (e.target.closest('.item-pedido')) {
        await verDetalle(e.target.closest('.item-pedido').dataset.pedido)
    }
});

const printerConfig = JSON.parse(document.getElementById('printer-config').textContent);

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
    createCopyButtons();
}

document.getElementById('panelDetalle').addEventListener('click', async (e) => {
    if (e.target.matches('.btn-bulto')) {
        modificarBultos(e.target.classList.contains('add') ? 1 : -1);
        return;
    }

    if (!e.target.closest('.btn-preparar')) {
        return;
    }

    const btn = e.target.closest('.btn-preparar');
    btn.disabled = true;

    const orderID = document.querySelector('.detalle-bloque').dataset.id;

    const shipmentResponse = await fetch(`/ajaxCreateShipment`, {
        method: 'POST',
        body: new URLSearchParams({
            order: orderID,
            parcels: document.getElementById('input__parcels').value,
        }),
    });

    const shipmentData = await shipmentResponse.json();

    if (shipmentData.status !== 200) {
        document.getElementById('panelDetalle').innerHTML = `
            <div class="vista-vacia" style="flex-direction: column; gap: 10px;">
                <span style="font-size: 3rem;">📦</span>
                <h3 style="color: red;">¡Ups! Ha ocurrido un error al obtener la etiqueta</h3>
                <p style="color: #555;">${shipmentData.message}</p>
            </div>`;
        return;
    }


    const itemLista = document.getElementById(`li-${orderID}`);
    if (itemLista) {
        itemLista.style.display = 'none';
    }

    const panel = document.getElementById('panelDetalle');
    panel.innerHTML = `
        <div class="vista-vacia" style="flex-direction: column; gap: 10px;">
            <span style="font-size: 3rem;">📦</span>
            <h3 style="color: #27ae60;">¡Pedido #${orderID} preparado!</h3>
            <p style="color: #555;">Etiqueta de envío generada y enviada a la impresora.</p>
        </div>
    `;

    try {
        qz.security.setSignatureAlgorithm("SHA512");
        qz.security.setCertificatePromise((resolve, reject) => {
            resolve(
                "-----BEGIN CERTIFICATE-----\n" +
                "MIIECzCCAvOgAwIBAgIGAZ9lEUg6MA0GCSqGSIb3DQEBCwUAMIGiMQswCQYDVQQG\n" +
                "EwJVUzELMAkGA1UECAwCTlkxEjAQBgNVBAcMCUNhbmFzdG90YTEbMBkGA1UECgwS\n" +
                "UVogSW5kdXN0cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMx\n" +
                "HDAaBgkqhkiG9w0BCQEWDXN1cHBvcnRAcXouaW8xGjAYBgNVBAMMEVFaIFRyYXkg\n" +
                "RGVtbyBDZXJ0MB4XDTI2MDcxNDA5MTczOVoXDTQ2MDcxNDA5MTczOVowgaIxCzAJ\n" +
                "BgNVBAYTAlVTMQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYD\n" +
                "VQQKDBJRWiBJbmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMs\n" +
                "IExMQzEcMBoGCSqGSIb3DQEJARYNc3VwcG9ydEBxei5pbzEaMBgGA1UEAwwRUVog\n" +
                "VHJheSBEZW1vIENlcnQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDd\n" +
                "7AzUSqfC9TyM6cgKNRSw6L24Il6TvCJRUHABzQfAt7HUTVRwDVUYO9KDID+EoGVW\n" +
                "Dgc+vG0g2iQl/ja2vTcrZz6uajjuVI2A6Yqntw6i3CZKSb6bBQAczJaTCB2P4Im3\n" +
                "c97W2d1aNSfT4eKD+QgHfpcFN/a6I4Hv8ic4NoSqjolsqRgi/9toz4SUJzy4N/iN\n" +
                "2ygPebdL1UgHvbGj7Gh4z86X5Y0iYCP44uUg9CIIoJlUkNL1i6dY7pm72El6JTF9\n" +
                "2FqFUWaqyhGtijzolpOFwq87x9S1LKyiHBA5REREGjGqsY0t9GqiKI8AapBpRje2\n" +
                "42QpJ879kZWW4mJkzr83AgMBAAGjRTBDMBIGA1UdEwEB/wQIMAYBAf8CAQEwDgYD\n" +
                "VR0PAQH/BAQDAgEGMB0GA1UdDgQWBBRUJMfiaVlih7QjHoNZwRFc04D/tzANBgkq\n" +
                "hkiG9w0BAQsFAAOCAQEABU8duHn6i07rZmKApzP4ztxd2BCf7JSBliJ4TNaa/q/9\n" +
                "Rpio0lE4tC2/rVDHPxcuadby3EBvGZ2ShijcwiuUnGLzF0wu8G7GxGvHS1NQanJp\n" +
                "7UJgXw8/GwF19GONjX/K/pg1jbdvp8jd1Q9OqdCYie4Ec6wrXldFA9Y1qx6SIwz2\n" +
                "jMzTSC07q8RwpXfeNxBVMuRQwf/ntgXU1LKbYJsXnl474/RK1Rw6HbH0zRT6qcqI\n" +
                "JR4FYSuMTMK00amOXIdyLx94bMeSbWHqNakwzNZ6QGKCC5w+K/X9OTE7XFRenhCo\n" +
                "OvWfgetIwt6zW6N1+y0aLDuOqqa0YPEn55QASFcRnA==\n" +
                "-----END CERTIFICATE-----"
            )
        });
        qz.security.setSignaturePromise((toSign) => {
            return (resolve, reject) => {
                fetch("/sing-print", {
                    method: "POST",
                    headers: { "Content-Type": "text/plain" },
                    body: toSign
                })
                    .then(response => response.text())
                    .then(resolve)
                    .catch(reject);
            };
        });

        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }
        
        let configuration;
        if (printerConfig.type === 'usb') {
            configuration = printerConfig.name;
        } else {
            configuration = {
                "host": printerConfig.host,
                "port": printerConfig.port,
            }
        }

        const conf = qz.configs.create(configuration);

        await qz.print(conf, [shipmentData.label]);

    } catch (error) {
        console.error(error);
        alert('No puedo imprimir: ' + error.message);
    }
});

function modificarBultos(delta) {
    const input = document.getElementById('input__parcels');
    if (!input) return;

    let valorActual = parseInt(input.value) || 1;
    valorActual += delta;

    if (valorActual < 1) valorActual = 1; // Mínimo 1 bulto
    input.value = valorActual;
}
