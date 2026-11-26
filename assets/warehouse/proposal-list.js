import '../styles/warehouse/proposal.css';

import {hideLoadingModal, showLoadingModal, showMessageModal} from "../components/modal.js";
import {makeFetchGet} from "../app.js";

document.addEventListener('DOMContentLoaded', async () => {
    await loadSuppliersPreview();
});

document.getElementById('btn__refresh').addEventListener('click', async () => {
    await loadSuppliersPreview();
});

async function loadSuppliersPreview() {
    showLoadingModal();

    const response = await makeFetchGet('/buyproposal/suppliersPreview');

    hideLoadingModal();

    if (response.status !== 200) {
        showMessageModal({
            type: 'error',
            message: response.message,
        });
        return;
    }

    document.querySelector('.suppliers-list').innerHTML = response.html;
}


