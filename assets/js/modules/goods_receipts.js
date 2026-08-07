const btnSaveGoodsReceipt = document.getElementById('btnSaveGoodsReceipt');
const btnSaveChangesGoodsReceipt = document.getElementById('btnSaveChangesGoodsReceipt');
const tblGoodsReceiptDetails = document.getElementById('tblGoodsReceiptDetails');
const btnPrintGoodsReceipt = document.getElementById('btnPrintGoodsReceipt');
const btnRefreshGoodsReceipt = document.getElementById('btnRefreshGoodsReceipt');
const btnPostGoodsReceipt = document.getElementById('btnPostGoodsReceipt');
const btnCancelGoodsReceipt = document.getElementById('btnCancelGoodsReceipt');
const btnCreatePurchaseReturn = document.getElementById('btnCreatePurchaseReturn');

const hidGoodsReceiptId = document.getElementById('hidGoodsReceiptId');
const txtRemarks = document.getElementById('txtRemarks');

let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', () => {

  /*** save */
  btnSaveGoodsReceipt?.addEventListener('click', saveGoodsReceipt);

  /*** update */
  btnSaveChangesGoodsReceipt?.addEventListener('click', saveChangesGoodsReceipt);

  /*** cancel */
  btnCancelGoodsReceipt?.addEventListener('click', cancelGoodsReceipt);

  /*** print */
  btnPrintGoodsReceipt?.addEventListener('click', printGoodsReceipt);

  /*** post */
  btnPostGoodsReceipt?.addEventListener('click', postGoodsReceipt);

  /*** purchase return */
  btnCreatePurchaseReturn?.addEventListener('click', () => {
    const id = getSelectedGoodsReceiptId();
    const status = Atlas.table.selected()[0]?.closest('tr').dataset.status;

    if (!id) {
      return;
    }

    if (status !== 'POSTED') {
      Atlas.toast.warning('Only POSTED Goods Receipts can be returned.');
      return;
    }
    Atlas.page.redirect(`purchase-returns/create/${id}`);
  });

  /*** refresh */
  btnRefreshGoodsReceipt?.addEventListener('click', () => Atlas.page.redirect(`goods-receipts`));

  Atlas.table.init({
    checkbox: '.chkGoodsReceipt',
    selectAll: '#chkSelectAllGoodsReceipt',
  });

  /*** dirty tracking and validation */
  document.querySelectorAll('.grn-qty').forEach(input => {
    input.addEventListener('input', function () {
      const row = this.closest('tr');
      const qtyOrdered = Number(row.dataset.orderedQty);
      const qtyReceived = Number(this.value);

      if (qtyReceived < 0) {
        Atlas.toast.error('Received quantity cannot be negative.');
        this.value = 0;
        this.focus();
        this.select();
        return;
      }

      if (qtyReceived > qtyOrdered) {
        Atlas.toast.error('Received quantity cannot exceed the ordered quantity.');
        this.value = qtyOrdered;
        this.focus();
        this.select();
        return;
      }
      markDirty();
    });
  });

  txtRemarks?.addEventListener('input', markDirty);
});

/*** check if closing, or navigating away from the page */
window.addEventListener('beforeunload', (event) => {
  if (!isDirty) {
    return;
  }
  event.preventDefault();
  event.returnValue = '';
});

const collectReceiptDetails = () => {
  const details = [];

  tblGoodsReceiptDetails.querySelectorAll('tbody tr').forEach(row => {
    const receiveNow = parseFloat(
      row.querySelector('.grn-receive-now').value || 0
    );

    const remainingQty = parseFloat(row.dataset.remainingQty);
    if (receiveNow > remainingQty) {
      Atlas.toast.error(
        'Receive quantity cannot exceed the remaining quantity.'
      );
      row.querySelector('.grn-receive-now').focus();
      row.querySelector('.grn-receive-now').select();
      throw new Error('Invalid receive quantity.');
    }

    details.push({
      po_detail_id: parseInt(row.dataset.poDetailId),
      product_id: parseInt(row.dataset.productId),
      qty_ordered: parseFloat(row.dataset.orderedQty),
      qty_receive: receiveNow,
      unit_cost: parseFloat(row.dataset.unitCost)
    });
  });

  return details;
};

const saveGoodsReceipt = async () => {
  btnSaveGoodsReceipt.disabled = true;

  try {
    const formData = new FormData();

    formData.append('grn_date', document.getElementById('dtGRNDate').value);
    formData.append('po_id', document.getElementById('hidPurchaseOrderId').value);
    formData.append('supplier_id', document.getElementById('hidSupplierId').value);
    formData.append('remarks', document.getElementById('txtRemarks').value.trim());

    let details;

    try {
      details = collectReceiptDetails();
    } catch (error) {
      return;
    }

    formData.append('details', JSON.stringify(details));

    const result = await Atlas.ajax.post(
      'goods-receipts/save',
      formData
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    isDirty = false;
    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.redirect(`goods-receipts/view/${result.data.goods_receipt_id}`), 1500);

  } finally {
    btnSaveGoodsReceipt.disabled = false;

  }
};

const saveChangesGoodsReceipt = async () => {
  btnSaveChangesGoodsReceipt.disabled = true;

  try {
    const grn = {
      id: Number(hidGoodsReceiptId.value),
      remarks: txtRemarks.value,
      details: []
    };

    document.querySelectorAll('#tblGoodsReceiptDetails tbody tr').forEach(row => {
      if (!row.dataset.grnDetailId) {
        return;
      }

      grn.details.push({
        id: Number(row.dataset.grnDetailId),
        qty_received: Number(row.querySelector('.grn-qty').value)
      });
    });

    if (grn.details.length === 0) {
      Atlas.toast.warning('There are no items to save.');
      return;
    }

    const result = await Atlas.ajax.post(
      'goods-receipts/update',
      grn
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);

    isDirty = false;
    setTimeout(() => Atlas.page.refresh(), 1500);

  } finally {
    btnSaveChangesGoodsReceipt.disabled = false;
  }
}

const postGoodsReceipt = async () => {
  /*** validate at least one received quantity */
  const qtyInputs = document.querySelectorAll('.grn-qty');
  let hasReceivedQty = false;
  qtyInputs.forEach(input => {
    if (Atlas.format.parseNumber(input.value) > 0) {
      hasReceivedQty = true;
    }
  });
  if (!hasReceivedQty) {
    Atlas.toast.warning(
      'Please enter a received quantity (Qty Rcvd) for at least one item before posting.'
    );
    return;
  }
  /*** end validate at least one received quantity */

  const result = await Atlas.dialog.confirm(
    'Confirm Action',
    `<div class="text-brown text-center">
      <p>Inventory quantities will be updated.<br>
      This action cannot be undone.</p>
      <p class="font-weight-500 text-danger">Post Goods Receipt?</p>
    </div>`
  );

  if (!result) {
    return;
  }

  btnPostGoodsReceipt.disabled = true;

  try {
    const response = await Atlas.ajax.post(
      'goods-receipts/post',
      {
        id: window.goodsReceiptId
      }
    );

    if (!response.success) {
      Atlas.toast.error(response.message);
      return;
    }

    Atlas.toast.success(response.message);
    // setTimeout(() => Atlas.page.refresh(), 1500);
  } finally {
    btnPostGoodsReceipt.disabled = false;
  }
};

const cancelGoodsReceipt = async () => {

  if (btnCancelGoodsReceipt.disabled) {
    return;
  }

  const reason = await Atlas.dialog.textarea({
    icon: 'warning',
    title: `Cancel Goods Receipt?`,
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

  btnCancelGoodsReceipt.disabled = true;

  try {
    const response = await Atlas.ajax.post(
      'goods-receipts/cancel',
      {
        id: Atlas.format.parseNumber(hidGoodsReceiptId.value),
        cancel_reason: reason,
      }
    );

    if (!response.success) {
      Atlas.toast.error(response.message);
      return;
    }

    Atlas.toast.success(response.message);
    setTimeout(() => Atlas.page.refresh(), 1500);
  } finally {
    btnCancelGoodsReceipt.disabled = false;
  }
};

const getSelectedGoodsReceiptId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select a Goods Receipt.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Goods Receipt.'
    );

    return null;
  }

  return checked[0].value;
};

const printGoodsReceipt = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.goodsReceiptId) {
      ids = [window.goodsReceiptId];

    } else {
      Atlas.toast.warning('Please select at least one Goods Receipt');
      return;
    }
  }

  Atlas.print.post('goods-receipts/print', ids);
};

const markDirty = () => {
  if (isLoading) return;
  isDirty = true;
}