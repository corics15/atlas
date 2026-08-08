const hidSalesOrderId = document.getElementById('hidSalesOrderId');

const txtSalesOrderNo = document.getElementById('txtSalesOrderNo');
const dtOrderDate = document.getElementById('dtOrderDate');
const txtCreditLimit = document.getElementById('txtCreditLimit');

const selCustomer = document.getElementById('selCustomer');
const selSalesman = document.getElementById('selSalesman');
const selTerms = document.getElementById('selTerms');

const txtSalesOrderRemarks = document.getElementById('txtSalesOrderRemarks');

const tblSalesOrderDetails = document.getElementById('tblSalesOrderDetails');

const btnNewSalesOrder = document.getElementById('btnNewSalesOrder');
const btnSaveSalesOrder = document.getElementById('btnSaveSalesOrder');
const btnEditSalesOrder = document.getElementById('btnEditSalesOrder');
const btnCancelSalesOrder = document.getElementById('btnCancelSalesOrder');
const btnRefreshSalesOrder = document.getElementById('btnRefreshSalesOrder');
const btnPrintSalesOrder = document.getElementById('btnPrintSalesOrder');
const btnPostSalesOrder = document.getElementById('btnPostSalesOrder');
const btnCreateDeliveryReceipt = document.getElementById('btnCreateDeliveryReceipt');

let isDirty = false;
let isLoading = true;
let isEditMode = (hidSalesOrderId?.value) ? true : false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkSalesOrder',
    selectAll: '#chkSelectAllSalesOrder',
  });

  /*** dirty tracking */
  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('so-qty') ||
      e.target.id === `txtSalesOrderRemarks`
    ) {
      markDirty();
    }
  });
  Atlas.select.onChange('#selCustomer', (option) => {
    $('#selSalesman').val(option.dataset.salesmanId).trigger('change');
    $('#selTerms').val(option.dataset.termsId).trigger('change');
    txtCreditLimit.value = Atlas.format.amount(option.dataset.creditLimit);
    markDirty()
  });
  Atlas.select.onChange('#selSaleman', () => markDirty());
  /*** end dirty tracking */

  /*** product finder event */
  document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-product-finder')) {
      const row = e.target.closest('tr');
      Atlas.productFinder.show(row);
    }
  });

  /*** scanner barcode event */
  document.addEventListener('keydown', async (e) => {
    console.log(e.target.classList)
    if (!e.target.classList.contains('atlas-barcode')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();

    const row = e.target.closest('tr');
    await Atlas.productFinder.lookup(row, e.target.value);
    markDirty();
  });

  /*** new */
  btnNewSalesOrder?.addEventListener('click', () => Atlas.page.redirect(`sales-orders/create/`));

  /*** save */
  btnSaveSalesOrder?.addEventListener('click', async () => {

    if (!validateSalesOrder()) {
      return;
    }

    btnSaveSalesOrder.disabled = true;

    try {
      const salesOrder = {
        id: hidSalesOrderId.value,
        so_no: txtSalesOrderNo.value,
        order_date: dtOrderDate.value,
        customer_id: selCustomer.value,
        salesman_id: selSalesman.value,
        terms_id: selTerms.value,
        credit_limit: Atlas.format.parseNumber(txtCreditLimit.value) || 0,
        remarks: txtSalesOrderRemarks.value,
        details: []
      };

      document.querySelectorAll('#tblSalesOrderDetails tr').forEach(row => {

        if (!row.dataset.productId) {
          return;
        }

        salesOrder.details.push({
          product_id: Number(row.dataset.productId),
          qty: Number(
            row.querySelector('.so-qty').value
          )
        });
      });

      const result = await Atlas.ajax.post(
        'sales-orders/save',
        salesOrder
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      hidSalesOrderId.value = result.data.sales_order_id;
      isEditMode = true;
      isDirty = false;
      setTimeout(() => Atlas.page.redirect(`sales-orders/edit/${result.data.sales_order_id}`), 1500);
    }
    finally {
      btnSaveSalesOrder.disabled = false;
    }
  });

  /*** edit */
  btnEditSalesOrder?.addEventListener('click', () => {
    const id = getSelectedSalesOrderId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`sales-orders/edit/${id}`);
  });

  /*** post */
  btnPostSalesOrder?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not yet saved yet.');
        return;
      } else if (window.salesOrderId) {
        ids = [window.salesOrderId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Order');
        return false;
      }
    }

    btnPostSalesOrder.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'sales-orders/post',
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
      btnPostSalesOrder.disabled = false;
    }
  });

  /*** create delivery receipt */
  btnCreateDeliveryReceipt?.addEventListener('click', () => {
    const id = getSelectedSalesOrderId();
    let row = document.querySelector(`tr[data-id="${id}"]`);

    if (!row) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not saved yet.');
        return;
      }

      if (!window.salesOrderId) {
        Atlas.toast.warning('Please select a Sales Order.');
        return;
      }

      const status = window.status;
      const remainingItems = window.remainingItems;

      if (status !== 'POSTED') {
        Atlas.toast.warning('Only POSTED Sales Orders can create a Delivery Receipt.');
        return;
      }

      if (Atlas.format.integer(remainingItems) === 0) {
        Atlas.toast.warning('This Sales Order is already fully delivered.');
        return;
      }

      Atlas.page.redirectRemember(`delivery-receipts/create/${window.salesOrderId}`);
      return;
    }

    if (row.dataset.status !== 'POSTED') {
      Atlas.toast.warning('Only POSTED Sales Orders can create a Delivery Receipt.');
      return;
    }

    if (Atlas.format.integer(row.dataset.remainingItems) === 0) {
      Atlas.toast.warning('This Sales Order is already fully delivered.');
      return;
    }

    Atlas.page.redirectRemember(`delivery-receipts/create/${id}`);
  });

  /*** print */
  btnPrintSalesOrder?.addEventListener('click', printSalesOrder);

  /*** cancel */
  btnCancelSalesOrder?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not yet saved yet.');
        return;
      } else if (window.salesOrderId) {
        ids = [window.salesOrderId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Order');
        return false;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Sales Order(s)?`,
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
      'sales-orders/cancel',
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

  /*** refresh */
  btnRefreshSalesOrder?.addEventListener('click', () => Atlas.page.redirect(`sales-orders`));

  /*** remove row event on details table */
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-delete-row');

    if (!btn) {
      return;
    }
    const row = btn.closest('tr');
    removeDetailRow(row);
  });

  /*** add new row when in edit mode */
  if (isEditMode) {
    const lastRow = tblSalesOrderDetails.lastElementChild;

    if (lastRow?.dataset.productId) {
      addDetailRow(false);
    }
  }

  isDirty = false;
  isLoading = false;

});

if (selCustomer) {
  Atlas.select.init('#selCustomer');
}
if (selSalesman) {
  Atlas.select.init('#selSalesman');
}
if (selTerms) {
  Atlas.select.init('#selTerms');
}

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
  e.returnValue = '';
});

const addDetailRow = (markAsDirty = true) => {
  const tbody = document.getElementById('tblSalesOrderDetails');
  tbody.insertAdjacentHTML('beforeend', createDetailRow());
  renumberRows();
  /*** let product finder determine which element to focus on */
  // setTimeout(() => tbody.lastElementChild.querySelector('.so-barcode').focus(), 500);
  if (markAsDirty) {
    markDirty();
  }
};

const createDetailRow = () => {
  return `
    <tr>
      <td class="so-row-no text-center"></td>
      <td>
        <div class="input-group">
          <input type="text" class="form-control form-control-sm so-barcode atlas-barcode" placeholder="Barcode">
          <div class="input-group-append">
            <button
              type="button"
              class="btn btn-sm btn-outline-warning btn-product-finder">
              <i class="fas fa-search font-smr"></i>
            </button>
          </div>
        </div>
      </td>
      <td class="so-description"></td>
      <td class="so-uom text-center"></td>
      <td class="so-available text-right">-</td>
      <td></td>
      <td></td>
      <td>
        <input
          type="number"
          step="any"
          class="form-control form-control-sm text-right so-qty"
          value="">
      </td>
      <td class="text-center">
        <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
      </td>
    </tr>
  `;
};

const populateProductRow = (row, product) => {
  /*** check duplicate rows */
  const duplicate = [...tblSalesOrderDetails.rows].find(r =>
    r !== row &&
    Atlas.format.integer(r.dataset.productId) === Atlas.format.integer(product.id)
  );
  if (duplicate) {
    Atlas.toast.warning('Product already exists.');
    duplicate.querySelector('.so-qty')?.focus();
    return;
  }
  /*** end check */

  row.dataset.productId = product.id;
  row.querySelector('.so-barcode').value = product.barcode;
  row.querySelector('.so-description').textContent = product.description;
  row.querySelector('.so-uom').textContent = product.uom;
  row.querySelector('.so-available').textContent = Atlas.format.integer(product.qty_on_hand);
  setTimeout(() => row.querySelector('.so-qty').focus(), 500);
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

const renumberRows = () => {
  document.querySelectorAll('#tblSalesOrderDetails tr').forEach((row, index) => {
    row.querySelector('.so-row-no').textContent = `${index + 1}.`;
  });
};

const validateSalesOrder = () => {
  const rows = document.querySelectorAll('#tblSalesOrderDetails tr');

  let hasProduct = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];

    if (!row.dataset.productId) {
      continue;
    }

    hasProduct = true;

    const qty = Number(row.querySelector('.so-qty').value || 0);
    if (qty <= 0) {
      Atlas.toast.warning(
        `Invalid quantity on row ${i + 1}.`
      );
      setTimeout(() => row.querySelector('.so-qty').focus(), 500);
      return false;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning(
      'Please add at least one product.'
    );
    setTimeout(() => document.querySelector('.so-barcode')?.focus(), 500);
    return false;
  }

  if (!selCustomer.value) {
    Atlas.toast.warning('Please select a customer.');
    $('#selCustomer').select2('open');
    return false;
  }

  if (!selSalesman.value) {
    Atlas.toast.warning('Please select a salesman.');
    $('#selSalesman').select2('open');
    return false;
  }

  return true;
};

const removeDetailRow = (row) => {
  const tbody = tblSalesOrderDetails;
  /*** keep one empty row at all times */
  if (tbody.children.length === 1) {
    row.dataset.productId = '';
    row.querySelector('.so-barcode').value = '';
    row.querySelector('.so-description').textContent = '';
    row.querySelector('.so-available').textContent = '-';
    row.querySelector('.so-qty').value = '';
    row.querySelector('.so-uom').textContent = '';
    setTimeout(() => row.querySelector('.so-barcode').focus(), 500);
  } else {
    row.remove();
  }
  renumberRows();
  markDirty();
};

const getSelectedSalesOrderId = () => {
  const checkedRow = Atlas.table.selected();

  if (checkedRow.length === 0) {
    if (window.salesOrderId === 0) {
      Atlas.toast.warning('New Sales Order, not saved yet.');
      return null;
    } else if (window.salesOrderId) {
      return Atlas.format.integer(window.salesOrderId, 10);
    } else {
      Atlas.toast.warning('Please select a Sales Order.');
      return null;
    }
  }

  if (checkedRow.length > 1) {
    Atlas.toast.warning(
      'Please select only one Sales Order.'
    );

    return null;
  }

  return checkedRow[0].value;
};

const printSalesOrder = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.salesOrderId === 0) {
      Atlas.toast.warning('New Sales Order, not yet saved yet.');
      return;
    } else if (window.salesOrderId) {
      ids = [window.salesOrderId];
    } else {
      Atlas.toast.warning('Please select at least one Sales Order');
      return false;
    }
  }

  Atlas.print.post('sales-orders/print', ids);
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};