document.addEventListener('DOMContentLoaded', () => {

  const btnNewOutletType = document.getElementById('btnNewOutletType');
  const btnEditOutletType = document.getElementById('btnEditOutletType');
  const btnActivateOutletType = document.getElementById('btnActivateOutletType');
  const btnDeactivateOutletType = document.getElementById('btnDeactivateOutletType');
  const btnRefreshOutletType = document.getElementById('btnRefreshOutletType');

  const frmOutletType = document.getElementById('frmOutletType');

  const txtOutletTypeName = document.getElementById('txtOutletTypeName');

  const hidOutletTypeId = document.getElementById('hidOutletTypeId');
  const chkSelectAllOutletType = document.getElementById('chkSelectAllOutletType');

  Atlas.table.init({
    checkbox: '.chkOutletType',
    selectAll: '#chkSelectAllOutletType',
    onChange: updateToolbarState
  });

  updateToolbarState();

  /*** new */
  btnNewOutletType.addEventListener('click', () => {
    frmOutletType.reset();
    hidOutletTypeId.value = '';

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlOutletType',
      title: 'New Outlet Type'
    });
  });

  /*** update / new event */
  frmOutletType.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmOutletType,
      url: 'outlet_types/save',
      onSuccess: (result) => {
        frmOutletType?.reset();
        hidOutletTypeId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlOutletType');
        Atlas.toast.success(result.message);
        setTimeout(() => Atlas.page.refresh(), 1500);
      },
      onError: (result) => {
        console.log(result); /*** TODO REPLACE */
      }
    });
  });

  /*** edit */
  btnEditOutletType.addEventListener('click', async () => {
    const id = getSelectedOutletTypeId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      `outlet_types/get/${id}`
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmOutletType.reset();

    hidOutletTypeId.value = result.data.id;

    txtOutletTypeName.value = result.data.outlet_type_name;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlOutletType',
      title: 'Edit Customer'
    });
  });

  /*** activate */
  btnActivateOutletType.addEventListener('click', async () => {
    const id = getSelectedOutletTypeId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Outlet Type',
      'Are you sure you want to activate the selected item?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      `outlet-types/activate/${id}`
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

  /*** deactivate */
  btnDeactivateOutletType.addEventListener('click', async () => {
    const id = getSelectedOutletTypeId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Outlet Type',
      'Are you sure you want to deactivate the selected item?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'outlet-types/deactivate/' + id
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

  /*** refresh */
  btnRefreshOutletType.addEventListener('click', () => Atlas.page.refresh());

});

const getSelectedOutletTypeId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning('Please select an item from the list.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one item from the list.');
    return null;
  }

  return checked[0].value;
}

const updateToolbarState = (selected = Atlas.table.selected()) => {
  const checked = selected.length;

  btnEditOutletType.disabled = (checked !== 1);
  btnActivateOutletType.disabled = (checked !== 1);
  btnDeactivateOutletType.disabled = (checked !== 1);
}