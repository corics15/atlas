class AtlasPrint {

  post(url, data, target = '_blank') {
    const form = document.createElement('form');

    form.method = 'POST';
    form.action = Atlas.config.baseUrl + url;
    form.target = target;

    /*** for array of IDs */
    if (Array.isArray(data)) {
      data.forEach(id => {
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;

        form.appendChild(input);
      });
    }

    /*** object */
    else {
      Object.entries(data).forEach(([key, value]) => {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = key;
        input.value = value ?? '';

        form.appendChild(input);
      });
    }

    document.body.appendChild(form);
    form.submit();
    form.remove();
  }
}

window.Atlas = window.Atlas || {};
Atlas.print = new AtlasPrint();