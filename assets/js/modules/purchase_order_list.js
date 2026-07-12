document.addEventListener('DOMContentLoaded', () => {
  const btnNewPurchaseOrder = document.getElementById('btnNewPurchaseOrder');
  const btnEditPurchaseOrder = document.getElementById('btnEditPurchaseOrder');
  const btnPrintPurchaseOrder = document.getElementById('btnPrintPurchaseOrder');
  const btnCancelPurchaseOrder = document.getElementById('btnCancelPurchaseOrder');
  const btnRefreshPurchaseOrder = document.getElementById('btnRefreshPurchaseOrder');
  const chkSelectAllPurchaseOrder = document.getElementById('chkSelectAllPurchaseOrder');

  updateToolbarState();

  Atlas.select.init('#selCustomerFilter');

  btnNewPurchaseOrder.addEventListener('click', () => {
    location.href = Atlas.config.baseUrl + 'purchase_orders';
  });

  btnRefreshPurchaseOrder.addEventListener('click', () => {
    location.reload();
  });

  chkSelectAllPurchaseOrder.addEventListener('change', () => {
    document.querySelectorAll('.chkPurchaseOrder').forEach(chk => {
      chk.checked = chkSelectAllPurchaseOrder.checked;
    });

    updateToolbarState();
  });

  document.querySelectorAll('.chkPurchaseOrder').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkPurchaseOrder').length;
      const checked = document.querySelectorAll('.chkPurchaseOrder:checked').length;

      chkSelectAllPurchaseOrder.checked = (total === checked);

      updateToolbarState();
    });
  });

  btnEditPurchaseOrder.addEventListener('click', () => {
    const id = getSelectedPurchaseOrderId();

    if (!id) {
      return;
    }
    location.href = Atlas.config.baseUrl + 'purchase_orders?id=' + id;
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
      'Please select only one Purchase Order.'
    );
    return null;
  }

  return checked[0].value;
}

const updateToolbarState = () => {
  const checked = document.querySelectorAll('.chkPurchaseOrder:checked').length;

  btnEditPurchaseOrder.disabled = checked !== 1;
  btnPrintPurchaseOrder.disabled = checked !== 1;
  btnCancelPurchaseOrder.disabled = checked !== 1;
}