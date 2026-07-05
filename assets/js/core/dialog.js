class AtlasDialog {

  async confirm(title, text) {

    return await Swal.fire({
      title,
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No'
    });

  }

}

window.Atlas = window.Atlas || {};
window.Atlas.dialog = new AtlasDialog();