class AtlasAjax {
  async post(url, data = {}) {
    const response = await fetch(Atlas.config.baseUrl + url, {
      method: 'POST',
      body: data
    });

    return await response.json();
  }

  async get(url) {
    const response = await fetch(Atlas.config.baseUrl + url);
    return await response.json();
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.ajax = new AtlasAjax();