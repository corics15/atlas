class AtlasAjax {

  /*** post */
  async post(url, data = {}) {
    const options = {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', /*** for backward compatibility */
      }
    };

    if (data instanceof FormData) {
      options.body = data;
    } else {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(data);
    }

    try {
      const response = await fetch(
        `${Atlas.config.baseUrl}${url}`,
        options
      );

      if (!response.ok) {
        return {
          success: false,
          message:
            'Unable to complete your request. Please try again or contact your System Administrator.',
          data: []
        };
      }

      return await response.json();

    } catch (e) {

      return {
        success: false,
        message: 'An unexpected error occurred, please contact your System Administrator.',
        data: []
      };
    }
  }

  /*** get */
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