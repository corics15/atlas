document.addEventListener('DOMContentLoaded', () => {

  const form = document.getElementById('frmCompany');

  if (!form) {
    return;
  }

  const logoInput = document.getElementById('companyLogo');
  const logoPreview = document.getElementById('companyLogoPreview');
  const logoPlaceholder = document.getElementById('companyLogoPlaceholder');

  logoInput?.addEventListener('change', () => {
    const file = Atlas.upload.getFile(logoInput);

    if (!file) {
      return;
    }

    const validation = Atlas.upload.validate(file, {
      types: [
        'image/png',
        'image/jpeg'
      ],
      maxSize: 2 * 1024 * 1024
    });

    if (!validation.valid) {
      Atlas.toast.warning(validation.message);
      Atlas.upload.clear(logoInput);
      return;
    }

    Atlas.upload.preview(
      file,
      logoPreview
    );

    logoPlaceholder.hidden = true;
    const label = logoInput.nextElementSibling;
    if (label) {
      label.textContent = file.name;
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const button = document.getElementById('btnSaveCompany');
    const formData = new FormData();

    formData.append('id', Atlas.format.integer(document.getElementById('companyId').value));
    formData.append('company_name', document.getElementById('companyName').value.trim());
    formData.append('address', document.getElementById('address').value.trim());
    formData.append('telephone_no', document.getElementById('telephoneNo').value.trim());
    formData.append('mobile_no', document.getElementById('mobileNo').value.trim());
    formData.append('email_address', document.getElementById('emailAddress').value.trim());
    formData.append('tin_no', document.getElementById('tinNo').value.trim());
    formData.append('current_logo', document.getElementById('currentLogo').value);

    const logoInput = document.getElementById('companyLogo');

    if (logoInput?.files.length > 0) {

      formData.append(
        'logo',
        logoInput.files[0]
      );
    }

    const companyName = document.getElementById('companyName').value.trim();

    if (!companyName) {
      Atlas.toast.warning('Company Name is required.');
      return;
    }

    button.disabled = true;

    try {

      const result = await Atlas.ajax.post(
        'company/update',
        formData
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      setTimeout(() => Atlas.page.refresh(), 1200);
    }
    finally {
      button.disabled = false;
    }
  });

});