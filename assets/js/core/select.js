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

  onChange(selector, callback) {
    $(selector).on('change', function () {
      const option = this.options[this.selectedIndex];
      callback(option, this);
    });
  }
}

window.Atlas = window.Atlas || {};
Atlas.select = new AtlasSelect();