document.addEventListener('DOMContentLoaded', () => {

  const btnNewUser = document.getElementById('btnNew');
  const formUser = document.getElementById('frmUser');

  const txtUsername = document.getElementById('txtUsername');
  const txtFirstName = document.getElementById('txtFirstName');
  const txtLastName = document.getElementById('txtLastName');

  const hidUserId = document.getElementById('hidUserId');
  const lblUserTitle = document.getElementById('lblUserTitle');

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

  document.querySelectorAll('.btnEditUser')
    .forEach(button => {
      button.addEventListener('click', async () => {

        const id = button.dataset.id;
        const result = await Atlas.ajax.get(
          'users/get/' + id
        );

        if (!result.success) {
          Atlas.toast.error(result.message);
          return;
        }

        frmUser.reset();

        txtUsername.value = result.data.username;
        txtFirstName.value = result.data.first_name;
        txtLastName.value = result.data.last_name;

        hidUserId.value = result.data.id;
        Atlas.modal.open({
          id: 'mdlUser',
          title: 'Edit User'
        });
      });

    });

});