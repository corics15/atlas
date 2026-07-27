const hidSalesOrderId = document.getElementById('hidSalesOrderId');

const txtSalesOrderNo = document.getElementById('txtSalesOrderNo');
const dtOrderDate = document.getElementById('dtOrderDate');

const selCustomer = document.getElementById('selCustomer');
const selSalesman = document.getElementById('selSalesman');
const selTerms = document.getElementById('selTerms');

const txtSalesOrderRemarks = document.getElementById('txtSalesOrderRemarks');

const tblSalesOrderDetails = document.getElementById('tblSalesOrderDetails');

const btnSaveSalesOrder = document.getElementById('btnSaveSalesOrder');
const btnEditSalesOrder = document.getElementById('btnEditSalesOrder');
const btnCancelSalesOrder = document.getElementById('btnCancelSalesOrder');
const btnRefreshSalesOrder = document.getElementById('btnRefreshSalesOrder');
const btnPrintSalesOrder = document.getElementById('btnPrintSalesOrder');

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
    console.log(option.dataset)
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

  /*** save sales order */
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
        'sales_orders/save',
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
      setTimeout(() => Atlas.page.redirect(`sales_orders/edit/${result.data.sales_order_id}`), 1500);
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

    Atlas.page.redirect(
      `sales_orders/edit/${id}`
    );
  });

  /*** print */
  btnPrintSalesOrder?.addEventListener('click', printSalesOrder);

  /*** cancel */
  btnCancelSalesOrder?.addEventListener('click', async () => {
    const ids = Atlas.table.selectedIds();

    if (!ids.length) {
      Atlas.toast.warning('Please select at least one Sales Order.');
      return;
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
      'sales_orders/cancel',
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
  btnRefreshSalesOrder?.addEventListener('click', () => {
    Atlas.page.refresh();
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
  setTimeout(() => tbody.lastElementChild.querySelector('.so-barcode').focus(), 500);
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
          <input
            type="text"
            class="form-control form-control-sm so-barcode"
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

      <td class="so-description"></td>
      <td class="so-available text-right">-</td>

      <td>
        <input
          type="number"
          step="any"
          class="form-control form-control-sm text-right so-qty"
          value="">
      </td>
      <td class="so-uom text-center"></td>
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
    Number(r.dataset.productId) === Number(product.id)
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
  row.querySelector('.so-available').textContent = '-';
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
    row.querySelector('.so-available').textContent = '0.00';
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
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select a Sales Order.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Sales Order.'
    );

    return null;
  }

  return checked[0].value;
};

const printSalesOrder = () => {
  const ids = Atlas.table.selectedIds();

  if (ids.length === 0) {
    Atlas.toast.warning(
      'Please select at least one Sales Order.'
    );
    return;
  }

  Atlas.print.post(
    'sales_orders/print',
    ids
  );
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};