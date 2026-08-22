class AtlasExcel {
  download(table, options = {}) {

    if (!table) {
      Atlas.toast.warning('Table not found.');
      return;
    }

    if (typeof TableToExcel === 'undefined') {
      Atlas.toast.error('Excel export library is not available.');
      return;
    }

    const fileName = options.fileName || 'Export.xlsx';
    const sheetName = options.sheetName || 'Sheet1';
    const reportTitle = options.title || 'ATLAS Report';
    const generatedBy = options.generatedBy || Atlas.config?.userName || '';
    const generatedOn = new Date().toLocaleString();

    /*** clone source table */
    const exportTable = table.cloneNode(true);

    /*** grab the excel value for truncated texts */
    exportTable.querySelectorAll('[data-excel-value]').forEach(cell => {
      cell.textContent = cell.dataset.excelValue;
      cell.removeAttribute('data-excel-value');
    });

    /*** sanitize numeric cells */
    exportTable.querySelectorAll('[data-t="n"]').forEach(cell => {
      const rawValue = cell.textContent.trim().replace(/,/g, '');
      if (rawValue === '') {
        cell.textContent = '0';
        return;
      }
      const numericValue = Atlas.format.parseNumber(rawValue);
      cell.textContent = Number.isFinite(numericValue) ? numericValue.toString() : '0';
    });

    const thead = exportTable.querySelector('thead');
    if (thead) {

      const columnCount = exportTable.querySelectorAll('thead tr:last-child th:not([data-exclude="true"])').length;

      /*** report title */
      const titleRow = document.createElement('tr');
      const titleCell = document.createElement('td');

      titleCell.colSpan = columnCount;
      titleCell.textContent = reportTitle;

      titleCell.setAttribute('data-f-sz', '14');
      titleCell.setAttribute('data-f-bold', 'true');

      titleRow.appendChild(titleCell);

      /*** generated on / by */
      const infoRow = document.createElement('tr');
      const infoCell = document.createElement('td');

      infoCell.colSpan = columnCount;
      infoCell.textContent = `Generated on: ${generatedOn}` + (generatedBy ? ` by ${generatedBy}` : '');

      infoCell.setAttribute('data-f-sz', '9');
      infoRow.appendChild(infoCell);

      /*** insert metadata before normal headings */
      thead.insertBefore(infoRow, thead.firstChild);
      thead.insertBefore(titleRow, thead.firstChild);
    }

    /*** style actual column headings */
    exportTable.querySelectorAll('thead tr:last-child th').forEach(cell => {
      if (cell.dataset.exclude === 'true') {
        return;
      }
      cell.setAttribute('data-f-sz', '10');
      cell.setAttribute('data-f-bold', 'true');
      cell.setAttribute('data-fill-color', 'FFD8E4BC');
    });

    /*** style report body */
    exportTable.querySelectorAll('tbody tr').forEach(row => {
      row.querySelectorAll('td').forEach(cell => {
        cell.setAttribute('data-f-sz', '9');
      });
    });

    /*** optional totals row */
    if (Array.isArray(options.totals) && options.totals.length > 0) {
      const tbody = exportTable.querySelector('tbody');

      if (tbody) {
        const row = document.createElement('tr');
        const visibleColumnCount = exportTable.querySelectorAll('thead tr:last-child th:not([data-exclude="true"])').length;

        for (let i = 0; i < visibleColumnCount; i++) {
          const cell = document.createElement('td');
          const total = options.totals.find(item => item.column === i);
          if (total) {
            cell.textContent = total.value;
            if (total.type) {
              cell.setAttribute('data-t', total.type);
            }
            if (total.format) {
              cell.setAttribute('data-num-fmt', total.format);
            }
          }
          /*** totals styling */
          cell.setAttribute('data-f-sz', '10');
          cell.setAttribute('data-f-bold', 'true');
          cell.setAttribute('data-a-h', 'right');
          row.appendChild(cell);
        }
        tbody.appendChild(row);
      }
    }

    TableToExcel.convert(
      exportTable,
      {
        name: `${fileName}-` + Math.random().toString(36).substring(2, 7) + `.xlsx`,
        sheet: {
          name: sheetName
        }
      }
    );
  }
}

window.Atlas = window.Atlas || {};
Atlas.excel = new AtlasExcel();