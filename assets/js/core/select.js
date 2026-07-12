class AtlasSelect {

  init(control, modal = null) {
    const options = {};
    const element =
      typeof control === 'string'
        ? $(control)
        : $(control);

    if (modal) {
      options.dropdownParent = $(modal);
    }

    $(control).select2({
      theme: 'bootstrap4',
      width: '100%',
      ...options
    });
  }

  onChange(selector, callback) {
    $(document).on('change', selector, function () {
      const option = this.options[this.selectedIndex];
      callback(option, this);
    });
  }
}

window.Atlas = window.Atlas || {};
Atlas.select = new AtlasSelect();

$(document).on('select2:open', () => {
  setTimeout(() => {
    document.querySelector('.select2-container--open .select2-search__field').focus();
  }, 50);
});