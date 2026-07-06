class AtlasModal {

  open(id) {
    $('#' + id).modal('show');
  }

  close(id) {
    $('#' + id).modal('hide');
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.modal = new AtlasModal();