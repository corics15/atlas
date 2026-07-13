const txtPONo = document.getElementById('txtPONo');
const txtPODate = document.getElementById('txtPODate');
const txtTerms = document.getElementById('txtTerms');
const txtRemarks = document.getElementById('txtRemarks');
const txtCreditLimit = document.getElementById('txtCreditLimit');
const lblTotal = document.getElementById('lblTotal');
const tblPurchaseOrderDetails = document.getElementById('tblPurchaseOrderDetails');

const selCustomer = document.getElementById('selCustomer');
const btnSavePurchaseOrder = document.getElementById('btnSavePurchaseOrder');

let isEditMode = false;
let purchaseOrderId = null;
let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selSalesman');

  Atlas.select.onChange('#selCustomer', (option) => {
    const salesmanId = option.dataset.salesmanId;

    $('#selSalesman').val(salesmanId).trigger('change');

    txtTerms.value = option.dataset.terms ?? '';
    txtCreditLimit.value = option.dataset.creditLimit;

    markDirty();
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

    markDirty();
  });

  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('po-qty') ||
      e.target.classList.contains('po-price') ||
      e.target.classList.contains('po-discount')
    ) {
      const row = e.target.closest('tr');
      calculateRowTotal(row);

      markDirty();
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

    markDirty();

    const tbody = document.getElementById('tblPurchaseOrderDetails');
    if (!tbody.children.length) {
      addDetailRow();
    }
  });

  //*** save */
  btnSavePurchaseOrder.addEventListener('click', async () => {
    if (!validatePurchaseOrder()) {
      return;
    }

    btnSavePurchaseOrder.disabled = true;

    try {
      const po = {
        id: purchaseOrderId,
        po_no: txtPONo.value,
        po_date: txtPODate.value,
        customer_id: selCustomer.value,
        salesman_id: selSalesman.value,
        terms: txtTerms.value,
        remarks: txtRemarks.value,
        total_amount: Number(lblTotal.textContent),
        details: []
      };

      document.querySelectorAll('#tblPurchaseOrderDetails tr').forEach(row => {
        if (!row.dataset.productId) {
          return;
        }
        po.details.push({
          product_id: Number(row.dataset.productId),
          qty: Number(row.querySelector('.po-qty').value),
          price: Number(row.querySelector('.po-price').value),
          discount: Number(row.querySelector('.po-discount').value),
          amount: Number(row.querySelector('.po-total').textContent)
        });
      });

      const url = isEditMode
        ? 'purchase_orders/update'
        : 'purchase_orders/save';

      const result = await Atlas.ajax.post(url, po);

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      txtPONo.value = result.data.po_no;
      isEditMode = true;
      purchaseOrderId = result.data.purchase_order_id;

      btnSavePurchaseOrder.innerHTML = 'Save Changes';

      isDirty = false;
      const action = await Atlas.dialog.saved({
        title: 'Purchase Order',
        documentNo: result.data.po_no,
        confirmText: 'New',
        cancelText: 'Continue Editing'
      });

      if (action === 'new') {
        await resetPurchaseOrder();
      }

    } finally {
      btnSavePurchaseOrder.disabled = false;
    }
  });

  if (window.purchaseOrderId > 0) {
    await loadPurchaseOrder(window.purchaseOrderId);
  }

});

window.addEventListener('beforeunload', (e) => {
  if (!isDirty) {
    return;
  }
  e.preventDefault();
  e.returnValue = '';
});

const populateProductRow = (row, product) => {
  row.dataset.productId = product.id;
  row.querySelector('.po-barcode').value = product.barcode;
  row.querySelector('.po-supplier').textContent = product.supplier_name;
  row.querySelector('.po-description').textContent = product.description;
  row.querySelector('.po-uom').textContent = product.uom;

  row.querySelector('.po-price').value = Number(product.srp).toFixed(2);

  calculateRowTotal(row);

  row.querySelector('.po-qty').focus();

  markDirty();
}

const calculateRowTotal = (row) => {
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
          value="">
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

  markDirty();
}

const resetPurchaseOrder = async () => {
  isEditMode = false;
  purchaseOrderId = null;

  btnSavePurchaseOrder.innerHTML = 'Save Purchase Order';

  txtPONo.value = '';
  txtPODate.valueAsDate = new Date();

  $('#selCustomer').val('').trigger('change');
  $('#selSalesman').val('').trigger('change');

  txtTerms.value = '';
  txtRemarks.value = '';
  txtCreditLimit.value = '';
  lblTotal.textContent = '0.00';

  const tbody = document.getElementById(
    'tblPurchaseOrderDetails'
  );
  tbody.innerHTML = createDetailRow();
  tbody.querySelector('.po-barcode').focus();
  isDirty = false;
}

const validatePurchaseOrder = () => {

  if (!selCustomer.value) {
    Atlas.toast.warning('Please select a customer.');
    $('#selCustomer').select2('open');
    return false;
  }

  if (!selSalesman.value) {
    Atlas.toast.warning('Please select a salesman.');
    $('#selSalesman').select2('open');
    return false;
  }

  const rows = document.querySelectorAll('#tblPurchaseOrderDetails tr');
  let hasProduct = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    if (!row.dataset.productId) {
      continue;
    }
    hasProduct = true;

    const qty = Number(row.querySelector('.po-qty').value);
    const price = Number(row.querySelector('.po-price').value);
    const discount = Number(row.querySelector('.po-discount').value);

    if (qty <= 0) {
      Atlas.toast.warning(`Invalid quantity on row ${i + 1}.`);
      row.querySelector('.po-qty').focus();
      return false;
    }

    if (price < 0) {
      Atlas.toast.warning(`Invalid price on row ${i + 1}.`);
      row.querySelector('.po-price').focus();
      return false;
    }

    if (discount < 0) {
      Atlas.toast.warning(`Invalid discount on row ${i + 1}.`);
      row.querySelector('.po-discount').focus();
      return false;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning('Please add at least one product.');
    document.querySelector('.po-barcode').focus();
    return false;
  }

  return true;
}

const loadPurchaseOrder = async (id) => {
  isLoading = true;

  const result = await Atlas.ajax.get(
    'purchase_orders/get/' + id
  );

  if (!result.success) {
    Atlas.toast.error(result.message);
    return;
  }

  populateHeader(result.data.header);
  populateDetails(result.data.details);
  calculateGrandTotal();

  enableEditMode(result.data.header);

  isLoading = false;
  isDirty = false;
}

const populateHeader = (header) => {
  txtPONo.value = header.po_no;
  txtPODate.value = header.po_date;

  $('#selCustomer').val(header.customer_id).trigger('change');

  $('#selSalesman').val(header.salesman_id).trigger('change');

  txtTerms.value = header.terms ?? '';
  txtRemarks.value = header.remarks ?? '';
  txtCreditLimit.value = header.credit_limit ?? '';
}

const enableEditMode = (header) => {
  isEditMode = true;
  purchaseOrderId = header.id;

  btnSavePurchaseOrder.innerHTML =
    header.status === 'OPEN'
      ? 'Save Changes'
      : 'Read Only';
  btnSavePurchaseOrder.disabled = header.status !== 'OPEN';
  txtPONo.value = header.po_no;
}

const populateDetails = (details) => {
  const tbody = document.getElementById('tblPurchaseOrderDetails');
  tbody.innerHTML = '';

  details.forEach(detail => {
    const row = createDetailRow();
    tbody.insertAdjacentHTML('beforeend', row);
    const tr = tbody.lastElementChild;

    tr.dataset.productId = detail.product_id;
    tr.querySelector('.po-barcode').value = detail.barcode;
    tr.querySelector('.po-supplier').textContent = detail.supplier_name;
    tr.querySelector('.po-description').textContent = detail.description;
    tr.querySelector('.po-uom').textContent = detail.uom;
    tr.querySelector('.po-qty').value = Number(detail.qty);
    tr.querySelector('.po-price').value = Number(detail.price).toFixed(2);
    tr.querySelector('.po-discount').value = Number(detail.discount).toFixed(2);

    calculateRowTotal(tr);
  });
}

const markDirty = () => {
  if (isLoading) return;
  isDirty = true;
}