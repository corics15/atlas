class AtlasFormat {

  /** format number with fixed decimals */
  number(value, decimals = 2) {
    return Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  /** format as amount with 2 decimals */
  amount(value) {
    return this.number(value, 2);
  }

  /** format as integer (no decimals) */
  integer(value) {
    return this.number(value, 0);
  }

  /** format as percentage string */
  percent(value, decimals = 2) {
    return `${this.number(value, decimals)}%`;
  }

  /*** convert formatted string back to number (with decimals) */
  parseNumber(str) {
    if (!str) return 0;
    return parseFloat(str.replace(/,/g, ''));
  }

  /** convert text to formatted amount */
  amountFromText(text) {
    return this.amount(this.parseNumber(text || 0));
  }

  /*** convert DB date (YYYY-MM-DD) to MM/DD/YYYY, optional with timestamp */
  formatDate(dbDate, includeTime = false) {
    if (!dbDate) return '';

    const [datePart, timePart] = dbDate.split(' ');
    const [year, month, day] = datePart.split('-');

    let formatted = `${month}/${day}/${year}`;
    if (includeTime && timePart) {
      formatted += ` ${timePart}`;
    }
    return formatted;
  }
}

window.Atlas = window.Atlas || {};
window.Atlas.format = new AtlasFormat();