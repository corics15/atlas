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
  async get(url, data = {}) {

    const params = new URLSearchParams();

    Object.entries(data).forEach(([key, value]) => {
      if (
        value !== undefined &&
        value !== null &&
        value !== ''
      ) {
        params.append(key, value);
      }
    });

    const queryString = params.toString();
    const requestUrl = queryString ? `${Atlas.config.baseUrl}${url}?${queryString}` : `${Atlas.config.baseUrl}${url}`;

    try {

      const response = await fetch(
        requestUrl,
        {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        }
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
        message:
          'An unexpected error occurred, please contact your System Administrator.',
        data: []
      };
    }
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.ajax = new AtlasAjax();