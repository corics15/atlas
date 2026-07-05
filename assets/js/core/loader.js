class AtlasLoader {

  show() {
    Swal.showLoading();
  }

  hide() {
    Swal.close();
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.loader = new AtlasLoader();