document.addEventListener('DOMContentLoaded', () => {

  const btnNewUser = document.getElementById('btnNew');
  const formUser = document.getElementById('frmUser');

  btnNewUser.addEventListener('click', () => {
    formUser.reset();
    Atlas.modal.open('mdlUser');
  });

  // formUser.addEventListener('submit', async (e) => {
  //   e.preventDefault()
  //   const formData = new FormData(formUser);

  //   Atlas.validation.clear();
  //   Atlas.loader.show();

  //   const result = await Atlas.ajax.post('users/save', formData);

  //   Atlas.loader.hide();

  //   if (result.success) {
  //     Atlas.toast.success(result.message);
  //     Atlas.modal.close('mdlUser');
  //     setTimeout(() => {
  //       window.location.reload();
  //     }, 500);
  //   } else {
  //     Atlas.validation.show(result.data.errors)
  //   }
  // });

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

});