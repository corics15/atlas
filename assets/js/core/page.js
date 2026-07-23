class AtlasPage {

  refresh() {
    location.reload();
  }

  redirect(url, params = null) {
    let fullUrl = Atlas.config.baseUrl + url;

    if (params) {
      const query = new URLSearchParams(params);

      fullUrl += '?' + query.toString();
    }
    location.href = fullUrl;
  }

  back(defaultUrl = '') {
    const returnUrl = sessionStorage.getItem('atlas_return_url');

    if (returnUrl) {
      sessionStorage.removeItem('atlas_return_url');
      location.href = returnUrl;
      return;
    }

    if (defaultUrl) {
      this.redirect(defaultUrl);
      return;
    }

    history.back();
  }

  remember() {
    sessionStorage.setItem(
      'atlas_return_url',
      location.pathname + location.search
    );
  }

  redirectRemember(url, params = null) {
    this.remember();
    this.redirect(url, params);
  }

  open(url, target = '_blank') {
    window.open(
      Atlas.config.baseUrl + url,
      target
    );
  }
}

window.Atlas = window.Atlas || {};
Atlas.page = new AtlasPage();