document.addEventListener('DOMContentLoaded', () => {

  const btnNewProduct = document.getElementById('btnNewProduct');
  const btnEditProduct = document.getElementById('btnEditProduct');
  // const btnSaveProduct = document.getElementById('btnSaveProduct');
  const btnActivateProduct = document.getElementById('btnActivateProduct');
  const btnDeactivateProduct = document.getElementById('btnDeactivateProduct');
  const btnRefreshProduct = document.getElementById('btnRefreshProduct');

  const frmProduct = document.getElementById('frmProduct');

  // const txtProductCode = document.getElementById('txtProductCode');
  const txtBarcode = document.getElementById('txtBarcode');
  const txtDescription = document.getElementById('txtDescription');
  const selSupplier = document.getElementById('selSupplier');
  const txtUOM = document.getElementById('txtUOM');
  const txtCost = document.getElementById('txtCost');
  const txtSRP = document.getElementById('txtSRP');

  const hidProductId = document.getElementById('hidProductId');
  const chkSelectAllProduct = document.getElementById('chkSelectAllProduct');

  updateToolbarState();

  btnNewProduct.addEventListener('click', () => {
    frmProduct.reset();

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlProduct',
      title: 'New Product'
    });
  });

  frmProduct.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmProduct,
      url: 'products/save',
      onSuccess: (result) => {
        frmProduct.reset();
        hidProductId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlProduct');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          location.reload();
        }, 1500);
      },
      onError: (result) => {
        console.log(result);
      }
    });
  });

  btnEditProduct.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'products/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmProduct.reset();

    hidProductId.value = result.data.id;

    // txtProductCode.value = result.data.product_code;
    txtBarcode.value = result.data.barcode;
    txtDescription.value = result.data.description;
    selSupplier.value = result.data.supplier_id;
    txtUOM.value = result.data.uom;
    txtCost.value = result.data.cost;
    txtSRP.value = result.data.srp;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlProduct',
      title: 'Edit Product'
    });
  });

  btnActivateProduct.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Product',
      'Are you sure you want to activate the selected product?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'products/activate/' + id
    );

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => {
        location.reload();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  btnDeactivateProduct.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Product',
      'Are you sure you want to deactivate the selected product?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'products/deactivate/' + id
    );

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => {
        location.reload();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  chkSelectAllProduct.addEventListener('change', () => {
    document.querySelectorAll('.chkProduct').forEach(chk => {
      chk.checked = chkSelectAllProduct.checked;
    });
    updateToolbarState();
  });

  document.querySelectorAll('.chkProduct').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkProduct').length;
      const checked = document.querySelectorAll('.chkProduct:checked').length;
      chkSelectAllProduct.checked = (total === checked);

      updateToolbarState();
    });

  });

  btnRefreshProduct.addEventListener('click', () => {
    location.reload();
  });

  function getSelectedSupplierId() {
    const checked = document.querySelectorAll('.chkProduct:checked');

    if (checked.length === 0) {
      Atlas.toast.warning('Please select a product.');
      return null;
    }

    if (checked.length > 1) {
      Atlas.toast.warning('Please select only one product.');
      return null;
    }

    return checked[0].value;
  }

  function updateToolbarState() {
    const checked = document.querySelectorAll('.chkProduct:checked').length;
    btnEditProduct.disabled = (checked !== 1);
    btnActivateProduct.disabled = (checked !== 1);
    btnDeactivateProduct.disabled = (checked !== 1);
  }
});