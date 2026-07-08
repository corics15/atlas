document.addEventListener('DOMContentLoaded', () => {

  const btnNewSalesman = document.getElementById('btnNewSalesman');
  const btnEditSalesman = document.getElementById('btnEditSalesman');
  const btnSaveSalesman = document.getElementById('btnSaveSalesman');
  const btnActivateSalesman = document.getElementById('btnActivateSalesman');
  const btnDeactivateSalesman = document.getElementById('btnDeactivateSalesman');
  const btnRefreshSalesmen = document.getElementById('btnRefreshSalesmen');

  const frmSalesman = document.getElementById('frmSalesman');

  const txtSalesmanCode = document.getElementById('txtSalesmanCode');
  const txtFirstName = document.getElementById('txtFirstName');
  const txtLastName = document.getElementById('txtLastName');
  const txtContactNo = document.getElementById('txtContactNo');

  const hidSalesmanId = document.getElementById('hidSalesmanId');
  const chkSelectAllSalesmen = document.getElementById('chkSelectAllSalesmen');

  updateToolbarState();

  btnNewSalesman.addEventListener('click', () => {
    frmSalesman.reset();

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlSalesman',
      title: 'New Salesman'
    });
  });

  frmSalesman.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmSalesman,
      url: 'salesmen/save',
      onSuccess: (result) => {
        frmSalesman.reset();
        hidSalesmanId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlSalesman');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          location.reload();
        }, 1500);
      },
      onError: (result) => { }
    });
  });

  btnEditSalesman.addEventListener('click', async () => {
    const id = getSelectedSalesmanId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'salesmen/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmSalesman.reset();

    hidSalesmanId.value = result.data.id;

    txtSalesmanCode.value = result.data.code;
    txtFirstName.value = result.data.first_name;
    txtLastName.value = result.data.last_name;
    txtContactNo.value = result.data.mobile_no;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlSalesman',
      title: 'Edit Salesman'
    });
  });

  btnActivateSalesman.addEventListener('click', async () => {
    const id = getSelectedSalesmanId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Salesman',
      'Are you sure you want to activate the selected salesman?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'salesmen/activate/' + id
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

  btnDeactivateSalesman.addEventListener('click', async () => {
    const id = getSelectedSalesmanId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Salesman',
      'Are you sure you want to deactivate the selected salesman?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'salesmen/deactivate/' + id
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

  chkSelectAllSalesmen.addEventListener('change', () => {
    document.querySelectorAll('.chkSalesman').forEach(chk => {
      chk.checked = chkSelectAllSalesmen.checked;
    });
    updateToolbarState();
  });

  document.querySelectorAll('.chkSalesman').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkSalesman').length;
      const checked = document.querySelectorAll('.chkSalesman:checked').length;
      chkSelectAllSalesmen.checked = (total === checked);

      updateToolbarState();
    });

  });

  btnRefreshSalesmen.addEventListener('click', () => {
    location.reload();
  });

  function getSelectedSalesmanId() {
    const checked = document.querySelectorAll('.chkSalesman:checked');

    if (checked.length === 0) {
      Atlas.toast.warning('Please select a salesman.');
      return null;
    }

    if (checked.length > 1) {
      Atlas.toast.warning('Please select only one salesman.');
      return null;
    }

    return checked[0].value;
  }

  function updateToolbarState() {
    const checked = document.querySelectorAll('.chkSalesman:checked').length;
    btnEditSalesman.disabled = (checked !== 1);
    btnActivateSalesman.disabled = (checked !== 1);
    btnDeactivateSalesman.disabled = (checked !== 1);
  }
});