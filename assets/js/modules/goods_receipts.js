document.addEventListener('DOMContentLoaded', () => {

  const btnSaveGoodsReceipt = document.getElementById('btnSaveGoodsReceipt');
  const tblGoodsReceiptDetails = document.getElementById('tblGoodsReceiptDetails');

  if (!btnSaveGoodsReceipt) {
    return;
  }

  btnSaveGoodsReceipt.addEventListener('click', saveGoodsReceipt);
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
  const formData = new FormData();

  formData.append('grn_date', document.getElementById('dtGRNDate').value);
  formData.append('po_id', document.getElementById('hidPurchaseOrderId').value);
  formData.append('supplier_id', document.getElementById('hidSupplierId').value);
  formData.append('remarks', document.getElementById('txtRemarks').value.trim());

  let details;
  try {
    details = collectReceiptDetails();
  }
  catch (error) {
    return;
  }

  formData.append(
    'details',
    JSON.stringify(details)
  );

  const result = await Atlas.ajax.post(
    'goods_receipts/save',
    formData
  );

  if (!result.success) {
    Atlas.toast.error(result.message);
    return;
  }

  Atlas.toast.success(result.message);
  setTimeout(() => {
    Atlas.page.refresh();
  }, 1500);
};