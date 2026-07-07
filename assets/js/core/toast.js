class AtlasToast {

  success(message) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: message,
      theme: 'bootstrap-4-dark',
      toast: true,
      position: 'top-end',
      timer: 3000
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
      timer: 3000
    });
  }

  warning(message) {
    Swal.fire({
      icon: 'warning',
      text: message,
      theme: 'bootstrap-4-dark',
      toast: true,
      position: 'top-end',
      timer: 3000
    });
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.toast = new AtlasToast();