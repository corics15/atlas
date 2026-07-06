class AtlasDialog {

  async confirm(title, text) {
    const result = await Swal.fire({
      title,
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      theme: 'bootstrap-4-dark'
    });

    return result.isConfirmed;
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.dialog = new AtlasDialog();