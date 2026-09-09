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

  async number(options) {
    const result = await Swal.fire({
      icon: options.icon || 'question',
      title: options.title,
      html: options.html || '',
      input: 'number',
      inputPlaceholder: options.inputPlaceholder || '',
      inputValue: options.inputValue || '',
      inputAttributes: {
        min: options.min ?? 0.0001,
        step: options.step || 'any'
      },
      inputValidator: value => {
        const number = Atlas.format.parseNumber(value);

        if (!value || number <= 0) {
          return options.requiredMessage || 'Please enter a valid value.';
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

    return Atlas.format.parseNumber(result.value);
  }

  async details(options) {
    const result = await Swal.fire({
      icon: options.icon || 'question',
      title: options.title,
      html: `
          <div class="text-left">
            <label class="font-sm mb-1 text-olive">${options.firstLabel || 'Reference No.'}</label>
            <input id="atlasDialogFirst" type="text" class="form-control form-control-sm bg-transparent text-white-50 mb-3" placeholder="${options.firstPlaceholder || ''}" value="${options.firstValue || ''}">
            <label class="font-sm mb-1 text-olive">${options.secondLabel || 'Remarks'}</label>
            <textarea id="atlasDialogSecond" class="form-control form-control-sm bg-transparent text-white-50" rows="3" placeholder="${options.secondPlaceholder || ''}">${options.secondValue || ''}</textarea>
          </div>
        `,
      showCancelButton: true,
      confirmButtonText: options.confirmText || 'OK',
      cancelButtonText: options.cancelText || 'Cancel',
      focusConfirm: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      theme: 'bootstrap-4-dark',
      preConfirm: () => ({
        first: document.getElementById('atlasDialogFirst').value.trim(),
        second: document.getElementById('atlasDialogSecond').value.trim()
      })
    });

    if (!result.isConfirmed) {
      return null;
    }

    return result.value;
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.dialog = new AtlasDialog();