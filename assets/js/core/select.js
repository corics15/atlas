class AtlasSelect {

  init(selector, modal = null) {
    const options = {};
    if (modal) {
      options.dropdownParent = $(modal);
    }
    $(selector).select2({
      theme: 'bootstrap4',
      width: '100%',
      ...options
    });
  }
}

window.Atlas = window.Atlas || {};
Atlas.select = new AtlasSelect();