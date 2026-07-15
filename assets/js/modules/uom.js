document.addEventListener('DOMContentLoaded', () => {

  const btnNewUom = document.getElementById('btnNewUom');
  const btnEditUom = document.getElementById('btnEditUom');
  const btnActivateUom = document.getElementById('btnActivateUom');
  const btnDeactivateUom = document.getElementById('btnDeactivateUom');
  const btnRefreshUom = document.getElementById('btnRefreshUom');

  const frmUom = document.getElementById('frmUom');

  const txtUom = document.getElementById('txtUom');

  const hidUomId = document.getElementById('hidUomId');
  const chkSelectAllUom = document.getElementById('chkSelectAllUom');

  Atlas.table.init({
    checkbox: '.chkUom',
    selectAll: '#chkSelectAllUom',
    onChange: updateToolbarState
  });

  updateToolbarState();

  btnNewUom.addEventListener('click', () => {
    frmUom.reset();
    hidUomId.value = '';

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlUom',
      title: 'New UOM'
    });
  });

  frmUom.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmUom,
      url: 'uom/save',
      onSuccess: (result) => {
        frmUom.reset();
        hidUomId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlUom');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          Atlas.page.refresh();
        }, 1500);
      },
      onError: (result) => { }
    });
  });

  btnEditUom.addEventListener('click', async () => {
    const id = getSelectedUomId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'uom/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmUom.reset();

    hidUomId.value = result.data.id;
    txtUom.value = result.data.uom;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlUom',
      title: 'Edit UOM'
    });
  });

  btnActivateUom.addEventListener('click', async () => {
    const id = getSelectedUomId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate UOM',
      'Are you sure you want to activate the selected UOM?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'uom/activate/' + id
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

  btnDeactivateUom.addEventListener('click', async () => {
    const id = getSelectedUomId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate UOM',
      'Are you sure you want to deactivate the selected UOM?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'uom/deactivate/' + id
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

  btnRefreshUom.addEventListener('click', () => {
    Atlas.page.refresh();
  });
});

const getSelectedUomId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning('Please select a UOM.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one UOM.');
    return null;
  }

  return checked[0].value;
}

const updateToolbarState = (selected = Atlas.table.selected()) => {
  const checked = selected.length;

  btnEditUom.disabled = (checked !== 1);
  btnActivateUom.disabled = (checked !== 1);
  btnDeactivateUom.disabled = (checked !== 1);
}