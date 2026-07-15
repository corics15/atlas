document.addEventListener('DOMContentLoaded', () => {

  const btnLogout = document.getElementById('btnLogout');

  if (!btnLogout) {
    return;
  }

  btnLogout.addEventListener('click', async (e) => {
    e.preventDefault();

    const confirmed = await Atlas.dialog.confirm(
      'Logout',
      'Are you sure you want to logout?'
    );

    if (!confirmed) {
      return;
    }

    window.location = btnLogout.href;
  });
});