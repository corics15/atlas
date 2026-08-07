const btnSavePurchaseReturn = document.getElementById('btnSavePurchaseReturn');
const btnPostPurchaseReturn = document.getElementById('btnPostPurchaseReturn');
const btnEditPurchaseReturn = document.getElementById('btnEditPurchaseReturn');
const btnRefreshPurchaseReturn = document.getElementById('btnRefreshPurchaseReturn');
const btnCancelPurchaseReturn = document.getElementById('btnCancelPurchaseReturn');
const btnPrintPurchaseReturn = document.getElementById('btnPrintPurchaseReturn');

const txtPurchaseReturnRemarks = document.getElementById('txtPurchaseReturnRemarks');

const hidPurchaseReturnId = document.getElementById('hidPurchaseReturnId');
const hidGoodsReceiptId = document.getElementById('hidGoodsReceiptId');

let isDirty = false;
let isLoading = true;
let isEditMode = hidPurchaseReturnId?.value ? true : false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkPurchaseReturn',
    selectAll: '#chkSelectAllPurchaseReturn',
  });

  Atlas.select.init('#selSupplierFilter');

  /*** dirty tracking */
  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('pr-return-qty') ||
      e.target.id === `txtPurchaseReturnRemarks`
    ) {
      markDirty();
    }
  });

  /*** edit */
  btnEditPurchaseReturn?.addEventListener('click', () => {
    const id = getSelectedId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`purchase-returns/edit/${id}`);
  });

  /*** save */
  btnSavePurchaseReturn?.addEventListener('click', async () => {

    if (!validatePurchaseReturn()) {
      return;
    }

    btnSavePurchaseReturn.disabled = true;

    try {

      const purchaseReturn = {
        id: Atlas.format.integer(hidPurchaseReturnId.value),
        goods_receipt_id: Atlas.format.integer(hidGoodsReceiptId.value),
        return_date: document.getElementById('dtReturnDate').value,
        supplier_id: Atlas.format.integer(document.getElementById('hidSupplierId').value),
        remarks: txtPurchaseReturnRemarks.value,
        details: []
      };

      document.querySelectorAll('#tblPurchaseReturnDetails tbody tr').forEach(row => {
        if (!row.dataset.productId) {
          return;
        }

        const qty = Atlas.format.integer(row.querySelector('.pr-return-qty').value);

        // if (qty <= 0) {
        //   return;
        // }

        /***
          * above code commented out because:
          * an empty details[] has two possible meanings:
          *  1. user is only editing the header (remarks/date). - fine
          *  2. User intentionally removed every quantity. - sill fine but not good
          *  the server cannot distinguish those two cases.
          * so we always push, even with 0 quantities
        */
        purchaseReturn.details.push({
          goods_receipt_detail_id: Atlas.format.integer(row.dataset.goodsReceiptDetailId),
          product_id: Atlas.format.integer(row.dataset.productId),
          qty: qty
        });

      });

      if (!isEditMode) {

        /*** final confirmation for user */
        const confirmed = await Atlas.dialog.confirm(
          'Confirm Action',
          `<div class="text-brown text-center">
          <p>This Purchase Return will be created.</p>
          <p>To preserve the integrity of inventory transactions,<br>
          RETURN QTY can no longer be modified after the document has been created.</p>
          <p>If changes to the returned items or quantities are required,<br>
          you will need to cancel this Purchase Return and create a new one.</p>
          <p class="font-weight-500 text-danger">Save and proceed?</p>
        </div>`
        );

        if (!confirmed) {
          return;
        }
      }

      const result = await Atlas.ajax.post(
        'purchase-returns/save',
        purchaseReturn
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      document.getElementById('hidPurchaseReturnId').value = result.data.purchase_return_id;
      setTimeout(() => Atlas.page.redirect(`purchase-returns/edit/${result.data.purchase_return_id}`), 1200);

      isEditMode = true;
      isDirty = false;

    }
    finally {
      btnSavePurchaseReturn.disabled = false;
    }

  });

  /*** post */
  btnPostPurchaseReturn?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.purchaseReturnId === 0) {
        Atlas.toast.warning('New Purchase Return, not yet saved');
        return false;
      } else if (window.purchaseReturnId) {
        ids = [window.purchaseReturnId];
      } else {
        Atlas.toast.warning('Please select at least one Purchase Return');
        return false;
      }
    }

    const result = await Atlas.dialog.confirm(
      'Confirm Action',
      `<div class="text-brown text-center">
        <p>Inventory quantities will be updated.<br>
        This action cannot be undone.</p>
        <p class="font-weight-500 text-danger">Post Purchase Return?</p>
      </div>`
    );

    if (!result) {
      return;
    }

    btnPostPurchaseReturn.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'purchase-returns/post',
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
      btnPostPurchaseReturn.disabled = false;
    }
  });

  /*** cancel */
  btnCancelPurchaseReturn?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.purchaseReturnId === 0) {
        Atlas.toast.warning('New Purchase Return, not yet saved');
        return false;
      } else if (window.purchaseReturnId) {
        ids = [window.purchaseReturnId];
      } else {
        Atlas.toast.warning('Please select at least one Purchase Return');
        return false;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Purchase Return(s)?`,
      text: 'Please provide the reason for cancellation.',
      inputPlaceholder: 'Enter cancellation reason...',
      required: false,
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    const result = await Atlas.ajax.post(
      'purchase-returns/cancel',
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
  btnPrintPurchaseReturn?.addEventListener('click', printPurchaseReturn);

  /*** refresh */
  btnRefreshPurchaseReturn?.addEventListener('click', () => Atlas.page.redirect(`purchase-returns`));

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
      'Please select a Purchase Return.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Purchase Return.'
    );

    return null;
  }

  return checked[0].value;
};

const validatePurchaseReturn = () => {
  const rows = document.querySelectorAll('#tblPurchaseReturnDetails tr');

  let hasProduct = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];

    if (!row.dataset.productId) {
      continue;
    }

    hasProduct = true;

    const qty = Atlas.format.integer(row.querySelector('.pr-return-qty').value || 0);
    if (qty < 0) {
      Atlas.toast.warning(`Invalid quantity on row ${i + 1}.`);
      setTimeout(() => row.querySelector('.pr-return-qty').focus(), 500);
      return false;
    }

    if (qty > Atlas.format.parseNumber(row.dataset.availableQty)) {
      Atlas.toast.error(`Return quantity cannot be more than the received quantity.`)
      return false;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning('Please add at least one product.');
    return false;
  }

  return true;
};

const printPurchaseReturn = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.purchaseReturnId === 0) {
      Atlas.toast.warning('New Purchase Return, not yet saved');
      return;
    } else if (window.purchaseReturnId) {
      ids = [window.purchaseReturnId];
    } else {
      Atlas.toast.warning('Please select at least one Purchase Return');
      return;
    }
  }

  Atlas.print.post('purchase-returns/print', ids);
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};