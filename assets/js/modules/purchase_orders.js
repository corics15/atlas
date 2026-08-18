const txtPONo = document.getElementById('txtPONo');
const tdRefNo = document.getElementById('tdRefNo');
const txtPODate = document.getElementById('txtPODate');
const selTerms = document.getElementById('selTerms');
const txtRemarks = document.getElementById('txtRemarks');
const txtCreditLimit = document.getElementById('txtCreditLimit');
const lblTotal = document.getElementById('lblTotal');
const tblPurchaseOrderDetails = document.getElementById('tblPurchaseOrderDetails');

const selSupplier = document.getElementById('selSupplier');
const btnSavePurchaseOrder = document.getElementById('btnSavePurchaseOrder');

let isEditMode = false;
let purchaseOrderId = null;
let isDirty = false;
let isLoading = false;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.select.init('#selSupplier');
  Atlas.select.init('#selTerms');

  Atlas.select.onChange('#selSupplier', (option) => {
    $('#selTerms').val(option.dataset.termsId).trigger('change');
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

  /*** product finder event */
  document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-product-finder')) {
      const row = e.target.closest('tr');
      Atlas.productFinder.show(row);
    }
  });

  /*** scanner barcode event */
  document.addEventListener('keydown', async (e) => {
    if (!e.target.classList.contains('atlas-barcode')) {
      return;
    }

    if (e.key !== 'Enter') {
      return;
    }

    e.preventDefault();
    const row = e.target.closest('tr');
    await Atlas.productFinder.lookup(
      row,
      e.target.value
    );
    markDirty();
  });

  /*** details table on change event */
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

  /*** "enter" key event after entering qty */
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

  /*** delete row */
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

  /*** save */
  btnSavePurchaseOrder?.addEventListener('click', async () => {
    if (!validatePurchaseOrder()) {
      return;
    }

    btnSavePurchaseOrder.disabled = true;

    try {
      const po = {
        id: purchaseOrderId,
        po_no: txtPONo.value,
        po_date: txtPODate.value,
        supplier_id: selSupplier.value,
        terms_id: Atlas.format.parseNumber(selTerms.value),
        remarks: txtRemarks.value,
        total_amount: Atlas.format.parseNumber(lblTotal.textContent),
        details: []
      };

      const rows = document.querySelectorAll('#tblPurchaseOrderDetails tr');
      for (const row of rows) {
        if (!row.dataset.productId) {
          continue;
        }

        const uomId = row.querySelector('.po-uom').value;
        if (!uomId) {
          Atlas.toast.error('Please select a UOM for all products.');
          row.querySelector('.po-uom').focus();
          return;
        }
        po.details.push({
          product_id: Atlas.format.parseNumber(row.dataset.productId),
          qty: Atlas.format.parseNumber(row.querySelector('.po-qty').value),
          price: Atlas.format.parseNumber(row.querySelector('.po-price').value),
          discount: Atlas.format.parseNumber(row.querySelector('.po-discount').value),
          amount: Atlas.format.parseNumber(row.querySelector('.po-total').textContent),
          uom_id: Atlas.format.parseNumber(uomId),
        });
      }

      const url = isEditMode
        ? 'purchase-orders/update'
        : 'purchase-orders/save';

      const result = await Atlas.ajax.post(url, po);

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      txtPONo.value = result.data.po_no;
      tdRefNo.innerHTML = result.data.po_no;
      purchaseOrderId = result.data.purchase_order_id;

      Atlas.toast.success(result.message);
      setTimeout(() => Atlas.page.redirect(`purchase-orders?id=${Atlas.id.encode(result.data.purchase_order_id)}`), 1200);
      isEditMode = true;
      isDirty = false;

    } finally {
      btnSavePurchaseOrder.disabled = false;
    }
  });

  /*** print footer */
  btnPrintPurchaseOrder.addEventListener('click', () => {
    if (window.purchaseOrderId > 0) {
      ids = []
      ids.push(window.purchaseOrderId)

      Atlas.print.post('purchase-orders/print', ids);
    } else {
      Atlas.toast.warning(`Create a Purchase Order first.`)
    }
  });

  /*** receive goods footer */
  btnReceiveGoods?.addEventListener('click', () => {
    if (window.purchaseOrderId > 0)
      Atlas.page.redirect('goods-receipts/create', { po: Atlas.id.encode(window.purchaseOrderId) })
    else Atlas.toast.warning(`Create a Purchase Order first.`);
  });

  /*** cancel purchase order footer */
  btnCancelPurchaseOrder?.addEventListener('click', async () => {
    if (window.purchaseOrderId === 0) {
      Atlas.toast.warning(`Create a Purchase Order first.`);
      return;
    }

    if (btnCancelPurchaseOrder.dataset.status !== 'OPEN') {
      Atlas.toast.warning(
        'Only OPEN Purchase Orders can be CANCELLED.'
      );
      return;
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel Purchase Order?`,
      text: 'Please provide the reason for cancellation.',
      // inputLabel: 'Cancellation Reason',
      inputPlaceholder: 'Enter cancellation reason...',
      required: false, /*** set to true if you want this to be required */
      requiredMessage: 'Cancellation reason is required.',
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    const formData = new FormData();
    formData.append('ids[]', window.purchaseOrderId);
    formData.append('cancel_reason', reason);

    const result = await Atlas.ajax.post(
      'purchase-orders/cancel',
      formData
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.refresh(), 2000);
  });

  if (window.purchaseOrderId > 0) {
    await loadPurchaseOrder(window.purchaseOrderId);
  }
});

/*** for markDirty */
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
  row.querySelector('.po-uom').value = product.uom_id;

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

  row.querySelector('.po-total').textContent = Atlas.format.amount(amount);// amount.toFixed(2);
  calculateGrandTotal();
}

const calculateGrandTotal = () => {
  let grandTotal = 0;
  document.querySelectorAll('#tblPurchaseOrderDetails tr').forEach(row => {
    grandTotal += Atlas.format.parseNumber(
      row.querySelector('.po-total').textContent || 0
    );
  });
  document.getElementById('lblTotal').textContent = Atlas.format.amount(grandTotal);//grandTotal.toFixed(2);
}

const buildUomOptions = () => {
  const uoms = Array.isArray(window.atlasUoms)
    ? window.atlasUoms
    : [];

  console.log(uoms)

  return `
    <option value="">Select...</option>
    ${uoms.map(uom => `
      <option value="${uom.id}">
        ${uom.uom}
      </option>
    `).join('')}
  `;
};

const createDetailRow = () => {
  return `
    <tr>
      <td>
        <div class="input-group">
          <input type="text" class="form-control form-control-sm po-barcode atlas-barcode" placeholder="Barcode">
          <div class="input-group-append">
            <button
              type="button"
              class="btn btn-sm btn-outline-warning btn-product-finder">
              <i class="fas fa-search font-smr"></i>
            </button>
          </div>
        </div>
      </td>
      <td class="po-supplier"></td>
      <td class="po-description"></td>
      <td>
        <select class="form-control form-control-sm po-uom custom-select w-auto">
          ${buildUomOptions()}
        </select>
      </td>
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
      <td class="po-total text-right">
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

  $('#selSupplier').val('').trigger('change');

  txtRemarks.value = '';
  lblTotal.textContent = '0.00';

  const tbody = document.getElementById('tblPurchaseOrderDetails');
  tbody.innerHTML = createDetailRow();
  tbody.querySelector('.po-barcode').focus();
  isDirty = false;
}

const validatePurchaseOrder = () => {

  if (!selSupplier.value) {
    Atlas.toast.warning('Please select a supplier.');
    $('#selSupplier').select2('open');
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

    const qty = Atlas.format.parseNumber(row.querySelector('.po-qty').value);
    const price = Atlas.format.parseNumber(row.querySelector('.po-price').value);
    const discount = Atlas.format.parseNumber(row.querySelector('.po-discount').value);
    console.log(qty, price, discount)

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
    `purchase-orders/get/${id}`
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
  tdRefNo.innerHTML = header.po_no;
  txtPODate.value = header.po_date;

  $('#selSupplier').val(header.supplier_id).trigger('change');

  $('#selTerms').val(header.terms_id).trigger('change');
  txtRemarks.value = header.remarks ?? '';

  let statusClass = ``;
  switch (header.status) {
    case 'COMPLETED':
      statusClass = 'text-success';
      break;
    case 'OPEN':
      statusClass = 'text-secondary';
      break;
    case 'PARTIAL':
      statusClass = 'text-warning';
      break;
    case 'COMPLETED':
      statusClass = 'text-primary';
      break;
    case 'CLOSED':
      statusClass = 'text-secondary';
      break;
    default: /*** CANCELLED */
      statusClass = 'text-danger';
      break;
  }
  document.querySelector('.ls-wider').innerHTML = `[${header.status}]`;
  document.querySelector('.ls-wider').classList.add(statusClass);
  document.getElementById('btnCancelPurchaseOrder').setAttribute('data-status', header.status)
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
    tr.querySelector('.po-uom').value = detail.uom_id;
    tr.querySelector('.po-qty').value = Number(detail.qty);
    tr.querySelector('.po-price').value = Number(detail.price).toFixed(2);
    tr.querySelector('.po-discount').value = Number(detail.discount).toFixed(2);

    calculateRowTotal(tr);
  });
}

const enableEditMode = (header) => {
  isEditMode = true;
  purchaseOrderId = header.id;

  btnSavePurchaseOrder.disabled = header.status !== 'OPEN';
  txtPONo.value = header.po_no;
}

const markDirty = () => {
  if (isLoading) return;
  isDirty = true;
}