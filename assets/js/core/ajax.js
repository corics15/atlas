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

// const inputs = document.querySelectorAll('input');
// inputs.forEach(input => {
//   input.setAttribute('autocomplete', 'off');
// });


window.Atlas = window.Atlas || {};
window.Atlas.ajax = new AtlasAjax();