const btnSaveDeliveryReceipt = document.getElementById('btnSaveDeliveryReceipt');
const btnEditDeliveryReceipt = document.getElementById('btnEditDeliveryReceipt');
const btnPostDeliveryReceipt = document.getElementById('btnPostDeliveryReceipt');
const btnRefreshDeliveryReceipt = document.getElementById('btnRefreshDeliveryReceipt');
const btnCancelDeliveryReceipt = document.getElementById('btnCancelDeliveryReceipt');
const btnCreateSalesInvoice = document.getElementById('btnCreateSalesInvoice');
const btnPrintDeliveryReceipt = document.getElementById('btnPrintDeliveryReceipt');
const btnDownloadDRExcel = document.getElementById('btnDownloadDRExcel');

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
          uom_id: Atlas.format.parseNumber(row.dataset.uomId),
          conversion_factor: Atlas.format.parseNumber(row.dataset.conversionFactor),
          qty: Atlas.format.parseNumber(row.querySelector('.dr-deliver-qty').value),
          description: row.dataset.description,
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
      setTimeout(() => Atlas.page.redirect(`delivery-receipts/edit/${Atlas.id.encode(result.data.delivery_receipt_id)}`), 1200);

      isEditMode = true;
      isDirty = false;

    } finally {
      btnSaveDeliveryReceipt.disabled = false;
    }

  });

  /*** edit */
  btnEditDeliveryReceipt?.addEventListener('click', () => {
    const id = getSelectedDeliveryReceiptId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`delivery-receipts/edit/${Atlas.id.encode(id)}`);
  });

  /*** post */
  btnPostDeliveryReceipt?.addEventListener('click', async () => {
    let id = getSelectedDeliveryReceiptId();

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
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.deliveryReceiptId === 0) {
        Atlas.toast.warning('New Delivery Receipt, not saved yet.');
        return false;
      } else if (window.deliveryReceiptId) {
        ids = [window.deliveryReceiptId];
      } else {
        Atlas.toast.warning('Please select at least one Delivery Receipt');
        return false;
      }
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

  /*** excel download */
  btnDownloadDRExcel?.addEventListener('click', () => {
    Atlas.excel.download(
      document.getElementById('tblDRList'),
      {
        title: 'Delivery Receipts List',
        generatedBy: Atlas.config.userName,
        fileName: 'delivery-receipts-list',
        sheetName: 'DRList',
        /*** start with 0, index based */
        totals: [
          {
            column: 4,
            value: 'TOTAL'
          },
          {
            column: 5,
            value: window.itemCount || 0,
            type: 'n',
            format: '#,##0'
          },
          {
            column: 6,
            value: window.totalAmount || 0,
            type: 'n',
            format: '#,##0.00'
          },
        ]
      }
    );
  });

  /*** create sales invoice */
  btnCreateSalesInvoice?.addEventListener('click', () => {
    let ids = Atlas.table.selectedIds();
    let row = null;

    if (ids && ids.length === 1) {
      row = document.querySelector(`tr[data-id="${ids[0]}"]`);
    }

    if (!row) {
      if (window.deliveryReceiptId === 0) {
        Atlas.toast.warning('New Delivery Receipt, not saved yet.');
        return;
      }

      if (!window.deliveryReceiptId) {
        Atlas.toast.warning('Please select one Delivery Receipt.');
        return;
      }

      // Use global values
      const status = window.status;

      if (status !== 'POSTED') {
        Atlas.toast.warning('Only POSTED Delivery Receipts can be invoiced.');
        return;
      }

      Atlas.page.redirect(`sales-invoices/create/${Atlas.id.encode(window.deliveryReceiptId)}`);
      return;
    }

    if (row.dataset.status !== 'POSTED') {
      Atlas.toast.warning('Only POSTED Delivery Receipts can be invoiced.');
      return;
    }

    Atlas.page.redirect(`sales-invoices/create/${Atlas.id.encode(ids[0])}`);
  });

  /*** refresh */
  btnRefreshDeliveryReceipt?.addEventListener('click', () => Atlas.page.redirect(`delivery-receipts`));

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
    if (window.deliveryReceiptId === 0) {
      Atlas.toast.warning('New Delivery Receipt, not saved yet.');
      return null;
    } else if (window.deliveryReceiptId) {
      return window.deliveryReceiptId;
    } else {
      Atlas.toast.warning('Please select a Delivery Receipt.');
      return null;
    }
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
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.deliveryReceiptId === 0) {
      Atlas.toast.warning('New Delivery Receipt, not saved yet.');
      return;
    } else if (window.deliveryReceiptId) {
      ids = [window.deliveryReceiptId];
    } else {
      Atlas.toast.warning('Please select at least one Delivery Receipt');
      return;
    }
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