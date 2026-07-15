document.addEventListener('DOMContentLoaded', () => {

  const btnNewProduct = document.getElementById('btnNewProduct');
  const btnEditProduct = document.getElementById('btnEditProduct');
  const btnActivateProduct = document.getElementById('btnActivateProduct');
  const btnDeactivateProduct = document.getElementById('btnDeactivateProduct');
  const btnRefreshProduct = document.getElementById('btnRefreshProduct');

  const frmProduct = document.getElementById('frmProduct');

  const txtBarcode = document.getElementById('txtBarcode');
  const txtDescription = document.getElementById('txtDescription');
  const selSupplier = document.getElementById('selSupplier');
  const selUom = document.getElementById('selUom');
  const txtCost = document.getElementById('txtCost');
  const txtSRP = document.getElementById('txtSRP');

  const hidProductId = document.getElementById('hidProductId');
  const chkSelectAllProduct = document.getElementById('chkSelectAllProduct');

  Atlas.select.init('#selSupplier', '#mdlProduct');
  Atlas.select.init('#selUom', '#mdlProduct');

  Atlas.table.init({
    checkbox: '.chkProduct',
    selectAll: '#chkSelectAllProduct',
    onChange: updateToolbarState
  });

  updateToolbarState();

  btnNewProduct.addEventListener('click', () => {
    frmProduct.reset();
    hidProductId.value = '';

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
          Atlas.page.refresh();
        }, 1500);
      },
      onError: (result) => {
        console.log(result);
      }
    });
  });

  btnEditProduct.addEventListener('click', async () => {
    const id = getSelectedProductId();

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

    txtBarcode.value = result.data.barcode;
    txtDescription.value = result.data.description;
    txtCost.value = result.data.cost;
    txtSRP.value = result.data.srp;

    $('#selSupplier').val(result.data.supplier_id).trigger('change');
    $('#selUom').val(result.data.uom_id).trigger('change');

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlProduct',
      title: 'Edit Product'
    });
  });

  btnActivateProduct.addEventListener('click', async () => {
    const id = getSelectedProductId();

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
        Atlas.page.refresh();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  btnDeactivateProduct.addEventListener('click', async () => {
    const id = getSelectedProductId();

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
        Atlas.page.refresh();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  btnRefreshProduct.addEventListener('click', () => {
    Atlas.page.refresh();
  });
});

const getSelectedProductId = () => {
  const checked = Atlas.table.selected();

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

const updateToolbarState = (selected = Atlas.table.selected()) => {
  const checked = selected.length;

  btnEditProduct.disabled = (checked !== 1);
  btnActivateProduct.disabled = (checked !== 1);
  btnDeactivateProduct.disabled = (checked !== 1);
}