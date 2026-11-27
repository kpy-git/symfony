import '../styles/warehouse/prebuy.css';
import {createCopyButtons, makeFetchDelete, makeFetchJSON, makeFetchPost,} from "../app.js";
import {showMessageModal} from "../components/modal.js";
import {ButtonController} from "../components/buttonController.js";

document.querySelectorAll('button.order-preview').forEach((item) => {
    item.addEventListener('click', async (e) => {
        const elOrder = e.target.closest('.order-item');
        elOrder.classList.add('is-loading');
        elOrder.disabled = true;

        const response = await makeFetchPost('/prebuy/orderdetail', {
            operation: elOrder.dataset.operation,
            order_id: elOrder.dataset.order,
        });

        if (response.status !== 200) {
            showMessageModal({
                message: response.message,
                type: 'error',
            })
            return;
        }

        elOrder.classList.remove('is-loading');
        elOrder.classList.add('is-expanded');
        elOrder.querySelector('.order-detail').innerHTML = response.html;
        elOrder.querySelector('.order-detail').hidden = false;
        elOrder.setAttribute('aria-expanded', 'true');

        createCopyButtons(elOrder);
    });
});

document.addEventListener('change', (event) => {
    const radio = event.target;

    if (!radio.matches('[data-purchase-choice]')) {
        return;
    }

    const row = radio.closest('[data-product-row]');
    const quantity = row.querySelector('.order-product__quantity');

    if (!quantity) {
        return;
    }

    quantity.hidden = radio.value !== 'yes';
});

document.addEventListener('click', (event) => {
    const decreaseButton = event.target.closest('[data-quantity-decrease]');
    const increaseButton = event.target.closest('[data-quantity-increase]');

    if (!decreaseButton && !increaseButton) {
        return;
    }

    const quantityWrapper = event.target.closest('.order-product__quantity');
    const input = quantityWrapper?.querySelector('[data-purchase-quantity]');

    if (!input) {
        return;
    }

    const min = Number(input.min) || 1;
    const max = Number(input.max);
    let newValue = Number(input.value) || 0;

    if (decreaseButton) {
        newValue--;
    } else if (increaseButton) {
        newValue++;
    }

    // Respetar los límites del input.
    newValue = Math.max(min, Math.min(newValue, max));

    input.value = newValue;
});


document.addEventListener('click', async (event) => {
    const btnOrderCheck = event.target.closest('.btn.check-order');
    if (!btnOrderCheck) {
        return;
    }

    ButtonController.setLoading(btnOrderCheck);

    const elOrder = btnOrderCheck.closest('.order-item');

    const response = await makeFetchJSON('/prebuy/produts', getArrayProductsFromDetail(elOrder));

    if (response.status !== 200) {
        showMessageModal({
            type: 'success',
            title: 'Operación completada',
            message: 'Productos agregados correctamente.',
        });
        return;
    }

    const deleteResponse = await makeFetchDelete(`/prebuy/orderchecked/${elOrder.dataset.order}`);

    if (deleteResponse.status !== 200) {
        showMessageModal({
            type: 'error',
            title: 'Error',
            message: deleteResponse.message,
        });
        return;
    }

    elOrder.remove();
});

function getArrayProductsFromDetail(orderDetail) {
    return Array.from(orderDetail.querySelectorAll('[data-sku]'))
        .filter((item) => item.querySelector('[data-purchase-choice]:checked')?.value === 'yes')
        .map((item) => {
            return {sku: item.dataset.sku, quantity: Number(item.querySelector('[data-purchase-quantity]').value)}
        });
}

document.addEventListener('change', (event) => {
    if (!event.target.matches('[data-purchase-choice]')) {
        return;
    }

    const orderDetail = event.target.closest('.order-detail');

    if (orderDetail) {
        updateReviewButton(orderDetail);
    }
});

function updateReviewButton(orderDetail) {
    const products = orderDetail.querySelectorAll('[data-product-row]:has([data-purchase-choice])');
    const reviewButton = orderDetail.querySelector('[data-mark-reviewed]');

    if (!reviewButton || products.length === 0) {
        return;
    }

    const allSelected = Array.from(products).every((product) => {
        return product.querySelector('[data-purchase-choice]:checked') !== null;
    });

    reviewButton.disabled = !allSelected;
}
