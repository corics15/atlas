const btnSaveGoodsReceipt = document.getElementById('btnSaveGoodsReceipt');
const btnSaveChangesGoodsReceipt = document.getElementById('btnSaveChangesGoodsReceipt');
const tblGoodsReceiptDetails = document.getElementById('tblGoodsReceiptDetails');
const btnPrintGoodsReceipt = document.getElementById('btnPrintGoodsReceipt');
const btnRefreshGoodsReceipt = document.getElementById('btnRefreshGoodsReceipt');
const btnPostGoodsReceipt = document.getElementById('btnPostGoodsReceipt');
const btnCancelGoodsReceipt = document.getElementById('btnCancelGoodsReceipt');

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

  /*** refresh */
  btnRefreshGoodsReceipt?.addEventListener('click', () => Atlas.page.refresh());

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
      'goods_receipts/save',
      formData
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.redirect(`goods_receipts/view/${result.data.goods_receipt_id}`), 1500);

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
      'goods_receipts/update',
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
  const result = await Atlas.dialog.confirm(
    'Post Goods Receipt?',
    'Inventory quantities will be updated. This action cannot be undone.'
  );

  if (!result) {
    return;
  }

  btnPostGoodsReceipt.disabled = true;

  try {
    const response = await Atlas.ajax.post(
      'goods_receipts/post',
      {
        id: Number(hidGoodsReceiptId.value)
      }
    );

    if (!response.success) {
      Atlas.toast.error(response.message);
      return;
    }

    Atlas.toast.success(response.message);

    setTimeout(() => Atlas.page.refresh(), 1500);

  } finally {
    btnPostGoodsReceipt.disabled = false;
  }
};

const cancelGoodsReceipt = async () => {

  if (btnCancelGoodsReceipt.disabled) {
    return;
  }

  const confirmed = await Atlas.dialog.confirm(
    'Cancel Goods Receipt?',
    'Are you sure you want to cancel this Goods Receipt?'
  );

  if (!confirmed) {
    return;
  }

  btnCancelGoodsReceipt.disabled = true;

  try {
    const response = await Atlas.ajax.post(
      'goods_receipts/cancel',
      {
        id: Number(hidGoodsReceiptId.value)
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
  const ids = Atlas.table.selectedIds();

  if (ids.length === 0) {
    Atlas.toast.warning(
      'Please select at least one Goods Receipt'
    );
    return;
  }

  Atlas.print.post('goods_receipts/print', ids);
};

const markDirty = () => {
  if (isLoading) return;
  isDirty = true;
}