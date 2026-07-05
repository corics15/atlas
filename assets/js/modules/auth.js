document.addEventListener('DOMContentLoaded', () => {

  const form = document.getElementById('frmLogin');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    Atlas.loader.show();
    const result = await Atlas.ajax.post('auth/login', formData);
    Atlas.loader.hide();

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => {
        window.location.href = result.data.redirect;
      }, 1000);

    } else {
      Atlas.toast.error(result.message);
    }
  });

});