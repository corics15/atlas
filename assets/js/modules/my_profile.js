document.addEventListener('DOMContentLoaded', () => {

  const frmChangePassword = document.getElementById('frmChangePassword');
  const btnChangeAvatar = document.getElementById('btnChangeAvatar');
  const btnUseAvatar = document.getElementById('btnUseAvatar');
  const btnUploadAvatar = document.getElementById('btnUploadAvatar');
  const fileAvatar = document.getElementById('fileAvatar');
  const btnCancelCustomAvatar = document.getElementById('btnCancelCustomAvatar');
  const btnUploadCustomAvatar = document.getElementById('btnUploadCustomAvatar');



  /*** change password event */
  frmChangePassword?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const currentPassword = document.getElementById('txtCurrentPassword').value;
    const newPassword = document.getElementById('txtNewPassword').value;
    const confirmPassword = document.getElementById('txtConfirmPassword').value;

    document.getElementById('errCurrentPassword').textContent = '';
    document.getElementById('errNewPassword').textContent = '';
    document.getElementById('errConfirmPassword').textContent = '';

    if (!currentPassword) {
      document.getElementById('errCurrentPassword').textContent = 'Current password is required.';
      return;
    }

    if (!newPassword) {
      document.getElementById('errNewPassword').textContent = 'New password is required.';
      return;
    }

    if (newPassword !== confirmPassword) {
      document.getElementById('errConfirmPassword').textContent = 'Passwords do not match.';
      return;
    }

    if (newPassword.length < 8) {
      document.getElementById('errNewPassword').textContent = 'Password must be at least 8 characters long.';
      return;
    }

    try {

      const response = await Atlas.ajax.post(
        'my-profile/changePassword',
        {
          current_password: currentPassword,
          new_password: newPassword,
          confirm_password: confirmPassword
        }
      );

      if (!response.success) {
        const message = response.message || 'Unable to update password.';

        if (message.toLowerCase().includes('current password')) {
          document.getElementById('errCurrentPassword').textContent = message;
        } else if (message.toLowerCase().includes('confirmation')) {
          document.getElementById('errConfirmPassword').textContent = message;
        } else if (message.toLowerCase().includes('new password')) {
          document.getElementById('errNewPassword').textContent = message;
        } else {
          Atlas.toast.error(message);
        }

        return;
      }

      Atlas.toast.success(response.message || 'Password updated successfully.');
      setTimeout(() => frmChangePassword.reset(), 3000);
    } catch (error) {
      Atlas.toast.error('An unexpected error occurred while updating your password.');
    } finally { }
  });

  /*** toggle eyes on password */
  document.querySelectorAll('.btn-toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {

      const input = document.getElementById(btn.dataset.target);
      const icon = btn.querySelector('i');

      if (!input) {
        return;
      }

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      icon.classList.toggle('fa-eye', !isPassword);
      icon.classList.toggle('fa-eye-slash', isPassword);
    });
  });

  /*** avatar picker */
  btnChangeAvatar?.addEventListener('click', () => {
    Atlas.modal.open({
      id: 'mdlAvatar',
      title: 'Choose Your Avatar'
    });
  });

  /*** avatar selection */
  let selectedAvatar = null;
  document.querySelectorAll('.avatar-option').forEach(button => {
    button.addEventListener('click', () => {
      document.querySelectorAll('.avatar-option').forEach(item => {
        item.classList.remove('border', 'border-success');
      });

      button.classList.add('border', 'border-success');
      selectedAvatar = button.dataset.avatar;
      document.getElementById('btnUseAvatar').disabled = false;
    });
  });

  /*** use predefined avatar */
  btnUseAvatar?.addEventListener('click', async () => {
    if (!selectedAvatar) {
      return;
    }

    btnUseAvatar.disabled = true;

    try {
      const result = await Atlas.ajax.post(
        'my-profile/selectAvatar',
        {
          avatar: selectedAvatar
        }
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      const avatarImage = document.querySelector('.box-profile .profile-user-img');
      if (avatarImage && result.data.avatar) {
        avatarImage.src = result.data.avatar;
      }

      Atlas.toast.success(result.message);
      setProfileAvatar(result.data?.avatar);
      $('#mdlAvatar').modal('hide');
      selectedAvatar = null;

      document.querySelectorAll('.avatar-option').forEach(item => {
        item.classList.remove('border', 'border-success');
      });

      btnUseAvatar.disabled = true;

    } finally {

      btnUseAvatar.disabled = false;

    }
  });

  /*** use custom avatar selection */
  btnUploadAvatar?.addEventListener('click', () => fileAvatar?.click());
  fileAvatar?.addEventListener('change', () => {
    const file = fileAvatar.files?.[0];

    if (!file) {
      return;
    }

    const allowedTypes = [
      'image/jpeg',
      'image/png',
      'image/webp'
    ];

    if (!allowedTypes.includes(file.type)) {
      Atlas.toast.error('Please select a JPG, PNG, or WEBP image.');

      fileAvatar.value = '';
      return;
    }

    const maxSize = 2 * 1024 * 1024;

    if (file.size > maxSize) {
      Atlas.toast.error('Avatar image must not exceed 2 MB.');

      fileAvatar.value = '';
      return;
    }

    const previewUrl = URL.createObjectURL(file);
    const preview = document.getElementById('customAvatarPreview');
    const previewImage = document.getElementById('imgCustomAvatarPreview');
    const fileName = document.getElementById('txtCustomAvatarName');

    if (preview && previewImage && fileName) {
      previewImage.src = previewUrl;
      fileName.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
      preview.classList.remove('d-none');
      Atlas.toast.success(`Image selected, click on Upload Picture to update`);
    }
  });

  /*** cancel uploading of custom avatar */
  btnCancelCustomAvatar?.addEventListener('click', () => {
    const preview = document.getElementById('customAvatarPreview');
    const previewImage = document.getElementById('imgCustomAvatarPreview');
    const fileName = document.getElementById('txtCustomAvatarName');

    if (preview) {
      preview.classList.add('d-none');
    }

    if (previewImage) {
      previewImage.src = '';
    }

    if (fileName) {
      fileName.textContent = '';
    }

    if (fileAvatar) {
      fileAvatar.value = '';
    }
  });

  /*** uploadd custom avatar */
  btnUploadCustomAvatar?.addEventListener('click', async () => {
    const file = fileAvatar?.files?.[0];

    if (!file) {
      Atlas.toast.warning('Please select an image first.');
      return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {

      btnUploadCustomAvatar.disabled = true;
      const result = await Atlas.ajax.post(
        `my-profile/uploadAvatar`,
        formData
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      /*** apply the changes */
      // const avatarImage = document.querySelector('.box-profile .profile-user-img');
      // if (avatarImage && result.data?.avatar) {
      //   avatarImage.src = result.data.avatar;
      // }
      setProfileAvatar(result.data?.avatar);
      /*** end apply */
      $('#mdlAvatar').modal('hide');
      fileAvatar.value = '';
      document.getElementById('customAvatarPreview')?.classList.add('d-none');

    } catch (error) {

      Atlas.toast.error('An unexpected error occurred while uploading your avatar.');

    } finally {

      btnUploadCustomAvatar.disabled = false;

    }
  });

});

const setProfileAvatar = (avatarUrl) => {

  if (!avatarUrl) {
    return;
  }

  /*** My Profile */
  const profileContainer = document.getElementById('profileAvatarContainer');

  if (profileContainer) {
    let profileImage = profileContainer.querySelector('#imgProfileAvatar');
    if (!profileImage || profileImage.tagName !== 'IMG') {
      profileImage = document.createElement('img');

      profileImage.id = 'imgProfileAvatar';
      profileImage.alt = 'User Avatar';
      profileImage.className = 'img-fluid img-circle elevation-2';

      profileImage.style.width = '110px';
      profileImage.style.height = '110px';
      profileImage.style.objectFit = 'contain';

      profileContainer.innerHTML = '';
      profileContainer.appendChild(profileImage);
    }
    profileImage.src = avatarUrl;
  }

  /*** Navbar   */
  const navbarAvatar = document.getElementById('navbarUserAvatar');
  if (navbarAvatar) {
    navbarAvatar.src = avatarUrl;

  } else {
    const navbarLink = document.querySelector('.main-header .nav-item.dropdown > a.nav-link');
    if (navbarLink) {
      const icon = navbarLink.querySelector('i.fa-user-circle');
      if (icon) {
        const image = document.createElement('img');

        image.id = 'navbarUserAvatar';
        image.src = avatarUrl;
        image.alt = 'User Avatar';
        image.className = 'img-circle mr-1';

        image.style.width = '28px';
        image.style.height = '28px';
        image.style.objectFit = 'contain';

        icon.replaceWith(image);
      }
    }
  }
  /*** end Navbar */

  /*** Sidebar */
  const sidebarAvatar = document.getElementById('sidebarUserAvatar');
  if (sidebarAvatar) {
    sidebarAvatar.src = avatarUrl;

  } else {
    const sidebarUserPanel = document.querySelector('.main-sidebar .user-panel');

    if (sidebarUserPanel) {
      const icon = sidebarUserPanel.querySelector('.image i.fa-user-circle');
      if (icon) {
        const image = document.createElement('img');

        image.id = 'sidebarUserAvatar';
        image.src = avatarUrl;
        image.alt = 'User Avatar';
        image.className = 'img-circle elevation-2';

        image.style.width = '34px';
        image.style.height = '34px';
        image.style.objectFit = 'contain';

        icon.replaceWith(image);
      }
    }
  }
  /*** end Sidebar */
};

// const setProfileAvatar = (avatarUrl) => {
//   if (!avatarUrl) {
//     return;
//   }
//   const container = document.getElementById('profileAvatarContainer');

//   if (!container) {
//     return;
//   }

//   let avatarImage = container.querySelector('#imgProfileAvatar');
//   if (!avatarImage || avatarImage.tagName !== 'IMG') {
//     avatarImage = document.createElement('img');

//     avatarImage.id = 'imgProfileAvatar';
//     avatarImage.alt = 'User Avatar';
//     avatarImage.className = 'img-fluid img-circle elevation-2';

//     avatarImage.style.width = '110px';
//     avatarImage.style.height = '110px';
//     avatarImage.style.objectFit = 'contain';

//     container.innerHTML = '';
//     container.appendChild(avatarImage);
//   }

//   avatarImage.src = avatarUrl;
// };