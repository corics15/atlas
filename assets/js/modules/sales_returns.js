const btnSaveSalesReturn = document.getElementById('btnSaveSalesReturn');
const btnPostSalesReturn = document.getElementById('btnPostSalesReturn');
const btnEditSalesReturn = document.getElementById('btnEditSalesReturn');
const btnRefreshSalesReturn = document.getElementById('btnRefreshSalesReturn');
const btnCancelSalesReturn = document.getElementById('btnCancelSalesReturn');
const btnPrintSalesReturn = document.getElementById('btnPrintSalesReturn');

const txtCreditLimit = document.getElementById('txtCreditLimit');

const hidSalesReturnId = document.getElementById('hidSalesReturnId');
const hidSalesInvoiceId = document.getElementById('hidSalesInvoiceId');

let isDirty = false;
let isLoading = true;
let isEditMode = hidSalesReturnId?.value ? true : false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkSalesReturn',
    selectAll: '#chkSelectAllSalesReturn',
  });

  /*** dirty tracking + live totals */
  document.addEventListener('input', (e) => {
    if (e.target.classList.contains('so-qty')) {
      calculateSalesReturnTotals();
      markDirty();
      return;
    }
    if (e.target.id === 'txtSalesReturnRemarks') {
      markDirty();
    }

  });

  /*** edit */
  btnEditSalesReturn?.addEventListener('click', () => {
    const id = getSelectedId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`sales-returns/edit/${Atlas.id.encode(id)}`);
  });

  /*** save */
  btnSaveSalesReturn?.addEventListener('click', async () => {

    if (!validateSalesReturn()) {
      return;
    }

    btnSaveSalesReturn.disabled = true;

    try {

      const salesReturn = {
        id: Atlas.format.parseNumber(document.getElementById('hidSalesReturnId').value),
        sales_invoice_id: Atlas.format.parseNumber(document.getElementById('hidSalesInvoiceId').value),
        return_date: document.getElementById('dtSalesReturnDate').value,
        customer_id: Atlas.format.parseNumber(document.getElementById('selCustomer').value),
        salesman_id: Atlas.format.parseNumber(document.getElementById('selSalesman').value),
        terms_id: Atlas.format.parseNumber(document.getElementById('selTerms').value),
        credit_limit: Atlas.format.parseNumber(document.getElementById('txtCreditLimit').value),
        remarks: document.getElementById('txtSalesReturnRemarks').value,
        details: []
      };

      document.querySelectorAll('#tblSalesReturnDetails tr').forEach(row => {

        if (!row.dataset.productId) {
          return;
        }

        const qty = Atlas.format.parseNumber(row.querySelector('.so-qty').value);

        if (qty <= 0) {
          return;
        }

        salesReturn.details.push({
          sales_invoice_detail_id: Atlas.format.parseNumber(row.dataset.salesInvoiceDetailId),
          product_id: Atlas.format.parseNumber(row.dataset.productId),
          uom_id: Atlas.format.parseNumber(row.dataset.uomId),
          conversion_factor: Atlas.format.parseNumber(row.dataset.conversionFactor),
          qty: qty
        });

      });

      const result = await Atlas.ajax.post(
        'sales-returns/save',
        salesReturn
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      document.getElementById('hidSalesReturnId').value = result.data.sales_return_id;
      setTimeout(() => Atlas.page.redirect(`sales-returns/edit/${Atlas.id.encode(result.data.sales_return_id)}`), 1500);
      isEditMode = true;
      isDirty = false;

    }
    finally {
      btnSaveSalesReturn.disabled = false;
    }

  });

  /*** post */
  btnPostSalesReturn?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesReturnId === 0) {
        Atlas.toast.warning('New Sales Return, not saved yet.');
        return false;
      } else if (window.salesReturnId) {
        ids = [window.salesReturnId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Return');
        return false;
      }
    }

    const result = await Atlas.dialog.confirm(
      'Confirm Action',
      `<div class="text-brown text-center">
        <p>Inventory quantities will be updated.<br>
        This action cannot be undone.</p>
        <p class="font-weight-500 text-danger">Post Delivery Receipt?</p>
      </div>`
    );

    if (!result) {
      return;
    }

    btnPostSalesReturn.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'sales-returns/post',
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
      btnPostSalesReturn.disabled = false;
    }
  });

  /*** cancel */
  btnCancelSalesReturn?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesReturnId === 0) {
        Atlas.toast.warning('New Sales Return, not saved yet.');
        return false;
      } else if (window.salesReturnId) {
        ids = [window.salesReturnId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Return');
        return false;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Sales Return(s)?`,
      text: 'Please provide the reason for cancellation.',
      inputPlaceholder: 'Enter cancellation reason...',
      required: false,
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    const result = await Atlas.ajax.post(
      'sales-returns/cancel',
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
  btnPrintSalesReturn?.addEventListener('click', printSalesReturn);

  /*** refresh */
  btnRefreshSalesReturn?.addEventListener('click', () => Atlas.page.redirect(`sales-returns`));

  /*** initialize Sales Return totals */
  calculateSalesReturnTotals();

  isDirty = false;
  isLoading = false;

});

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
  e.returnValue = '';
});

const calculateSalesReturnTotals = () => {
  let grossAmount = 0;
  let discountAmount = 0;

  document.querySelectorAll('#tblSalesReturnDetails tr[data-product-id]').forEach(row => {

    const qty = Atlas.format.parseNumber(row.querySelector('.so-qty')?.value || 0);
    const unitPrice = Atlas.format.parseNumber(row.dataset.unitPrice || 0);
    const rowDiscount = Atlas.format.parseNumber(row.dataset.discountAmount || 0);

    grossAmount += qty * unitPrice;
    discountAmount += rowDiscount;
  });

  const discountedAmount = Math.max(0, grossAmount - discountAmount);
  const vatMode = window.salesReturnVatMode || '';
  const vatRate = window.salesReturnVatRate || 0;

  const vatDecimal = vatRate / 100;

  let subtotal = 0;
  let vatAmount = 0;
  let totalAmount = 0;

  /*** VAT inclusive */
  if (vatMode === 'INCLUSIVE') {
    totalAmount = discountedAmount;

    if (vatDecimal > 0) {
      subtotal = totalAmount / (1 + vatDecimal);
      vatAmount = totalAmount - subtotal;
    } else {
      subtotal = totalAmount;
    }
  }

  /*** VAT exclusive */
  else if (vatMode === 'EXCLUSIVE') {
    subtotal = discountedAmount;
    vatAmount = subtotal * vatDecimal;
    totalAmount = subtotal + vatAmount;
  }

  document.getElementById('srGrossAmount').textContent = Atlas.format.amount(grossAmount);
  document.getElementById('srDiscountAmount').textContent = Atlas.format.amount(discountAmount);
  document.getElementById('srSubtotal').textContent = Atlas.format.amount(subtotal);
  document.getElementById('srVatRateLabel').textContent = `${vatRate.toFixed(2)}%`;
  document.getElementById('srVatAmount').textContent = Atlas.format.amount(vatAmount);
  document.getElementById('srTotalAmount').textContent = Atlas.format.amount(totalAmount);
};

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

const validateSalesReturn = () => {
  const rows = document.querySelectorAll('#tblSalesReturnDetails tr');

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

const printSalesReturn = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.salesReturnId === 0) {
      Atlas.toast.warning('New Sales Return, not saved yet.');
      return;
    } else if (window.salesReturnId) {
      ids = [window.salesReturnId];
    } else {
      Atlas.toast.warning('Please select at least one Sales Return');
      return;
    }
  }

  Atlas.print.post(
    'sales-returns/print',
    ids
  );
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};