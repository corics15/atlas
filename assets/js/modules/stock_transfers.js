const txtTransferNo = document.getElementById('txtTransferNo');
const dtTransferDate = document.getElementById('dtTransferDate');

const selFromBranch = document.getElementById('selFromBranch');
const selToBranch = document.getElementById('selToBranch');

const txtStockTransferRemarks = document.getElementById('txtStockTransferRemarks');
const tblStockTransferDetails = document.getElementById('tblStockTransferDetails');

const btnSaveStockTransfer = document.getElementById('btnSaveStockTransfer');
const hidStockTransferId = document.getElementById('hidStockTransferId');

const btnNewStockTransfer = document.getElementById('btnNewStockTransfer');
const btnEditStockTransfer = document.getElementById('btnEditStockTransfer');
const btnRefreshStockTransfer = document.getElementById('btnRefreshStockTransfer');
const btnCancelStockTransfer = document.getElementById('btnCancelStockTransfer');
const btnPrintStockTransfer = document.getElementById('btnPrintStockTransfer');
const btnPostStockTransfer = document.getElementById('btnPostStockTransfer');

let isEditMode = (hidStockTransferId?.value) ? true : false;
let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', async () => {
  isLoading = true;

  if (selFromBranch && selToBranch) {
    Atlas.select.init('#selFromBranch');
    Atlas.select.init('#selToBranch');

    refreshBranchOptions();
  }

  Atlas.table.init({
    checkbox: '.chkStockTransfer',
    selectAll: '#chkSelectAllStockTransfer',
  });

  /*** always select main branch */
  $('#selFromBranch').val(1).trigger('change.select2');

  /*** event for product search on selected */
  document.addEventListener('keydown', async (e) => {
    if (!e.target.classList.contains('st-barcode')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();
    const barcode = e.target.value.trim();
    if (!barcode) {
      return;
    }

    const result = await Atlas.ajax.get(`product-finder/barcode/${encodeURIComponent(barcode)}`)
    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    const row = e.target.closest('tr');
    populateProductRow(row, result.data);
    markDirty();
  });

  /*** product finder event */
  document.addEventListener('click', e => {
    if (!e.target.closest('.btn-product-finder')) {
      return;
    }

    const row = e.target.closest('tr');
    Atlas.productFinder.show(row);
  });

  /*** remove row event on details table */
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-delete-row');

    if (!btn) {
      return;
    }
    const row = btn.closest('tr');
    removeDetailRow(row);
  });

  /*** save stock transfer */
  btnSaveStockTransfer?.addEventListener('click', async () => {

    if (!validateStockTransfer()) {
      return;
    }

    btnSaveStockTransfer.disabled = true;

    try {
      const stockTransfer = {
        id: hidStockTransferId.value,
        transfer_no: txtTransferNo.value,
        transfer_date: dtTransferDate.value,
        from_branch_id: Number(selFromBranch.value),
        to_branch_id: Number(selToBranch.value),
        remarks: txtStockTransferRemarks.value,
        details: []
      };

      document.querySelectorAll('#tblStockTransferDetails tr').forEach(row => {

        if (!row.dataset.productId) {
          return;
        }

        stockTransfer.details.push({
          product_id: Number(row.dataset.productId),
          qty: Number(
            row.querySelector('.st-qty').value
          )
        });
      });

      const result = await Atlas.ajax.post(
        'stock-transfers/save',
        stockTransfer
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      hidStockTransferId.value = result.data.stock_transfer_id;
      isEditMode = true;
      isDirty = false;
      setTimeout(() => Atlas.page.redirect(`stock-transfers/edit/${result.data.stock_transfer_id}`), 1500);
    }
    finally {
      btnSaveStockTransfer.disabled = false;
    }
  });

  /*** new */
  btnNewStockTransfer?.addEventListener('click', () => Atlas.page.redirect(`stock-transfers/create`));

  /*** edit */
  btnEditStockTransfer?.addEventListener('click', () => {
    const id = getSelectedStockTransferId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`stock-transfers/edit/${id}`);
  });

  /*** post */
  btnPostStockTransfer?.addEventListener('click', async () => {
    const ids = Atlas.table.selectedIds();

    if (ids.length === 0) {
      Atlas.toast.warning('Please select at least one Stock Transfer')
      return false;
    }

    if (ids.length > 1) {
      Atlas.toast.warning('Please select only one Stock Transfer')
      return false;
    }

    const result = await Atlas.dialog.confirm(
      'Confirm Action',
      `<div class="text-brown text-center">
        <p>Inventory quantities will be updated.<br>
        This action cannot be undone.</p>
        <p class="font-weight-500 text-danger">Post Stock Transfer?</p>
      </div>`
    );

    if (!result) {
      return;
    }

    btnPostStockTransfer.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'stock-transfers/post',
        {
          ids: ids
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);
      setTimeout(() => Atlas.page.refresh(), 1500);

    } finally {
      btnPostStockTransfer.disabled = false;
    }
  });

  /*** cancel */
  btnCancelStockTransfer?.addEventListener('click', async () => {
    const ids = Atlas.table.selectedIds();

    if (!ids.length) {
      Atlas.toast.warning('Please select at least one Stock Transfer.');
      return;
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Stock Transfer(s)?`,
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

    const result = await Atlas.ajax.post(
      'stock-transfers/cancel',
      {
        ids: ids,
        cancel_reason: reason
      }
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.refresh(), 1500);
  });

  /*** print */
  btnPrintStockTransfer?.addEventListener('click', printStockTransfer);

  /*** refresh */
  btnRefreshStockTransfer?.addEventListener('click', () => {
    Atlas.page.refresh();
  });

  /*** scanner barcode event */
  document.addEventListener('keydown', async (e) => {
    if (!e.target.classList.contains('atlas-barcode')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();

    const row = e.target.closest('tr');

    await Atlas.productFinder.lookup(
      row,
      e.target.value
    );

    markDirty();
  });

  selFromBranch?.addEventListener('change', () => {
    if (isLoading) {
      return;
    }

    refreshBranchOptions();
    markDirty();
  });

  selToBranch?.addEventListener('change', () => {
    if (isLoading) {
      return;
    }

    refreshBranchOptions();
    markDirty();
  });

  /*** add row when in edit mode */
  if (isEditMode) {
    const lastRow = tblStockTransferDetails.lastElementChild;

    if (lastRow?.dataset.productId) {
      addDetailRow(false);
    }
  }

  refreshBranchOptions();
  isDirty = false;
  isLoading = false;
});

window.addEventListener('beforeunload', (e) => {
  if (!isDirty) {
    return;
  }
  e.preventDefault();
  e.returnValue = '';
});

const createDetailRow = () => {
  return `
    <tr>
      <td class="st-row-no text-center"></td>
      <td>
        <div class="input-group">
          <input
            type="text"
            class="form-control form-control-sm st-barcode atlas-barcode"
            placeholder="Barcode">
          <div class="input-group-append">
            <button
              type="button"
              class="btn btn-sm btn-outline-warning btn-product-finder">
              <i class="fas fa-search font-smr"></i>
            </button>
          </div>
        </div>
      </td>

      <td class="st-description"></td>
      <td class="st-available text-right">-</td>

      <td>
        <input
          type="number"
          step="any"
          class="form-control form-control-sm text-right st-qty"
          value="">
      </td>
      <td class="st-uom text-center"></td>
      <td class="text-center">
        <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
      </td>
    </tr>
  `;
};

const addDetailRow = (markAsDirty = true) => {
  const tbody = document.getElementById('tblStockTransferDetails');
  tbody.insertAdjacentHTML('beforeend', createDetailRow());
  renumberRows();
  // setTimeout(() => tbody.lastElementChild.querySelector('.st-barcode').focus(), 500);
  if (markAsDirty) {
    markDirty();
  }
};

const populateProductRow = (row, product) => {
  row.dataset.productId = product.id;
  row.querySelector('.st-barcode').value = product.barcode;
  row.querySelector('.st-description').textContent = product.description;
  row.querySelector('.st-uom').textContent = product.uom;
  row.querySelector('.st-available').textContent = Atlas.format.parseNumber(product.qty_on_hand);
  setTimeout(() => row.querySelector('.st-qty').focus(), 500);
  markDirty();

  /*** check if there's already an empty row */
  const nextRow = row.nextElementSibling;
  if (
    !nextRow ||
    nextRow.dataset.productId
  ) {
    addDetailRow();
  }
};

const removeDetailRow = (row) => {
  const tbody = tblStockTransferDetails;
  /*** keep one empty row at all times */
  if (tbody.children.length === 1) {
    row.dataset.productId = '';
    row.querySelector('.st-barcode').value = '';
    row.querySelector('.st-description').textContent = '';
    row.querySelector('.st-available').textContent = '0.00';
    row.querySelector('.st-qty').value = '';
    row.querySelector('.st-uom').textContent = '';
    setTimeout(() => row.querySelector('.st-barcode').focus(), 500);
  } else {
    row.remove();
  }
  renumberRows();
  markDirty();
};

const validateStockTransfer = () => {
  const rows = document.querySelectorAll('#tblStockTransferDetails tr');

  let hasProduct = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];

    if (!row.dataset.productId) {
      continue;
    }

    hasProduct = true;

    const qty = Number(row.querySelector('.st-qty').value || 0);
    if (qty <= 0) {
      Atlas.toast.warning(
        `Invalid transfer quantity on row ${i + 1}.`
      );
      setTimeout(() => row.querySelector('.st-qty').focus(), 500);
      return false;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning(
      'Please add at least one product.'
    );
    setTimeout(() => document.querySelector('.st-barcode')?.focus(), 500);
    return false;
  }

  if (!selFromBranch.value) {
    Atlas.toast.warning('Please select the source branch.');
    $('#selFromBranch').select2('open');
    return false;
  }

  if (!selToBranch.value) {
    Atlas.toast.warning('Please select the destination branch.');
    $('#selToBranch').select2('open');
    return false;
  }

  if (selFromBranch.value === selToBranch.value) {
    Atlas.toast.warning(
      'Source and destination branches must be different.'
    );
    $('#selToBranch').select2('open');
    return false;
  }

  return true;
};

const renumberRows = () => {
  document.querySelectorAll('#tblStockTransferDetails tr').forEach((row, index) => {
    row.querySelector('.st-row-no').textContent = `${index + 1}.`;
  });
};

const getSelectedStockTransferId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select a Stock Transfer.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Stock Transfer.'
    );

    return null;
  }

  return checked[0].value;
};

const refreshBranchOptions = () => {
  if (!selFromBranch || !selToBranch) {
    return;
  }

  [...selToBranch.options].forEach(option => {
    option.disabled =
      option.value !== '' &&
      option.value === selFromBranch.value;
  });

  [...selFromBranch.options].forEach(option => {
    option.disabled =
      option.value !== '' &&
      option.value === selToBranch.value;
  });

  $('#selFromBranch').trigger('change.select2');
  $('#selToBranch').trigger('change.select2');
};

const printStockTransfer = () => {
  const ids = Atlas.table.selectedIds();

  if (ids.length === 0) {
    Atlas.toast.warning(
      'Please select at least one Stock Transfer.'
    );
    return;
  }

  Atlas.print.post(
    'stock-transfers/print',
    ids
  );
};

const markDirty = () => {
  if (isLoading) {
    return;
  }
  isDirty = true;
};