document.addEventListener('DOMContentLoaded', () => {

  const btnNewSupplier = document.getElementById('btnNewSupplier');
  const btnEditSupplier = document.getElementById('btnEditSupplier');
  const btnSaveSupplier = document.getElementById('btnSaveSupplier');
  const btnActivateSupplier = document.getElementById('btnActivateSupplier');
  const btnDeactivateSupplier = document.getElementById('btnDeactivateSupplier');
  const btnRefreshSupplier = document.getElementById('btnRefreshSupplier');

  const frmSupplier = document.getElementById('frmSupplier');

  const txtSupplierName = document.getElementById('txtSupplierName');
  const txtContactPerson = document.getElementById('txtContactPerson');
  const txtMobileNo = document.getElementById('txtMobileNo');
  const txtTelephoneNo = document.getElementById('txtTelephoneNo');
  const txtEmailAddress = document.getElementById('txtEmailAddress');
  const txtAddress = document.getElementById('txtAddress');
  const txtTinNo = document.getElementById('txtTinNo');

  const hidSupplierId = document.getElementById('hidSupplierId');
  const chkSelectAllSupplier = document.getElementById('chkSelectAllSupplier');

  updateToolbarState();

  btnNewSupplier.addEventListener('click', () => {
    frmSupplier.reset();

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlSupplier',
      title: 'New Supplier'
    });
  });

  frmSupplier.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmSupplier,
      url: 'suppliers/save',
      onSuccess: (result) => {
        frmSupplier.reset();
        hidSupplierId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlSupplier');
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

  btnEditSupplier.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'suppliers/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmSupplier.reset();

    hidSupplierId.value = result.data.id;

    txtSupplierName.value = result.data.supplier_name;
    txtContactPerson.value = result.data.contact_person;
    txtMobileNo.value = result.data.mobile_no;
    txtTelephoneNo.value = result.data.telephone_no;
    txtEmailAddress.value = result.data.email_address;
    txtAddress.value = result.data.address;
    txtTinNo.value = result.data.tin_no;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlSupplier',
      title: 'Edit Supplier'
    });
  });

  btnActivateSupplier.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Supplier',
      'Are you sure you want to activate the selected Supplier?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'suppliers/activate/' + id
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

  btnDeactivateSupplier.addEventListener('click', async () => {
    const id = getSelectedSupplierId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Supplier',
      'Are you sure you want to deactivate the selected Supplier?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'suppliers/deactivate/' + id
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

  chkSelectAllSupplier.addEventListener('change', () => {
    document.querySelectorAll('.chkSupplier').forEach(chk => {
      chk.checked = chkSelectAllSupplier.checked;
    });
    updateToolbarState();
  });

  document.querySelectorAll('.chkSupplier').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkSupplier').length;
      const checked = document.querySelectorAll('.chkSupplier:checked').length;
      chkSelectAllSupplier.checked = (total === checked);

      updateToolbarState();
    });

  });

  btnRefreshSupplier.addEventListener('click', () => {
    location.reload();
  });

  function getSelectedSupplierId() {
    const checked = document.querySelectorAll('.chkSupplier:checked');

    if (checked.length === 0) {
      Atlas.toast.warning('Please select a Supplier.');
      return null;
    }

    if (checked.length > 1) {
      Atlas.toast.warning('Please select only one Supplier.');
      return null;
    }

    return checked[0].value;
  }

  function updateToolbarState() {
    const checked = document.querySelectorAll('.chkSupplier:checked').length;
    btnEditSupplier.disabled = (checked !== 1);
    btnActivateSupplier.disabled = (checked !== 1);
    btnDeactivateSupplier.disabled = (checked !== 1);
  }
});