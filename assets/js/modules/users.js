document.addEventListener('DOMContentLoaded', () => {

  const btnNewUser = document.getElementById('btnNewUser');
  const btnEditUser = document.getElementById('btnEditUser');
  const formUser = document.getElementById('frmUser');

  const txtUsername = document.getElementById('txtUsername');
  const txtFirstName = document.getElementById('txtFirstName');
  const txtLastName = document.getElementById('txtLastName');

  const hidUserId = document.getElementById('hidUserId');

  const btnDeactivateUser = document.getElementById('btnDeactivateUser');

  const chkSelectAllUsers = document.getElementById('chkSelectAllUsers');

  btnNewUser.addEventListener('click', () => {
    formUser.reset();
    hidUserId.value = '';

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlUser',
      title: 'New User'
    });
  });

  formUser.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: formUser,
      url: 'users/save',
      onSuccess: (result) => {
        Atlas.modal.close('mdlUser');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          location.reload();
        }, 500);
      },
      onError: (result) => {
        console.log(result);
      }
    });
  });

  btnEditUser.addEventListener('click', async () => {
    const id = getSelectedUserId();
    if (!id) {
      return;
    }
    const result = await Atlas.ajax.get('users/get/' + id);
    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    formUser.reset();
    hidUserId.value = result.data.id;

    txtUsername.value = result.data.username;
    txtFirstName.value = result.data.first_name;
    txtLastName.value = result.data.last_name;

    Atlas.modal.open({
      id: 'mdlUser',
      title: 'Edit User'
    });
  });

  btnDeactivateUser.addEventListener('click', async () => {
    const id = getSelectedUserId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate User',
      'Are you sure you want to deactivate the selected user?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'users/deactivate/' + id
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

  chkSelectAllUsers.addEventListener('change', () => {
    document.querySelectorAll('.chkUser').forEach(chk => {
      chk.checked = chkSelectAllUsers.checked;
    });
  });

  document.querySelectorAll('.chkUser').forEach(chk => {
    chk.addEventListener('change', () => {
      const total = document.querySelectorAll('.chkUser').length;
      const checked = document.querySelectorAll('.chkUser:checked').length;
      chkSelectAllUsers.checked = (total === checked);
    });
  });

});

function getSelectedUserId() {
  const checked = document.querySelectorAll('.chkUser:checked');
  if (checked.length === 0) {
    Atlas.toast.warning('Please select a user.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one user.');
    return null;
  }
  return checked[0].value;
}