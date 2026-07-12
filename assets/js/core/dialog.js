class AtlasDialog {

  async confirm(title, text) {
    const result = await Swal.fire({
      title,
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      theme: 'bootstrap-4-dark'
    });

    return result.isConfirmed;
  }

  async saved(options) {
    const result = await Swal.fire({
      icon: 'success',
      title: `${options.title} Saved`,
      html: `
        <div class="text-center">
          <span>Document No.</span><br>
          <div class="mt-2">${options.documentNo}</div>
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

}

window.Atlas = window.Atlas || {};
window.Atlas.dialog = new AtlasDialog();