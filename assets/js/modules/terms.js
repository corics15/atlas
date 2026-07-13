document.addEventListener('DOMContentLoaded', () => {

  const btnNewTerm = document.getElementById('btnNewTerm');
  const btnEditTerm = document.getElementById('btnEditTerm');
  const btnActivateTerm = document.getElementById('btnActivateTerm');
  const btnDeactivateTerm = document.getElementById('btnDeactivateTerm');
  const btnRefreshTerm = document.getElementById('btnRefreshTerm');

  const frmTerm = document.getElementById('frmTerm');

  const txtTerm = document.getElementById('txtTerm');

  const hidTermId = document.getElementById('hidTermId');
  const chkSelectAllTerm = document.getElementById('chkSelectAllTerm');

  updateToolbarState();

  btnNewTerm.addEventListener('click', () => {
    frmTerm.reset();

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlTerm',
      title: 'New Term'
    });
  });

  frmTerm.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmTerm,
      url: 'terms/save',
      onSuccess: (result) => {
        frmTerm.reset();
        hidTermId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlTerm');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          location.reload();
        }, 1500);
      },
      onError: (result) => { }
    });
  });

  btnEditTerm.addEventListener('click', async () => {
    const id = getSelectedTermId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'terms/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmTerm.reset();

    hidTermId.value = result.data.id;
    txtTerm.value = result.data.terms_name;

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlTerm',
      title: 'Edit Term'
    });
  });

  btnActivateTerm.addEventListener('click', async () => {
    const id = getSelectedTermId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Term',
      'Are you sure you want to activate the selected Term?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'terms/activate/' + id
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

  btnDeactivateTerm.addEventListener('click', async () => {
    const id = getSelectedTermId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Term',
      'Are you sure you want to deactivate the selected Term?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'terms/deactivate/' + id
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

  chkSelectAllTerm.addEventListener('change', () => {
    document.querySelectorAll('.chkTerm').forEach(chk => {
      chk.checked = chkSelectAllTerm.checked;
    });
    updateToolbarState();
  });

  document.querySelectorAll('.chkTerm').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkTerm').length;
      const checked = document.querySelectorAll('.chkTerm:checked').length;
      chkSelectAllTerm.checked = (total === checked);

      updateToolbarState();
    });

  });

  btnRefreshTerm.addEventListener('click', () => {
    location.reload();
  });
});

const getSelectedTermId = () => {
  const checked = document.querySelectorAll('.chkTerm:checked');

  if (checked.length === 0) {
    Atlas.toast.warning('Please select a Term.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one Term.');
    return null;
  }

  return checked[0].value;
}

const updateToolbarState = () => {
  const checked = document.querySelectorAll('.chkTerm:checked').length;
  btnEditTerm.disabled = (checked !== 1);
  btnActivateTerm.disabled = (checked !== 1);
  btnDeactivateTerm.disabled = (checked !== 1);
}