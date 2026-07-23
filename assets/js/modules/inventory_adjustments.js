document.addEventListener('DOMContentLoaded', () => {

  const btnNewInventoryAdjustment = document.getElementById('btnNewInventoryAdjustment');
  const btnAddProductInventoryAdjustment = document.getElementById('btnAddProductInventoryAdjustment');
  const btnEditInventoryAdjustment = document.getElementById('btnEditInventoryAdjustment');
  const btnSaveInventoryAdjustment = document.getElementById('btnSaveInventoryAdjustment');
  const btnCancelInventoryAdjustment = document.getElementById('btnCancelInventoryAdjustment');
  const btnBackInventoryAdjustment = document.getElementById('btnBackInventoryAdjustment');
  const tblInventoryAdjustmentDetails = document.getElementById('tblInventoryAdjustmentDetails');
  const btnPostInventoryAdjustment = document.getElementById('btnPostInventoryAdjustment');
  const btnRefreshInventoryAdjustment = document.getElementById('btnRefreshInventoryAdjustment');
  const btnPrintInventoryAdjustment = document.getElementById('btnPrintInventoryAdjustment');

  btnNewInventoryAdjustment?.addEventListener('click', () => Atlas.page.redirect(`inventory_adjustments/create`));

  /*** add */
  btnAddProductInventoryAdjustment?.addEventListener('click', () => {
    Atlas.productFinder.select(product => {
      const existingRow = findProductRow(product.id);

      if (existingRow) {
        Atlas.toast.warning('Product selected is already in Adjustment Details.');
        existingRow.querySelector('.ia-adjustment').focus();
        return;
      }

      const row = addDetailRow();
      populateProductRow(row, product);
    });
  });

  /*** edit/update/view */
  btnEditInventoryAdjustment?.addEventListener('click', () => {
    const adjustmentId = getSelectedInventoryAdjustmentId();

    if (!adjustmentId) {
      return;
    }

    Atlas.page.redirect(`inventory_adjustments/view/${adjustmentId}`)
  });

  /*** save */
  btnSaveInventoryAdjustment?.addEventListener('click', async () => {
    await saveInventoryAdjustment();
  });

  /*** post */
  btnPostInventoryAdjustment?.addEventListener('click', async () => {
    await postInventoryAdjustment();
  });

  /*** cancel */
  btnCancelInventoryAdjustment?.addEventListener('click', async () => {
    await cancelInventoryAdjustment();
  });

  /*** delete row in adjustment details */
  tblInventoryAdjustmentDetails?.addEventListener('click', (e) => {
    if (!e.target.classList.contains('btn-delete-row')) {
      return;
    }

    e.target.closest('tr').remove();
    restorePlaceholderRow();
  });

  /*** print */
  btnPrintInventoryAdjustment?.addEventListener('click', () => {
    const adjustmentId = getSelectedInventoryAdjustmentId();

    if (!adjustmentId) return;

    Atlas.page.open(`inventory_adjustments/print/${adjustmentId}`)
  })

  /*** refresh */
  btnRefreshInventoryAdjustment?.addEventListener('click', () => Atlas.page.refresh())

});

const addDetailRow = () => {
  const tbody = document.getElementById('tblInventoryAdjustmentDetails');
  const placeholder = document.getElementById('iaPlaceholderRow');

  if (placeholder) {
    placeholder.remove();
  }

  tbody.insertAdjacentHTML('beforeend', createDetailRow());

  const row = tbody.lastElementChild;
  const lblOnHand = row.querySelector('.ia-on-hand');
  const txtAdjustment = row.querySelector('.ia-adjustment');
  const lblNewBalance = row.querySelector('.ia-new-balance');

  txtAdjustment.addEventListener('input', () => {
    const onHand = parseFloat(lblOnHand.textContent) || 0;
    const adjustment = parseFloat(txtAdjustment.value) || 0;

    lblNewBalance.textContent = onHand + adjustment;
  });

  return row;
};

const createDetailRow = () => {
  return `
        <tr>
          <td class="ia-barcode"></td>
          <td class="ia-description"></td>
          <td class="ia-uom text-center"></td>
          <td class="ia-on-hand text-right">0</td>
          <td>
            <input
                type="number"
                step="any"
                class="form-control form-control-sm text-right ia-adjustment"
                value="">
          </td>
          <td class="ia-new-balance text-right">0</td>
          <td class="text-center">
            <i
              class="fas fa-trash text-danger pointer btn-delete-row"
              title="Remove Product" data-toggle="toolitp">
            </i>
          </td>
        </tr>
    `;
};

const findProductRow = (productId) => {
  const rows = document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr'
  );

  for (const row of rows) {
    if (parseInt(row.dataset.productId, 10) === parseInt(productId, 10)) {
      return row;
    }
  }
  return null;
};

const collectAdjustmentHeader = () => {
  return {
    adjustment_id: document.getElementById('hidInventoryAdjustmentId').value,
    adjustment_date: document.getElementById('dtAdjustmentDate').value,
    remarks: document.getElementById('txtAdjustmentRemarks').value.trim()
  };
};

const collectAdjustmentDetails = () => {
  const details = [];

  document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr'
  ).forEach(row => {

    const productId = parseInt(row.dataset.productId, 10);

    /*** Skip placeholder or incomplete rows */
    if (!productId) {
      return;
    }

    details.push({
      product_id: productId,
      on_hand: parseFloat(
        row.querySelector('.ia-on-hand').textContent
      ) || 0,
      adjustment: parseFloat(
        row.querySelector('.ia-adjustment').value
      ) || 0,
      new_balance: parseFloat(
        row.querySelector('.ia-new-balance').textContent
      ) || 0
    });
  });

  return details;
};

const validateInventoryAdjustment = () => {
  const rows = document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr[data-product-id]'
  );

  if (rows.length === 0) {
    Atlas.toast.warning('Please add at least one product.');
    return false;
  }

  for (const row of rows) {
    const txtAdjustment = row.querySelector('.ia-adjustment');
    const adjustment = parseFloat(txtAdjustment.value) || 0;

    if (adjustment === 0) {

      Atlas.toast.warning(
        'Adjustment quantity cannot be zero.'
      );

      txtAdjustment.focus();
      row.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
      txtAdjustment.select();

      return false;
    }
  }
  return true;
};

const saveInventoryAdjustment = async () => {
  if (!validateInventoryAdjustment()) {
    return;
  }

  const payload = {
    ...collectAdjustmentHeader(),
    details: collectAdjustmentDetails()
  };

  const response = await Atlas.ajax.post(
    'inventory_adjustments/save',
    payload
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  Atlas.toast.success(response.message);
  setTimeout(() => Atlas.page.redirect(`inventory_adjustments`), 1500);
};

const populateProductRow = async (row, product) => {
  row.dataset.productId = product.id;
  row.querySelector('.ia-barcode').textContent = product.barcode ?? '';
  row.querySelector('.ia-description').textContent = product.description ?? '';
  row.querySelector('.ia-uom').textContent = product.uom ?? '';
  row.querySelector('.ia-on-hand').textContent = '0';
  row.querySelector('.ia-adjustment').value = '';
  row.querySelector('.ia-new-balance').textContent = '0';

  const response = await Atlas.ajax.post(
    'inventory_adjustments/get_product_stock',
    {
      product_id: product.id
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  row.querySelector('.ia-on-hand').textContent = response.data.stock;
  row.querySelector('.ia-new-balance').textContent = response.data.stock;

  row.querySelector('.ia-adjustment').focus();
};

const restorePlaceholderRow = () => {
  const tbody = document.getElementById('tblInventoryAdjustmentDetails');

  if (tbody.querySelectorAll('tr').length > 0) {
    return;
  }

  tbody.innerHTML = `
      <tr id="iaPlaceholderRow">
        <td colspan="7" class="text-center text-muted py-2">
          No products added.
        </td>
      </tr>
    `;
};

const getSelectedInventoryAdjustmentId = () => {
  const checkedRows = document.querySelectorAll(
    'tbody .chkInventoryAdjustment:checked'
  );

  if (checkedRows.length === 0) {
    Atlas.toast.warning('Please select an Inventory Adjustment.');
    return null;
  }

  if (checkedRows.length > 1) {
    Atlas.toast.warning('Please select only one Inventory Adjustment.');
    return null;
  }

  return parseInt(
    checkedRows[0].closest('tr').dataset.id,
    10
  );
};

const postInventoryAdjustment = async () => {
  const adjustmentId = getSelectedInventoryAdjustmentId();

  if (!adjustmentId) {
    return;
  }

  const confirmed = await Atlas.dialog.confirm(
    'Post Inventory Adjustment?',
    'This action cannot be undone.'
  );

  if (!confirmed) {
    return;
  }

  await executeInventoryAdjustmentAction(
    'inventory_adjustments/post',
    {
      adjustment_id: adjustmentId
    }
  );
};

const cancelInventoryAdjustment = async () => {
  const adjustmentId = getSelectedInventoryAdjustmentId();

  if (!adjustmentId) {
    return;
  }

  const reason = await Atlas.dialog.textarea({
    icon: 'warning',
    title: 'Cancel Inventory Adjustment?',
    text: 'Please provide the reason for cancellation.',
    // inputLabel: 'Cancellation Reason',
    inputPlaceholder: 'Enter cancellation reason...',
    required: false, /*** set to true if you want this to be required */
    requiredMessage: 'Cancellation reason is required.',
    confirmText: 'Confirm Cancellation'
  });

  if (reason === null) {
    return;
  }

  await executeInventoryAdjustmentAction(
    'inventory_adjustments/cancel',
    {
      adjustment_id: adjustmentId,
      cancel_reason: reason
    }
  );
};

const executeInventoryAdjustmentAction = async (
  endpoint,
  payload,
  onSuccess = () => Atlas.page.refresh()
) => {

  Atlas.loader.show();

  try {
    const response = await Atlas.ajax.post(
      endpoint,
      payload
    );

    Atlas.loader.hide();

    if (!response.success) {
      Atlas.toast.error(response.message);
      return;
    }

    Atlas.toast.success(response.message);

    setTimeout(() => {
      onSuccess(response);
    }, 1500);

  } catch (error) {
    Atlas.loader.hide();
    Atlas.toast.error(
      'An unexpected error occurred.'
    );

    console.error(error);
  }
};