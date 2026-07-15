class AtlasPrint {

  post(url, ids, target = '_blank') {

    const form = document.createElement('form');

    form.method = 'POST';
    form.action = Atlas.config.baseUrl + url;
    form.target = target;

    ids.forEach(id => {
      const input = document.createElement('input');

      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;

      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    form.remove();
  }
}

window.Atlas = window.Atlas || {};
Atlas.print = new AtlasPrint();