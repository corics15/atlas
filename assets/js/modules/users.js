document.addEventListener('DOMContentLoaded', () => {

  const btnNewUser = document.getElementById('btnNewUser');
  const btnEditUser = document.getElementById('btnEditUser');
  const formUser = document.getElementById('frmUser');

  const txtUsername = document.getElementById('txtUsername');
  const txtFirstName = document.getElementById('txtFirstName');
  const txtLastName = document.getElementById('txtLastName');

  const hidUserId = document.getElementById('hidUserId');

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