class AtlasId {

  encode(id) {
    return btoa(String(id))
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/, '');
  }

  decode(value) {
    value = String(value)
      .replace(/-/g, '+')
      .replace(/_/g, '/');
    while (value.length % 4) {
      value += '=';
    }
    return atob(value);
  }

}

window.Atlas = window.Atlas || {};
window.Atlas.id = new AtlasId();