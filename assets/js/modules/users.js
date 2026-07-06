document.addEventListener('DOMContentLoaded', () => {

  const btnNewUser = document.getElementById('btnNew');
  const formUser = document.getElementById('frmUser');

  btnNewUser.addEventListener('click', () => {
    formUser.reset();
    Atlas.modal.open('mdlUser');
  });

  formUser.addEventListener('submit', async (e) => {
    e.preventDefault()
    const formData = new FormData(formUser);

    Atlas.loader.show();

    const result = await Atlas.ajax.post('users/save', formData);

    Atlas.loader.hide();

    if (result.success) {
      Atlas.toast.success(result.message);
      Atlas.modal.close('mdlUser');
    } else {
      Atlas.toast.error(result.message);
    }
  });

});