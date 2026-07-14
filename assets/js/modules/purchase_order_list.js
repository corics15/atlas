document.addEventListener('DOMContentLoaded', () => {
  const btnNewPurchaseOrder = document.getElementById('btnNewPurchaseOrder');
  const btnEditPurchaseOrder = document.getElementById('btnEditPurchaseOrder');
  const btnPrintPurchaseOrder = document.getElementById('btnPrintPurchaseOrder');
  const btnCancelPurchaseOrder = document.getElementById('btnCancelPurchaseOrder');
  const btnRefreshPurchaseOrder = document.getElementById('btnRefreshPurchaseOrder');
  const chkSelectAllPurchaseOrder = document.getElementById('chkSelectAllPurchaseOrder');

  updateToolbarState();

  Atlas.select.init('#selSupplierFilter');

  /*** new purchase order */
  btnNewPurchaseOrder.addEventListener('click', () => {
    location.href = Atlas.config.baseUrl + 'purchase_orders';
  });

  /*** edit purchase order */
  btnEditPurchaseOrder.addEventListener('click', () => {
    const id = getSelectedPurchaseOrderId();

    if (!id) {
      return;
    }

    location.href = Atlas.config.baseUrl + 'purchase_orders?id=' + id;
  });

  /*** refresh purchase order */
  btnRefreshPurchaseOrder.addEventListener('click', () => {
    location.reload();
  });

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
      location.reload();
    }, 500);
  });

  /*** checkbox event */
  chkSelectAllPurchaseOrder.addEventListener('change', () => {
    document.querySelectorAll('.chkPurchaseOrder').forEach(chk => {
      chk.checked = chkSelectAllPurchaseOrder.checked;
    });

    updateToolbarState();
  });

  /*** toolbar state */
  document.querySelectorAll('.chkPurchaseOrder').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkPurchaseOrder').length;
      const checked = document.querySelectorAll('.chkPurchaseOrder:checked').length;

      chkSelectAllPurchaseOrder.checked = (total === checked);

      updateToolbarState();
    });
  });

  /*** print */
  btnPrintPurchaseOrder.addEventListener('click', () => {
    const selected = getSelectedPurchaseOrders();

    console.log(1)

    if (selected.length === 0) {
      Atlas.toast.warning(
        'Please select at least one Purchase Order.'
      );
      return;
    }

    const form = document.createElement('form');

    form.method = 'POST';
    form.action = Atlas.config.baseUrl + 'purchase_orders/print';
    form.target = '_blank';

    selected.forEach(chk => {
      const input = document.createElement('input');

      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = chk.value;

      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    form.remove();
  });

});

const getSelectedPurchaseOrderId = () => {
  const checked = document.querySelectorAll('.chkPurchaseOrder:checked');

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

const updateToolbarState = () => {
  const selected = getSelectedPurchaseOrders();

  btnEditPurchaseOrder.disabled = true;
  btnPrintPurchaseOrder.disabled = true;
  btnCancelPurchaseOrder.disabled = true;

  if (selected.length === 0) {
    return;
  }

  //** Print supports one or more */
  btnPrintPurchaseOrder.disabled = false;

  //** Edit supports exactly one */
  if (selected.length === 1) {
    const status = selected[0].dataset.status;

    if (status === 'OPEN') {
      btnEditPurchaseOrder.disabled = false;
    }
  }

  //** Cancel supports one or more, but ALL must be OPEN */
  const allOpen = selected.every(chk =>
    chk.dataset.status === 'OPEN'
  );

  if (allOpen) {
    btnCancelPurchaseOrder.disabled = false;
  }
}

const getSelectedPurchaseOrders = () => {
  return Array.from(
    document.querySelectorAll('.chkPurchaseOrder:checked')
  );
}