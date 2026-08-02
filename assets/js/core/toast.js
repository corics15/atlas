class AtlasToast {

  success(message) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: message,
      theme: 'bootstrap-4-dark',
      toast: true,
      position: 'top-end',
      timer: 6000,
      width: 'auto',
      customClass: {
        popup: 'auto-width-toast'
      }
    });
  }

  error(message) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: message,
      theme: 'bootstrap-4-dark',
      toast: true,
      position: 'top-end',
      timer: 6000,
      width: 'auto',
      customClass: {
        popup: 'auto-width-toast'
      }
    });
  }

  warning(message) {
    Swal.fire({
      icon: 'warning',
      text: message,
      theme: 'bootstrap-4-dark',
      toast: true,
      position: 'top-end',
      timer: 6000,
      width: 'auto',
      customClass: {
        popup: 'auto-width-toast'
      }
    });
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.toast = new AtlasToast();