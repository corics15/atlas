// const btnCreateDeliveryReceipt = document.getElementById('btnCreateDeliveryReceipt');
const btnSaveDeliveryReceipt = document.getElementById('btnSaveDeliveryReceipt');
const btnEditDeliveryReceipt = document.getElementById('btnEditDeliveryReceipt');
const btnNewDeliveryReceipt = document.getElementById('btnNewDeliveryReceipt');
const btnPostDeliveryReceipt = document.getElementById('btnPostDeliveryReceipt');
const btnRefreshDeliveryReceipt = document.getElementById('btnRefreshDeliveryReceipt');
const btnCancelDeliveryReceipt = document.getElementById('btnCancelDeliveryReceipt');
const btnCreateSalesInvoice = document.getElementById('btnCreateSalesInvoice');
const btnPrintDeliveryReceipt = document.getElementById('btnPrintDeliveryReceipt');


const hidSalesOrderId = document.getElementById('hidSalesOrderId');
const hidDeliveryReceiptId = document.getElementById('hidDeliveryReceiptId');
const hidCustomerId = document.getElementById('hidCustomerId');

const txtDeliveryReceiptRemarks = document.getElementById('txtDeliveryReceiptRemarks');
const dtDeliveryDate = document.getElementById('dtDeliveryDate');

let isEditMode = false;
let purchaseOrderId = null;
let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkDeliveryReceipt',
    selectAll: '#chkSelectAllDeliveryReceipt',
  });

  /*** dirty tracking */
  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('dr-deliver-qty') ||
      e.target.id === `txtDeliveryReceiptRemarks`
    ) {
      markDirty();
    }
  });

  /*** save */
  btnSaveDeliveryReceipt?.addEventListener('click', async () => {
    btnSaveDeliveryReceipt.disabled = true;

    try {

      const deliveryReceipt = {
        id: Atlas.format.parseNumber(hidDeliveryReceiptId?.value),
        sales_order_id: Atlas.format.parseNumber(hidSalesOrderId.value),
        remarks: txtDeliveryReceiptRemarks?.value ?? '',
        delivery_date: dtDeliveryDate?.value,
        customer_id: Atlas.format.parseNumber(hidCustomerId?.value),
        details: [],
      };

      document.querySelectorAll('#tblDeliveryReceiptDetails tbody tr').forEach(row => {

        if (!row.dataset.productId) {
          return;
        }

        deliveryReceipt.details.push({
          sales_order_detail_id: Atlas.format.parseNumber(row.dataset.salesOrderDetailId),
          product_id: Atlas.format.parseNumber(row.dataset.productId),
          qty: Atlas.format.parseNumber(row.querySelector('.dr-deliver-qty').value),
        });

      });

      const result = await Atlas.ajax.post(
        'delivery-receipts/save',
        deliveryReceipt
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      hidDeliveryReceiptId.value = result.data.delivery_receipt_id;
      setTimeout(() => Atlas.page.redirect(`delivery-receipts/edit/${result.data.delivery_receipt_id}`), 1200);

      isEditMode = true;
      isDirty = false;

    } finally {
      btnSaveDeliveryReceipt.disabled = false;
    }

  });

  /*** new */
  btnNewDeliveryReceipt?.addEventListener('click', () => Atlas.page.redirect('delivery-receipts/queue'));

  /*** edit */
  btnEditDeliveryReceipt?.addEventListener('click', () => {
    const id = getSelectedDeliveryReceiptId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`delivery-receipts/edit/${id}`);
  });

  /*** post */
  btnPostDeliveryReceipt?.addEventListener('click', async () => {
    const id = getSelectedDeliveryReceiptId();

    if (!id) {
      return;
    }

    const res = await Atlas.dialog.confirm(
      'Confirm Action',
      `<div class="text-brown text-center">
        <p>Inventory quantities will be updated.<br>
        This action cannot be undone.</p>
        <p class="font-weight-500 text-danger">Post Delivery Receipt?</p>
      </div>`
    );

    if (!res) {
      return;
    }

    const result = await Atlas.ajax.post(
      'delivery-receipts/post',
      { id }
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.refresh(), 1200);
  });

  /*** cancel */
  btnCancelDeliveryReceipt?.addEventListener('click', async () => {
    const ids = Atlas.table.selectedIds();

    if (!ids.length) {
      Atlas.toast.warning('Please select at least one Delivery Receipt.');
      return;
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Delivery Receipt(s)?`,
      html: `<div class="text-brown">Please provide the reason for cancellation.<p>Inventory quantities will be updated.<br>
        This action cannot be undone.</p></div>`,
      // inputLabel: 'Cancellation Reason',
      inputPlaceholder: 'Optional cancellation reason...',
      required: false, /*** set to true if you want this to be required */
      requiredMessage: 'Cancellation reason is required.',
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    const result = await Atlas.ajax.post(
      'delivery-receipts/cancel',
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

  /*** create sales invoice */
  btnCreateSalesInvoice?.addEventListener('click', () => {
    const ids = Atlas.table.selectedIds();

    if (ids.length !== 1) {
      Atlas.toast.warning('Please select one Delivery Receipt.');
      return;
    }

    const row = document.querySelector(`tr[data-id="${ids[0]}"]`);

    if (!row) {
      return;
    }

    if (row.dataset.status !== 'POSTED') {
      Atlas.toast.warning('Only POSTED Delivery Receipts can be invoiced.');
      return;
    }

    Atlas.page.redirect(`sales-invoices/create/${ids[0]}`);
  });

  /*** refresh */
  btnRefreshDeliveryReceipt?.addEventListener('click', () => Atlas.page.refresh());

  /*** print */
  btnPrintDeliveryReceipt?.addEventListener('click', printDeliveryReceipt);

});

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
  e.returnValue = '';
});

const getSelectedDeliveryReceiptId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select an item from the list.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one item from the list.'
    );

    return null;
  }

  return checked[0].value;
};

const printDeliveryReceipt = () => {
  const ids = Atlas.table.selectedIds();

  if (ids.length === 0) {
    Atlas.toast.warning('Please select at least one Delivery Receipt.');
    return;
  }

  Atlas.print.post(
    'delivery-receipts/print',
    ids
  );
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};