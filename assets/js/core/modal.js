class AtlasModal {

  open(options) {
    if (typeof options === 'string') {
      $('#' + options).modal('show');
      return;
    }

    if (options.title) {
      const title = document.querySelector(
        '#' + options.id + ' .modal-title'
      );
      if (title) {
        title.textContent = options.title;
      }
    }

    $('#' + options.id).modal('show');
  }

  close(id) {
    $('#' + id).modal('hide');
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.modal = new AtlasModal();