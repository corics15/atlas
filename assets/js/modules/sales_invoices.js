const btnSaveSalesInvoice = document.getElementById('btnSaveSalesInvoice');
const btnPostSalesInvoice = document.getElementById('btnPostSalesInvoice');
const btnEditSalesInvoice = document.getElementById('btnEditSalesInvoice');
const btnRefreshSalesInvoice = document.getElementById('btnRefreshSalesInvoice');
const btnCancelSalesInvoice = document.getElementById('btnCancelSalesInvoice');
const btnPrintSalesInvoice = document.getElementById('btnPrintSalesInvoice');

const txtCreditLimit = document.getElementById('txtCreditLimit');

const hidSalesOrderId = document.getElementById('hidSalesOrderId');
const hidSalesInvoiceId = document.getElementById('hidSalesInvoiceId');
const hidSalemanId = document.getElementById('hidSalemanId');
const hidCustomerId = document.getElementById('hidCustomerId');
const hidSalesmanId = document.getElementById('hidSalesmanId');
const hidTermsId = document.getElementById('hidTermsId');
const hidCreditLimit = document.getElementById('hidCreditLimit');

let isDirty = false;
let isLoading = true;
let isEditMode = (hidSalesOrderId?.value) ? true : false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkSalesInvoice',
    selectAll: '#chkSelectAllSalesInvoice',
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

  /*** edit */
  btnEditSalesInvoice?.addEventListener('click', () => {
    const id = getSelectedId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`sales-invoices/edit/${Atlas.id.encode(id)}`);
  });

  /*** save */
  btnSaveSalesInvoice?.addEventListener('click', async () => {

    if (!validateSalesInvoice()) {
      return;
    }

    btnSaveSalesInvoice.disabled = true;

    try {
      const salesInvoice = {
        id: hidSalesInvoiceId?.value ?? '',
        sales_order_id: Atlas.format.parseNumber(hidSalesOrderId.value),
        delivery_receipt_id: Atlas.format.parseNumber(hidDeliveryReceiptId.value),
        invoice_date: dtInvoiceDate.value,
        customer_id: Atlas.format.parseNumber(hidCustomerId.value),
        salesman_id: Atlas.format.parseNumber(hidSalesmanId.value),
        terms_id: Atlas.format.parseNumber(hidTermsId.value),
        credit_limit: Atlas.format.parseNumber(hidCreditLimit.value),
        remarks: txtSalesInvoiceRemarks.value,
        details: []
      };

      document.querySelectorAll('#tblSalesOrderDetails tr').forEach(row => {

        if (!row.dataset.productId) {
          return;
        }

        /*** only push rows whose quantity is greater than zero */
        const qty = Atlas.format.parseNumber(row.querySelector('.so-qty').value);
        if (qty > 0) {
          salesInvoice.details.push({
            product_id: Atlas.format.parseNumber(row.dataset.productId),
            uom_id: Atlas.format.parseNumber(row.dataset.uomId),
            conversion_factor: Atlas.format.parseNumber(row.dataset.conversionFactor),
            sales_order_detail_id: Atlas.format.parseNumber(row.dataset.salesOrderDetailId),
            qty: qty
          });
        }
      });

      const result = await Atlas.ajax.post(
        'sales-invoices/save',
        salesInvoice
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      hidSalesInvoiceId.value = result.data.sales_invoice_id;
      isEditMode = true;
      isDirty = false;
      setTimeout(() => Atlas.page.redirect(`sales-invoices/edit/${Atlas.id.encode(result.data.sales_invoice_id)}`), 1500);
    }
    finally {
      btnSaveSalesInvoice.disabled = false;
    }
  });

  /*** post */
  btnPostSalesInvoice?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesInvoiceId === 0) {
        Atlas.toast.warning('New Sales Invoice, not saved yet.');
        return false;
      } else if (window.salesInvoiceId) {
        ids = [window.salesInvoiceId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Invoice');
        return false;
      }
    }

    const result = await Atlas.dialog.confirm(
      'Confirm Action',
      'Post Sales Invoice?'
    );

    if (!result) {
      return;
    }

    btnPostSalesInvoice.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'sales-invoices/post',
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
      btnPostSalesInvoice.disabled = false;
    }
  });

  /*** cancel */
  btnCancelSalesInvoice?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesInvoiceId === 0) {
        Atlas.toast.warning('New Sales Invoice, not saved yet.');
        return false;
      } else if (window.salesInvoiceId) {
        ids = [window.salesInvoiceId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Invoice');
        return false;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Sales Invoice(s)?`,
      text: 'Please provide the reason for cancellation.',
      inputPlaceholder: 'Enter cancellation reason...',
      required: false,
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    const result = await Atlas.ajax.post(
      'sales-invoices/cancel',
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
    setTimeout(() => Atlas.page.refresh(), 1200);
  });

  /*** print */
  btnPrintSalesInvoice?.addEventListener('click', printSalesInvoice);

  /*** create sales return */
  document.getElementById('btnCreateSalesReturn')?.addEventListener('click', () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length !== 1) {
      if (window.salesInvoiceId === 0) {
        Atlas.toast.warning('New Sales Invoice, not saved yet.');
        return;
      }

      if (!window.salesInvoiceId) {
        Atlas.toast.warning('Please select one Sales Invoice.');
        return;
      }

      Atlas.page.redirect(`sales-returns/create/${Atlas.id.encode(window.salesInvoiceId)}`);
      return;
    }
    Atlas.page.redirect(`sales-returns/create/${Atlas.id.encode(ids[0])}`)
  });

  /*** refresh */
  btnRefreshSalesInvoice?.addEventListener('click', () => Atlas.page.redirect(`sales-invoices`));

});

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
  e.returnValue = '';
});

const getSelectedId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select a Sales Invoice.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Sales Invoice.'
    );

    return null;
  }

  return checked[0].value;
};

const validateSalesInvoice = () => {
  const rows = document.querySelectorAll('#tblSalesOrderDetails tr');

  let hasProduct = false;
  let hasQty = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];

    if (!row.dataset.productId) {
      continue;
    }

    hasProduct = true;

    const qty = Atlas.format.integer(row.querySelector('.so-qty').value || 0);
    if (qty < 0) {
      Atlas.toast.warning(`Invalid quantity on row ${i + 1}.`);
      setTimeout(() => row.querySelector('.so-qty').focus(), 500);
      return false;
    }

    if (qty > 0) {
      hasQty = true;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning('Please add at least one product.');
    return false;
  }

  if (!hasQty) {
    Atlas.toast.warning('Please enter a quantity for at least one item.');
    return false;
  }

  return true;
};

const printSalesInvoice = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.salesInvoiceId === 0) {
      Atlas.toast.warning('New Sales Invoice, not saved yet.');
      return;
    } else if (window.salesInvoiceId) {
      ids = [window.salesInvoiceId];
    } else {
      Atlas.toast.warning('Please select at least one Sales Invoice');
      return;
    }
  }

  Atlas.print.post(
    'sales-invoices/print',
    ids
  );
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};