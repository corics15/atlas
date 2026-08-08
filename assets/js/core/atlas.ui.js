class AtlasUI {

  init() {
    this.initTooltips();
  }

  initTooltips(container = document) {

    $(container)
      .find('[data-toggle="tooltip"]')
      .tooltip({
        container: 'body',
        html: true,
        trigger: 'hover'
      });

  }

}

window.Atlas = window.Atlas || {};
window.Atlas.ui = new AtlasUI();