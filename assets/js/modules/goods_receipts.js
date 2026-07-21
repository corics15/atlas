const btnSaveGoodsReceipt = document.getElementById('btnSaveGoodsReceipt');
const btnSaveChangesGoodsReceipt = document.getElementById('btnSaveChangesGoodsReceipt');
const tblGoodsReceiptDetails = document.getElementById('tblGoodsReceiptDetails');
const btnPrintGoodsReceipt = document.getElementById('btnPrintGoodsReceipt');
const btnRefreshGoodsReceipt = document.getElementById('btnRefreshGoodsReceipt');

const hidGoodsReceiptId = document.getElementById('hidGoodsReceiptId');
const txtRemarks = document.getElementById('txtRemarks');

let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', () => {

  /*** save */
  btnSaveGoodsReceipt?.addEventListener('click', saveGoodsReceipt);

  /*** update */
  btnSaveChangesGoodsReceipt?.addEventListener('click', async () => {
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
  });

  /*** print */
  btnPrintGoodsReceipt?.addEventListener('click', printGoodsReceipt);

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

    if (receiveNow <= 0) {
      return;
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
    setTimeout(() => Atlas.page.refresh(), 1500);

  } finally {
    btnSaveGoodsReceipt.disabled = false;

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