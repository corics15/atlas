document.addEventListener('DOMContentLoaded', () => {
  const btnNewPurchaseOrder = document.getElementById('btnNewPurchaseOrder');
  const btnEditPurchaseOrder = document.getElementById('btnEditPurchaseOrder');
  const btnReceiveGoods = document.getElementById('btnReceiveGoods');
  const btnPrintPurchaseOrder = document.getElementById('btnPrintPurchaseOrder');
  const btnCancelPurchaseOrder = document.getElementById('btnCancelPurchaseOrder');
  const btnRefreshPurchaseOrder = document.getElementById('btnRefreshPurchaseOrder');
  const chkSelectAllPurchaseOrder = document.getElementById('chkSelectAllPurchaseOrder');

  Atlas.select.init('#selSupplierFilter');

  Atlas.table.init({
    checkbox: '.chkPurchaseOrder',
    selectAll: '#chkSelectAllPurchaseOrder',
    onChange: updateToolbarState
  });

  updateToolbarState();

  /*** new purchase order */
  btnNewPurchaseOrder.addEventListener('click', () => {
    Atlas.page.redirect('purchase_orders');
  });

  /*** edit purchase order */
  btnEditPurchaseOrder.addEventListener('click', () => {
    const id = getSelectedPurchaseOrderId();

    if (!id) {
      return;
    }

    Atlas.page.redirect('purchase_orders', { id: id });
  });

  /*** receive goods */
  btnReceiveGoods.addEventListener('click', () => {
    const id = getSelectedPurchaseOrderId();

    if (!id) {
      return;
    }

    Atlas.page.redirect('goods_receipts/create', { po: id });
  });

  /*** refresh purchase order */
  btnRefreshPurchaseOrder.addEventListener('click', () => Atlas.page.refresh());

  /*** cancel purchase order */
  btnCancelPurchaseOrder.addEventListener('click', async () => {
    const selected = getSelectedPurchaseOrders();

    if (selected.length === 0) {
      Atlas.toast.warning(
        'Please select at least one Purchase Order.'
      );
      return;
    }

    const allOpen = selected.every(chk =>
      chk.dataset.status === 'OPEN'
    );

    if (!allOpen) {
      Atlas.toast.warning(
        'Please select only OPEN Purchase Orders to cancel.'
      );
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Cancel Purchase Order',
      selected.length === 1
        ? 'Are you sure you want to cancel the selected Purchase Order?'
        : `Are you sure you want to cancel ${selected.length} Purchase Orders?`
    );

    if (!confirmed) {
      return;
    }

    const formData = new FormData();

    selected.forEach(chk => {
      formData.append('ids[]', chk.value);
    });

    const result = await Atlas.ajax.post(
      'purchase_orders/cancel',
      formData
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);

    setTimeout(() => {
      Atlas.page.refresh();
    }, 500);
  });

  /*** print */
  btnPrintPurchaseOrder.addEventListener('click', () => {
    const ids = Atlas.table.selectedIds();

    if (ids.length === 0) {
      Atlas.toast.warning(
        'Please select at least one Purchase Order.'
      );
      return;
    }

    Atlas.print.post('purchase_orders/print', ids);
  });

});

const getSelectedPurchaseOrderId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning(
      'Please select a Purchase Order.'
    );
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning(
      'Please select only one Purchase Order. Multiple selection is supported only for Print and Cancel.'
    );

    return null;
  }

  return checked[0].value;
}

const getSelectedPurchaseOrderStatus = () => {
  const checked = document.querySelector('.chkPurchaseOrder:checked');

  return checked
    ? checked.dataset.status
    : null;
}

const updateToolbarState = (selected = Atlas.table.selected()) => {
  btnEditPurchaseOrder.disabled = true;
  btnPrintPurchaseOrder.disabled = true;
  btnCancelPurchaseOrder.disabled = true;
  btnReceiveGoods.disabled = true;

  if (selected.length === 0) {
    return;
  }

  //** print supports one or more */
  btnPrintPurchaseOrder.disabled = false;

  //** edit and receive supports exactly one OPEN PO */
  if (selected.length === 1) {
    const status = selected[0].dataset.status;

    if (status === 'OPEN') {
      btnEditPurchaseOrder.disabled = false;
      btnReceiveGoods.disabled = false;
    }

    if (status === 'PARTIAL') {
      btnReceiveGoods.disabled = false;
    }
  }

  //** cancel supports one or more OPEN POs */
  const allOpen = selected.every(chk =>
    chk.dataset.status === 'OPEN'
  );

  if (allOpen) {
    btnCancelPurchaseOrder.disabled = false;
  }
}

const getSelectedPurchaseOrders = () => {
  return Atlas.table.selected();
}