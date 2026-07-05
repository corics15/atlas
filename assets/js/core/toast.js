class AtlasToast {

  success(message) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: message
    });
  }

  error(message) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: message
    });
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.toast = new AtlasToast();