class AtlasAjax {

  async post(url, data = {}) {
    const options = {
      method: 'POST',
      headers: {
        'Accept': 'application/json'
      }
    };

    if (data instanceof FormData) {
      options.body = data;
    } else {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(data);
    }

    const response = await fetch(
      Atlas.config.baseUrl + url,
      options
    );

    return await response.json();
  }

  async get(url) {
    const response = await fetch(
      Atlas.config.baseUrl + url,
      {
        headers: {
          'Accept': 'application/json'
        }
      }
    );
    return await response.json();
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.ajax = new AtlasAjax();