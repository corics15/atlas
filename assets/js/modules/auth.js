document.addEventListener('DOMContentLoaded', () => {

  const form = document.getElementById('frmLogin');
  const submitButton = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const originalText = submitButton.innerHTML;

    submitButton.disabled = true;
    submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i>Signing in...`;

    // Atlas.loader.show();

    const result = await Atlas.ajax.post('auth/login', formData);

    // Atlas.loader.hide();

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => window.location.href = result.data.redirect, 1000);

    } else {
      Atlas.toast.error(result.message);
      submitButton.disabled = false;
      submitButton.innerHTML = originalText;
    }
  });

});