document.addEventListener('DOMContentLoaded', () => {

  const selCustomer = document.getElementById('selCustomer');
  const txtTerms = document.getElementById('txtTerms');
  const txtCreditLimit = document.getElementById('txtCreditLimit');

  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selSalesman');

  Atlas.select.onChange('#selCustomer', (option) => {
    const salesmanId = option.dataset.salesmanId;

    $('#selSalesman').val(salesmanId).trigger('change');

    txtTerms.value = option.dataset.terms ?? '';
    txtCreditLimit.value = option.dataset.creditLimit;
  });

  Atlas.select.onChange('.po-product', (option, control) => {
    const row = control.closest('tr');

    row.querySelector('.po-description').value = option.dataset.description ?? '';
    row.querySelector('.po-uom').value = option.dataset.uom ?? '';
    row.querySelector('.po-price').value = option.dataset.price ?? '0';
  });

  document.querySelectorAll('.po-product').forEach(control => {
    Atlas.select.init(control);
  });

  document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-product-finder')) {
      const row = e.target.closest('tr');
      Atlas.productFinder.show(row);
    }
  });

  document.addEventListener('keydown', async (e) => {
    if (!e.target.classList.contains('po-barcode')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();
    const barcode = e.target.value.trim();

    if (!barcode) {
      return;
    }

    const result = await Atlas.ajax.get(
      'product_finder/barcode/' + encodeURIComponent(barcode)
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    const row = e.target.closest('tr');
    populateProductRow(row, result.data);
  });

  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('po-qty') ||
      e.target.classList.contains('po-price') ||
      e.target.classList.contains('po-discount')
    ) {
      const row = e.target.closest('tr');
      calculateRow(row);
    }
  });

  document.addEventListener('keydown', (e) => {

    if (!e.target.classList.contains('po-qty')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();
    const row = e.target.closest('tr');
    if (!row.dataset.productId) {
      Atlas.toast.warning('Please select a product.');
      row.querySelector('.po-barcode').focus();
      return;
    }

    /*** check if there's an empty row below */
    const nextRow = row.nextElementSibling;
    if (nextRow && !nextRow.dataset.productId) {
      nextRow.querySelector('.po-barcode').focus();
      return;
    }
    addDetailRow();
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-row');

    if (!btn) {
      return;
    }

    const row = btn.closest('tr');
    row.remove();
    calculateGrandTotal();

    const tbody = document.getElementById('tblPurchaseOrderDetails');
    if (!tbody.children.length) {
      addDetailRow();
    }
  });

});

const populateProductRow = (row, product) => {
  row.dataset.productId = product.id;
  row.querySelector('.po-barcode').value = product.barcode;
  row.querySelector('.po-supplier').textContent = product.supplier_name;
  row.querySelector('.po-description').textContent = product.description;
  row.querySelector('.po-uom').textContent = product.uom;

  row.querySelector('.po-price').value = Number(product.srp).toFixed(2);

  calculateRow(row);

  row.querySelector('.po-qty').focus();
}

const calculateRow = (row) => {
  const qty = Number(row.querySelector('.po-qty').value || 0);
  const price = Number(row.querySelector('.po-price').value || 0);
  const discount = Number(row.querySelector('.po-discount').value || 0);
  const amount = (qty * price) - discount;

  row.querySelector('.po-total').textContent = amount.toFixed(2);
  calculateGrandTotal();
}

const calculateGrandTotal = () => {
  let grandTotal = 0;
  document.querySelectorAll('#tblPurchaseOrderDetails tr').forEach(row => {
    grandTotal += Number(
      row.querySelector('.po-total').textContent || 0
    );
  });
  document.getElementById('lblTotal').textContent = grandTotal.toFixed(2);
}

const createDetailRow = () => {
  return `
    <tr>
      <td>
        <div class="input-group">
          <input type="text" class="form-control form-control-sm po-barcode" placeholder="Barcode">
          <div class="input-group-append">
            <button
              type="button"
              class="btn btn-sm btn-outline-info btn-product-finder">
              <i class="fas fa-search font-smr"></i>
            </button>
          </div>
        </div>
      </td>
      <td class="po-supplier align-middle"></td>
      <td class="po-description align-middle"></td>
      <td class="po-uom align-middle"></td>
      <td>
        <input
          type="number" step="any"
          class="form-control form-control-sm text-right po-qty"
          value="1">
      </td>
      <td>
        <input
          type="number" step="any"
          class="form-control form-control-sm text-right po-price"
          value="0.00">
      </td>
      <td>
        <input
          type="number" step="any"
          class="form-control form-control-sm text-right po-discount"
          value="0.00">
      </td>
      <td class="po-total text-right align-middle">
        0.00
      </td>
      <td>
        <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
      </td>
    </tr>
  `;
}

const addDetailRow = () => {
  const tbody = document.getElementById('tblPurchaseOrderDetails');
  tbody.insertAdjacentHTML(
    'beforeend',
    createDetailRow()
  );

  const row = tbody.lastElementChild;
  row.querySelector('.po-barcode').focus();
  calculateGrandTotal();
}