class AtlasDialog {

  async confirm(title, html) {
    const result = await Swal.fire({
      title,
      html,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      theme: 'bootstrap-4-dark',
    });

    return result.isConfirmed;
  }

  async textarea(options) {
    const result = await Swal.fire({
      icon: options.icon || 'warning',
      title: options.title,
      html: options.html || '',
      input: 'textarea',
      inputLabel: options.inputLabel || '',
      inputPlaceholder: options.inputPlaceholder || '',
      inputValue: options.inputValue || '',
      inputAttributes: {
        maxlength: options.maxlength || 255
      },
      inputValidator: value => {
        if (options.required && !value.trim()) {
          return options.requiredMessage || 'This field is required.';
        }
      },
      showCancelButton: true,
      confirmButtonText: options.confirmText || 'OK',
      cancelButtonText: options.cancelText || 'Cancel',
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      theme: 'bootstrap-4-dark'
    });

    if (!result.isConfirmed) {
      return null;
    }

    return result.value.trim();
  }

  async saved(options) {
    const result = await Swal.fire({
      icon: 'success',
      title: `${options.title} Saved`,
      html: `
        <div class="text-center">
          <span>Document No.</span><br>
          <div class="mt-2 font-weight-bold">${options.documentNo}</div>

          ${options.message ? `
            <hr>
            <div class="text-left small">
              ${options.message}
            </div>
          ` : ''}
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: options.confirmText || 'New',
      cancelButtonText: options.cancelText || 'Continue Editing',
      reverseButtons: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      theme: 'bootstrap-4-dark'
    });

    return result.isConfirmed
      ? 'new'
      : 'stay';
  }

  async choice(options) {
    const result = await Swal.fire({
      title: options.title,
      html: options.html || '',
      icon: options.icon || 'question',

      showConfirmButton: true,
      showDenyButton: true,
      showCancelButton: true,

      confirmButtonText: options.confirmText || 'Yes',
      denyButtonText: options.denyText || 'No',
      cancelButtonText: options.cancelText || 'Wait',

      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,

      theme: 'bootstrap-4-dark'
    });

    if (result.isConfirmed) {
      return 'confirm';
    }

    if (result.isDenied) {
      return 'deny';
    }

    return 'cancel';
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.dialog = new AtlasDialog();