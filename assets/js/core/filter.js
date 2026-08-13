class AtlasFilter {

  encode(data) {
    const json = JSON.stringify(data);

    return btoa(json)
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/, '');
  }


  decode(encoded) {
    if (!encoded) {
      return {};
    }

    try {

      let base64 = String(encoded)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

      while (base64.length % 4) {
        base64 += '=';
      }

      const json = atob(base64);
      const data = JSON.parse(json);

      return (
        data &&
        typeof data === 'object' &&
        !Array.isArray(data)
      )
        ? data
        : {};

    } catch (error) {
      return {};
    }
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.filter = new AtlasFilter();

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-atlas-filter]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();

      const params = new URLSearchParams(
        new FormData(form)
      );

      const filter = {};

      for (const [key, value] of params.entries()) {
        filter[key] = value;
      }

      const encoded = Atlas.filter.encode(filter);
      const url = new URL(
        form.action || window.location.href,
        window.location.origin
      );

      url.search = '';
      if (encoded) {
        url.searchParams.set('filter', encoded);
      }
      window.location.href = url.toString();
    });
  });
});