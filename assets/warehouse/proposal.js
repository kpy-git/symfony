import '../styles/warehouse/proposal.css';

import {makeFetchJSON} from "../app.js";
import {hideLoadingModal, showLoadingModal, showMessageModal} from "../components/modal.js";

document.addEventListener('change', (e) => {
    if (e.target.id === 'master-checkbox') {
        updateCheckBoxesStatus(e.target.checked);
    }

    if (e.target.matches('input[type="checkbox"]')) {
        updateOrderTotal();
        return;
    }

    const tr = e.target.closest('tr[data-sku]');

    if (!tr) {
        return;
    }

    let amount = Number(tr.querySelector('[data-unit-price]').dataset.unitPrice);
    let total = (Math.trunc(amount * 100) * parseInt(e.target.value)) / 100;

    const elTotal = tr.querySelector('[data-total-amount]');

    elTotal.dataset.totalAmount = total.toString();
    elTotal.textContent = `${total} €`;

    updateOrderTotal();
});

function updateCheckBoxesStatus(status) {
    document.querySelectorAll('tr[data-sku] input[type="checkbox"]').forEach(checkBox => {
        checkBox.checked = status;
    });
}

function updateOrderTotal() {
    let total = Array.from(document.querySelectorAll('[data-total-amount]'))
        .filter(el => el.closest('tr[data-sku]').querySelector('input[type="checkbox"]:checked'))
        .reduce((acc, el) => acc += Math.trunc(Number(el.dataset.totalAmount) * 100), 0);

    total = total / 100;
    const el = document.querySelector('[data-order-total]');
    el.textContent = `${total} €`;
    el.dataset.orderTotal = total.toString();
}

document.getElementById('btn__create-order-supplier').addEventListener('click', async () => {
    const products = Array
        .from(document.querySelectorAll('tr[data-sku]:has(input[type="checkbox"]:checked)'))
        .map(tr => buildProposalProductFromTableRow(tr));

    showLoadingModal();

    const response = await makeFetchJSON('/buyproposal/createOrder', {
        supplier: document.querySelector('[data-supplier]').dataset.supplier,
        products,
    });

    hideLoadingModal();

    if (response.status !== 200) {
        showMessageModal({
            type: 'error',
            message: response.message,
        });
        return;
    }

    showMessageModal({
        type: 'success',
        title: `El pedido se ha creado correctamente`,
        message: `Número de pedido: ${response.order_number}`,
    });
});

function buildProposalProductFromTableRow(tr) {
    if (!tr.hasAttribute('data-sku')) {
        return {};
    }

    return {
        sku: tr.dataset.sku,
        name: tr.querySelector('.product-name').textContent,
        unitPrice: Number(tr.querySelector('[data-unit-price]').dataset.unitPrice),
        totalPrice: Number(tr.querySelector('[data-total-amount]').dataset.totalAmount),
        units: parseInt(tr.querySelector('input[name="quantity"]').value),
        ...tr.dataset
    };
}
