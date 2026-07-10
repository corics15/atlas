class AtlasProductFinder {

  constructor() {
    this.currentRow = null;
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
    await this.load();

    $('#mdlProductFinder').modal('show');
  }

  hide() {
    $('#mdlProductFinder').modal('hide');
  }
}

window.Atlas = window.Atlas || {};
Atlas.productFinder = new AtlasProductFinder();

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