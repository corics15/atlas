class AtlasProductFinder {

  constructor() {
    this.currentRow = null;
    this.selectedIndex = -1;
  }

  async load() {
    const result = await Atlas.ajax.get('product_finder/list');

    const tbody = document.getElementById('tblProductFinder');

    tbody.innerHTML = '';

    result.data.forEach(product => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr class="pf-row" data-id="${product.id}"
          data-barcode="${product.barcode ?? ''}"
          data-supplier="${product.supplier_name ?? ''}"
          data-description="${product.description ?? ''}"
          data-uom="${product.uom ?? ''}"
          data-price="${product.srp}">
            <td class="text-center">${product.barcode ?? ''}</td>
            <td>${product.supplier_name ?? ''}</td>
            <td>${product.description ?? ''}</td>
            <td class="text-center">${product.uom ?? ''}</td>
            <td class="text-right">${Number(product.srp).toFixed(2)}</td>
        </tr>
      `);

    });
  }

  async show(row) {
    this.currentRow = row;

    const search = document.getElementById('searchInput');
    const tbody = document.getElementById('tblProductFinder');

    search.value = '';
    tbody.innerHTML = `<tr>
                          <td colspan="5" class="text-center text-muted font-sm">
                            Start typing to search products...
                          </td>
                        </tr>
                      `;

    $('#mdlProductFinder').modal('show');
    setTimeout(() => search.focus(), 150);
  }

  async search(keyword) {
    const tbody = document.getElementById('tblProductFinder');
    const lblRecordCount = document.getElementById('pfRecordCount');

    if (keyword.trim().length < 2) {
      lblRecordCount.textContent = '';

      tbody.innerHTML = `<tr>
                            <td colspan="5" class="text-center text-muted font-sm">
                              Type at least 2 characters...
                            </td>
                          </tr>
                        `;
      return;
    }

    const result = await Atlas.ajax.get(
      'product_finder/search?q=' +
      encodeURIComponent(keyword)
    );

    tbody.innerHTML = '';

    if (!result.data.length) {
      lblRecordCount.textContent = '0 product found.';

      tbody.innerHTML = `<tr>
                            <td colspan="5" class="text-center text-muted font-sm">
                              No products found.
                            </td>
                          </tr>
                        `;
      return;
    }

    lblRecordCount.textContent =
      `${result.data.length.toLocaleString()} ${result.data.length === 1
        ? 'record'
        : 'records'
      } found.`;

    result.data.forEach(product => {
      tbody.insertAdjacentHTML('beforeend', `
                                  <tr class="pf-row"
                                      data-id="${product.id}"
                                      data-barcode="${product.barcode ?? ''}"
                                      data-supplier="${product.supplier_name ?? ''}"
                                      data-description="${product.description ?? ''}"
                                      data-uom="${product.uom ?? ''}"
                                      data-price="${product.srp}">
                                    <td class="text-center">${product.barcode ?? ''}</td>
                                    <td>${product.supplier_name ?? ''}</td>
                                    <td>${product.description ?? ''}</td>
                                    <td class="text-center">${product.uom}</td>
                                    <td class="text-right">${Number(product.srp).toFixed(2)}</td>
                                  </tr>
                                `);
    });

    this.highlight(0);
  }

  hide() {
    this.currentRow = null;
    this.selectedIndex = -1;

    document.getElementById('searchInput').value = '';
    document.getElementById('tblProductFinder').innerHTML = `<tr>
                                                                <td colspan="5" class="text-center text-muted font-sm">
                                                                  Start typing to search products...
                                                                </td>
                                                              </tr>
                                                            `;

    document.getElementById('pfRecordCount').textContent = '';

    $('#mdlProductFinder').modal('hide');
  }

  highlight(index) {
    const rows = document.querySelectorAll('.pf-row');

    rows.forEach(r => r.classList.remove('tr-highlighter'));

    if (!rows.length) {
      this.selectedIndex = -1;
      return;
    }

    if (index < 0) index = 0;
    if (index >= rows.length) index = rows.length - 1;
    rows[index].classList.add('tr-highlighter');

    rows[index].scrollIntoView({
      block: 'nearest'
    });

    this.selectedIndex = index;
  }
}

window.Atlas = window.Atlas || {};
Atlas.productFinder = new AtlasProductFinder();

/*** navigation after product search */
document.getElementById('searchInput').addEventListener('keydown', e => {
  const rows = document.querySelectorAll('.pf-row');

  if (!rows.length) return;

  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault();
      Atlas.productFinder.highlight(
        Atlas.productFinder.selectedIndex + 1
      );
      break;

    case 'ArrowUp':
      e.preventDefault();
      Atlas.productFinder.highlight(
        Atlas.productFinder.selectedIndex - 1
      );
      break;

    case 'Enter':
      e.preventDefault();
      rows[
        Atlas.productFinder.selectedIndex
      ]?.click();
      break;
  }
});

document.addEventListener('click', (e) => {
  const tr = e.target.closest('.pf-row');

  if (!tr) {
    return;
  }

  const row = Atlas.productFinder.currentRow;

  populateProductRow(row, {
    id: tr.dataset.id,
    barcode: tr.dataset.barcode,
    supplier_name: tr.dataset.supplier,
    description: tr.dataset.description,
    uom: tr.dataset.uom,
    srp: tr.dataset.price
  });

  Atlas.productFinder.hide();
  row.querySelector('.po-qty').focus();
});

let productFinderTimer = null;
document.getElementById('searchInput').addEventListener('input', (e) => {
  clearTimeout(productFinderTimer);

  productFinderTimer = setTimeout(() => {
    Atlas.productFinder.search(e.target.value);
  }, 250);
});