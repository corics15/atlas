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

  back() {
    history.back();
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