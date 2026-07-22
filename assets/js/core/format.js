class AtlasFormat {

  number(value, decimals = 2) {
    return Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  amount(value) {
    return this.number(value, 2);
  }

  integer(value) {
    return this.number(value, 0);
  }

  percent(value, decimals = 2) {
    return `${this.number(value, decimals)}%`;
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.format = new AtlasFormat();