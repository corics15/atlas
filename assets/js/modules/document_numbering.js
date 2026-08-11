document.addEventListener('DOMContentLoaded', () => {

  const table = document.getElementById('tblDocumentNumbering');

  if (!table) {
    return;
  }

  const getPreview = (row) => {
    const prefix = row.querySelector('.dn-prefix')?.value.trim() ?? '';
    const includeYear = row.querySelector('.dn-include-year')?.checked ?? false;
    const yearFormat = row.querySelector('.dn-year-format')?.value ?? 'YYYY';
    const separator = row.querySelector('.dn-separator')?.value ?? '-';
    const numberLength = Number(row.querySelector('.dn-number-length')?.value) || 1;
    const nextNumber = Number(row.querySelector('.dn-next-number')?.value) || 1;

    const currentYear = new Date().getFullYear();

    let year = '';

    if (includeYear) {

      year = yearFormat === 'YY'
        ? String(currentYear).slice(-2)
        : String(currentYear);
    }

    const number = String(nextNumber).padStart(Atlas.format.integer(numberLength), '0');
    const parts = [];

    if (prefix !== '') {
      parts.push(prefix);
    }

    if (year !== '') {
      parts.push(year);
    }

    parts.push(number);

    return parts.join(separator);
  };

  const updatePreview = (row) => {
    const preview = row.querySelector('.dn-preview');

    if (!preview) {
      return;
    }

    preview.textContent = getPreview(row);
  };

  table.querySelectorAll('tbody tr').forEach(row => {
    updatePreview(row);
    row.querySelectorAll('input, select').forEach(control => {
      control.addEventListener('input', () => updatePreview(row));
      control.addEventListener('change', () => updatePreview(row));
    });
  });

  table.addEventListener('click', async (e) => {
    const button = e.target.closest('.btn-save-numbering');

    if (!button) {
      return;
    }

    const row = button.closest('tr');

    if (!row) {
      return;
    }

    const id = Atlas.format.parseNumber(row.dataset.id);

    const data = {
      id: id,
      prefix: row.querySelector('.dn-prefix')?.value ?? '',
      include_year: row.querySelector('.dn-include-year')?.checked ? 1 : 0,
      year_format: row.querySelector('.dn-year-format')?.value ?? 'YYYY',
      separator: row.querySelector('.dn-separator')?.value ?? '-',
      number_length: Atlas.format.parseNumber(row.querySelector('.dn-number-length')?.value) || 1,
      next_number: Atlas.format.parseNumber(row.querySelector('.dn-next-number')?.value) || 1,
      is_active: row.querySelector('.dn-active')?.checked ? 1 : 0
    };

    button.disabled = true;

    try {
      const result = await Atlas.ajax.post(
        'document-numbering/update',
        data
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
    }
    finally {
      button.disabled = false;
    }
  });
});