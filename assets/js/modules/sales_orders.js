const hidSalesOrderId = document.getElementById('hidSalesOrderId');

const txtSalesOrderNo = document.getElementById('txtSalesOrderNo');
const dtOrderDate = document.getElementById('dtOrderDate');
const txtCreditLimit = document.getElementById('txtCreditLimit');

const selCustomer = document.getElementById('selCustomer');
const selSalesman = document.getElementById('selSalesman');
const selTerms = document.getElementById('selTerms');

const txtSalesOrderRemarks = document.getElementById('txtSalesOrderRemarks');

const tblSalesOrderDetails = document.getElementById('tblSalesOrderDetails');

const btnNewSalesOrder = document.getElementById('btnNewSalesOrder');
const btnSaveSalesOrder = document.getElementById('btnSaveSalesOrder');
const btnEditSalesOrder = document.getElementById('btnEditSalesOrder');
const btnCancelSalesOrder = document.getElementById('btnCancelSalesOrder');
const btnRefreshSalesOrder = document.getElementById('btnRefreshSalesOrder');
const btnPrintSalesOrder = document.getElementById('btnPrintSalesOrder');
const btnPostSalesOrder = document.getElementById('btnPostSalesOrder');
const btnCreateDeliveryReceipt = document.getElementById('btnCreateDeliveryReceipt');
const btnDownloadSOExcel = document.getElementById('btnDownloadSOExcel');

let isDirty = false;
let isLoading = true;
let isEditMode = (hidSalesOrderId?.value) ? true : false;
let customerDiscountType = '';
let customerDiscountValue = 0;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkSalesOrder',
    selectAll: '#chkSelectAllSalesOrder',
  });

  /*** dirty tracking */
  document.addEventListener('input', (e) => {
    if (
      e.target.classList.contains('so-qty') ||
      e.target.id === `txtSalesOrderRemarks`
    ) {
      markDirty();
    }
  });
  Atlas.select.onChange('#selCustomer', (option) => {
    $('#selSalesman').val(option.dataset.salesmanId).trigger('change');
    $('#selTerms').val(option.dataset.termsId).trigger('change');
    txtCreditLimit.value = Atlas.format.amount(option.dataset.creditLimit);

    /*** customer default discount */
    customerDiscountType = option.dataset.discountType || '';
    customerDiscountValue = Atlas.format.parseNumber(option.dataset.discountValue || 0);
    markDirty();
  });
  Atlas.select.onChange('#selSaleman', () => markDirty());
  /*** end dirty tracking */

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
    await Atlas.productFinder.lookup(row, e.target.value);
    markDirty();
  });

  /*** product finder shortcut */
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'F2') {
      return;
    }

    e.preventDefault();

    /*** do nothing if Product Finder is already open */
    if (document.querySelector('.modal.show')) {
      return;
    }

    let row = e.target.closest?.('#tblSalesOrderDetails tr');

    /*** if focus is outside the details table, use first empty row */
    if (!row) {
      row = [...tblSalesOrderDetails.rows].find(r => !r.dataset.productId);
    }

    /*** if no empty row exists, add one */
    if (!row) {
      addDetailRow(false);
      row = tblSalesOrderDetails.lastElementChild;
    }

    Atlas.productFinder.show(row);
  });

  /*** sales order price / qty / discount calculation */
  document.addEventListener('input', (e) => {
    if (
      !e.target.classList.contains('so-qty') &&
      !e.target.classList.contains('so-unit-price') &&
      !e.target.classList.contains('so-discount-value')
    ) {
      return;
    }

    const row = e.target.closest('tr');

    if (!row?.dataset.productId) {
      return;
    }

    calculateSalesOrderRow(row);
    markDirty();
  });

  /*** sales order discount type change */
  document.addEventListener('change', (e) => {
    if (!e.target.classList.contains('so-discount-type')) {
      return;
    }

    const row = e.target.closest('tr');

    if (!row?.dataset.productId) {
      return;
    }

    const discountValue = row.querySelector('.so-discount-value');

    if (!e.target.value) {
      discountValue.value = '0.00';
      discountValue.disabled = true;
    } else {
      discountValue.disabled = false;

      /*** switching type starts clean */
      discountValue.value = '0.00';

      discountValue.focus();
      discountValue.select();
    }

    calculateSalesOrderRow(row);
    markDirty();
  });

  /*** new */
  btnNewSalesOrder?.addEventListener('click', () => Atlas.page.redirect(`sales-orders/create/`));

  /*** save */
  btnSaveSalesOrder?.addEventListener('click', async () => {

    if (!validateSalesOrder()) {
      return;
    }

    btnSaveSalesOrder.disabled = true;

    try {
      const salesOrder = {
        id: hidSalesOrderId.value,
        so_no: txtSalesOrderNo.value,
        order_date: dtOrderDate.value,
        customer_id: selCustomer.value,
        salesman_id: selSalesman.value,
        terms_id: selTerms.value,
        credit_limit: Atlas.format.parseNumber(txtCreditLimit.value) || 0,
        remarks: txtSalesOrderRemarks.value,
        details: []
      };

      document.querySelectorAll('#tblSalesOrderDetails tr').forEach(row => {
        if (!row.dataset.productId) {
          return;
        }

        const uomId = Atlas.format.parseNumber(row.querySelector('.so-uom').value);
        if (!uomId) {
          Atlas.toast.error('Please select a UOM for all products.');
          row.querySelector('.so-uom').focus();
          return;
        }

        const conversionFactor = Atlas.format.parseNumber(row.dataset.conversionFactor);
        if (!conversionFactor || conversionFactor <= 0) {
          Atlas.toast.error('Please provide a valid UOM conversion for all products.');
          return;
        }

        salesOrder.details.push({
          product_id: Atlas.format.parseNumber(row.dataset.productId),
          uom_id: uomId,
          conversion_factor: conversionFactor,
          qty: Atlas.format.parseNumber(row.querySelector('.so-qty').value),

          unit_price: Atlas.format.parseNumber(row.querySelector('.so-unit-price').value),
          discount_type: row.querySelector('.so-discount-type').value || null,
          discount_value: Atlas.format.parseNumber(row.querySelector('.so-discount-value').value || 0),
          discount_amount: Atlas.format.parseNumber(row.dataset.discountAmount || 0,)
        });
      });

      const result = await Atlas.ajax.post(
        'sales-orders/save',
        salesOrder
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      hidSalesOrderId.value = result.data.sales_order_id;
      isEditMode = true;
      isDirty = false;
      setTimeout(() => Atlas.page.redirect(`sales-orders/edit/${Atlas.id.encode(result.data.sales_order_id)}`), 1500);
    }
    finally {
      btnSaveSalesOrder.disabled = false;
    }
  });

  /*** edit */
  btnEditSalesOrder?.addEventListener('click', () => {
    const id = getSelectedSalesOrderId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`sales-orders/edit/${Atlas.id.encode(id)}`);
  });

  /*** post */
  btnPostSalesOrder?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not yet saved yet.');
        return;
      } else if (window.salesOrderId) {
        ids = [window.salesOrderId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Order');
        return false;
      }
    }

    btnPostSalesOrder.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'sales-orders/post',
        {
          ids: ids
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);
      setTimeout(() => Atlas.page.refresh(), 1500);

    } finally {
      btnPostSalesOrder.disabled = false;
    }
  });

  /*** create delivery receipt */
  btnCreateDeliveryReceipt?.addEventListener('click', () => {
    const id = getSelectedSalesOrderId();
    let row = document.querySelector(`tr[data-id="${id}"]`);

    if (!row) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not saved yet.');
        return;
      }

      if (!window.salesOrderId) {
        Atlas.toast.warning('Please select a Sales Order.');
        return;
      }

      const status = window.status;
      const remainingItems = window.remainingItems;

      if (status !== 'POSTED') {
        Atlas.toast.warning('Only POSTED Sales Orders can create a Delivery Receipt.');
        return;
      }

      if (Atlas.format.integer(remainingItems) === 0) {
        Atlas.toast.warning('This Sales Order is already fully delivered.');
        return;
      }

      Atlas.page.redirectRemember(`delivery-receipts/create/${Atlas.id.encode(window.salesOrderId)}`);
    }

    if (row.dataset.status !== 'POSTED') {
      Atlas.toast.warning('Only POSTED Sales Orders can create a Delivery Receipt.');
      return;
    }

    if (Atlas.format.integer(row.dataset.remainingItems) === 0) {
      Atlas.toast.warning('This Sales Order is already fully delivered.');
      return;
    }

    Atlas.page.redirectRemember(`delivery-receipts/create/${id}`);
  });

  /*** print */
  btnPrintSalesOrder?.addEventListener('click', printSalesOrder);

  /*** cancel */
  btnCancelSalesOrder?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.salesOrderId === 0) {
        Atlas.toast.warning('New Sales Order, not yet saved yet.');
        return;
      } else if (window.salesOrderId) {
        ids = [window.salesOrderId];
      } else {
        Atlas.toast.warning('Please select at least one Sales Order');
        return false;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Sales Order(s)?`,
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

    const result = await Atlas.ajax.post(
      'sales-orders/cancel',
      {
        ids: ids,
        cancel_reason: reason
      }
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    isDirty = false;
    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.refresh(), 1500);
  });

  /*** excel download */
  btnDownloadSOExcel?.addEventListener('click', () => {
    Atlas.excel.download(
      document.getElementById('tblSOList'),
      {
        title: 'Sales Order List',
        generatedBy: Atlas.config.userName,
        fileName: 'sales-order-list',
        sheetName: 'SOList',
        /*** start with 0, index based */
        totals: [
          {
            column: 5,
            value: 'TOTAL'
          },
          {
            column: 6,
            value: window.itemCount || 0,
            type: 'n',
            format: '#,##0'
          },
          {
            column: 7,
            value: window.totalAmount || 0,
            type: 'n',
            format: '#,##0.00'
          },
        ]
      }
    );
  });

  /*** refresh */
  btnRefreshSalesOrder?.addEventListener('click', () => Atlas.page.redirect(`sales-orders`));

  /*** remove row event on details table */
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-delete-row');

    if (!btn) {
      return;
    }
    const row = btn.closest('tr');
    removeDetailRow(row);
    calculateSalesOrderTotals();
  });

  /*** sales order UOM change */
  document.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('so-uom')) {
      return;
    }

    const row = e.target.closest('tr');
    if (!row?.dataset.productId) {
      return;
    }

    const productId = Atlas.format.parseNumber(row.dataset.productId);
    const baseUomId = Atlas.format.parseNumber(row.dataset.baseUomId);
    const uomId = Atlas.format.parseNumber(e.target.value);

    if (!uomId) {
      row.dataset.conversionFactor = '';
      return;
    }

    /*** base UOM always has conversion 1 */
    if (uomId === baseUomId) {
      row.dataset.conversionFactor = 1;
      markDirty();
      return;
    }

    const result = await Atlas.ajax.post(
      'sales-orders/get-uom-conversion',
      {
        product_id: productId,
        uom_id: uomId,
        base_uom_id: baseUomId
      }
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    /*** prompt conversion */
    if (result.data.is_known) {
      row.dataset.conversionFactor = result.data.conversion_factor;

      /*** load default selling price for selected UOM */
      row.querySelector('.so-unit-price').value = Atlas.format.parseNumber(result.data.selling_price || 0).toFixed(2);
      calculateSalesOrderRow(row);

      const baseQtyAvailable = Atlas.format.parseNumber(row.dataset.baseQtyAvailable || 0);
      const qtyAvailable = baseQtyAvailable / Atlas.format.parseNumber(row.dataset.conversionFactor);
      row.querySelector('.so-available').textContent = Atlas.format.amount(qtyAvailable);

      markDirty();
      return;
    }

    /*** unknown conversion */
    const selectedUom = e.target.options[e.target.selectedIndex].text.trim();
    const baseUomOption = [...e.target.options].find(option =>
      Atlas.format.parseNumber(option.value) === baseUomId
    );
    const baseUom = baseUomOption ? baseUomOption.text.trim() : 'BASE UOM';
    const unitsPerBase = await Atlas.dialog.number({
      title: 'UOM Conversion',
      html: `<div class="text-center">
          <p>
            ATLAS does not yet have a<br>conversion defined for
            <span class="font-weight-500 text-danger">${selectedUom}</span>.
          </p>
          <p>
            The base UOM for this product is
            <span class="font-weight-500 text-info">${baseUom}</span>.
          </p>
          <p>
            How many <span class="font-weight-500 text-danger">${selectedUom}</span>
            are in <span class="font-weight-500 text-info">1 ${baseUom}</span>?
          </p>
        </div>`,
      inputPlaceholder: `1 ${baseUom} = ? ${selectedUom}`,
      min: 0.0001,
      confirmText: 'Use Conversion'
    });

    if (unitsPerBase === null) {
      /*** return to base UOM */
      e.target.value = baseUomId;
      row.dataset.conversionFactor = 1;
      const baseQtyAvailable = Atlas.format.parseNumber(row.dataset.baseQtyAvailable || 0);
      row.querySelector('.so-available').textContent = Atlas.format.amount(baseQtyAvailable);
      return;
    }

    /*** convert human-friendly relationship to ATLAS base factor */
    const conversionFactor = 1 / unitsPerBase;
    row.dataset.conversionFactor = conversionFactor;
    const baseQtyAvailable = Atlas.format.parseNumber(row.dataset.baseQtyAvailable || 0);
    const qtyAvailable = baseQtyAvailable / conversionFactor;
    row.querySelector('.so-available').textContent = Atlas.format.amount(qtyAvailable);

    markDirty();
    /*** end prompt conversion */
  });

  /*** initialize saved Sales Order calculations */
  tblSalesOrderDetails?.querySelectorAll('tr[data-product-id]').forEach(row => {
    const discountType = row.querySelector('.so-discount-type');
    const discountValue = row.querySelector('.so-discount-value');

    if (!discountType || !discountValue) {
      return;
    }

    /*** no discount */
    if (!discountType.value) {
      discountValue.value = '0.00';
      discountValue.disabled = true;
    } else {
      discountValue.disabled = false;
    }

    calculateSalesOrderRow(row);
  });

  /*** initialize Sales Order totals */
  if (tblSalesOrderDetails)
    calculateSalesOrderTotals();

  /*** add new row when in edit mode */
  if (isEditMode) {
    const lastRow = tblSalesOrderDetails.lastElementChild;

    if (lastRow?.dataset.productId) {
      addDetailRow(false);
    }
  }

  isDirty = false;
  isLoading = false;

});

if (selCustomer) Atlas.select.init('#selCustomer');
if (selSalesman) Atlas.select.init('#selSalesman');
if (selTerms) Atlas.select.init('#selTerms');

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
});

const addDetailRow = (markAsDirty = true) => {
  const tbody = document.getElementById('tblSalesOrderDetails');
  tbody.insertAdjacentHTML('beforeend', createDetailRow());
  renumberRows();
  /*** let product finder determine which element to focus on */
  // setTimeout(() => tbody.lastElementChild.querySelector('.so-barcode').focus(), 500);
  if (markAsDirty) {
    markDirty();
  }
};

const createDetailRow = () => {
  return `<tr>
            <td class="so-row-no text-center"></td>

            <td>
              <div class="input-group">
                <input type="text" class="form-control form-control-sm so-barcode atlas-barcode text-center" placeholder="Barcode">

                <div class="input-group-append">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-warning btn-product-finder">
                    <i class="fas fa-search font-smr"></i>
                  </button>
                </div>
              </div>
            </td>

            <td class="so-description" data-toggle="tooltip"></td>

            <td class="text-right">
              <select class="form-control form-control-sm so-uom custom-select w-auto">
                ${buildUomOptions()}
              </select>
            </td>

            <td class="so-available text-right">-</td>

            <td></td>

            <td></td>

            <td>
              <input type="number" step="any" class="form-control form-control-sm text-right so-qty" value="">
            </td>

            <td>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right so-unit-price" value="0.00">
            </td>

            <td>
              <select class="form-control form-control-sm so-discount-type custom-select">
                <option value="">No Discount</option>
                <option value="PERCENT">Percent (%)</option>
                <option value="AMOUNT">Amount</option>
              </select>
            </td>

            <td>
              <input
                type="number"
                step="0.01"
                min="0"
                class="form-control form-control-sm text-right so-discount-value"
                value="0.00"
                disabled>
            </td>

            <td class="so-net-amount text-right">
              0.00
            </td>

            <td class="text-center">
              <i class="fas fa-trash text-danger pointer btn-delete-row"></i>
            </td>
          </tr>
        `;
};

const buildUomOptions = () => {
  const uoms = Array.isArray(window.atlasUoms)
    ? window.atlasUoms
    : [];

  return `
    <option value="">Select...</option>
    ${uoms.map(uom => `
      <option value="${uom.id}">
        ${uom.uom}
      </option>
    `).join('')}
  `;
};

const populateProductRow = (row, product) => {
  /*** check duplicate rows */
  const duplicate = [...tblSalesOrderDetails.rows].find(r =>
    r !== row &&
    Atlas.format.integer(r.dataset.productId) === Atlas.format.integer(product.id)
  );
  if (duplicate) {
    Atlas.toast.warning('Product already exists.');
    duplicate.querySelector('.so-qty')?.focus();
    return;
  }
  /*** end check */

  row.dataset.productId = product.id;
  row.dataset.baseUomId = product.uom_id;
  row.dataset.conversionFactor = 1;
  row.dataset.baseQtyAvailable = Atlas.format.parseNumber(product.qty_on_hand);

  row.querySelector('.so-barcode').value = product.barcode;
  row.querySelector('.so-description').textContent = product.description.length > 15 ? product.description.substring(0, 15) + '...' : product.description;
  if (product.description.length > 15) {
    row.querySelector('.so-description').setAttribute('title', product.description)
  }
  row.querySelector('.so-uom').value = product.uom_id;
  row.querySelector('.so-available').textContent = Atlas.format.integer(product.qty_on_hand);

  /*** default selling price */
  const unitPrice = Atlas.format.parseNumber(product.selling_price ?? product.srp ?? 0);
  row.querySelector('.so-unit-price').value = unitPrice.toFixed(2);

  /*** customer default discount */
  const discountType = row.querySelector('.so-discount-type');
  const discountValue = row.querySelector('.so-discount-value');

  discountType.value = customerDiscountType || '';
  discountValue.value = Atlas.format.parseNumber(customerDiscountValue || 0).toFixed(2);
  discountValue.disabled = !customerDiscountType;

  /*** calculate initial row */
  calculateSalesOrderRow(row);

  setTimeout(() => row.querySelector('.so-qty').focus(), 500);
  markDirty();

  /*** check if there's already an empty row */
  const nextRow = row.nextElementSibling;
  if (!nextRow || nextRow.dataset.productId) {
    addDetailRow();
  }
};

const calculateSalesOrderRow = (row) => {
  const qty = Atlas.format.parseNumber(row.querySelector('.so-qty')?.value || 0);
  const unitPrice = Atlas.format.parseNumber(row.querySelector('.so-unit-price')?.value || 0);
  const discountType = row.querySelector('.so-discount-type')?.value || '';

  let discountValue = Atlas.format.parseNumber(row.querySelector('.so-discount-value')?.value || 0);

  const grossAmount = qty * unitPrice;
  let discountAmount = 0;

  if (discountType === 'PERCENT') {
    if (discountValue > 100) {
      discountValue = 100;
      row.querySelector('.so-discount-value').value = '100.00';
    }

    discountAmount = grossAmount * (discountValue / 100);

  } else if (discountType === 'AMOUNT') {

    discountAmount = discountValue;

    /*** row discount cannot exceed row gross */
    if (discountAmount > grossAmount) {
      discountAmount = grossAmount;
      row.querySelector('.so-discount-value').value = grossAmount.toFixed(2);
    }
  }

  const netAmount = Math.max(0, grossAmount - discountAmount);
  row.dataset.discountAmount = discountAmount.toFixed(2);
  row.querySelector('.so-net-amount').textContent = Atlas.format.amount(netAmount);

  calculateSalesOrderTotals();
};

const calculateSalesOrderTotals = () => {
  let grossAmount = 0;
  let discountAmount = 0;

  document.querySelectorAll('#tblSalesOrderDetails tr[data-product-id]').forEach(row => {
    const qty = Atlas.format.parseNumber(row.querySelector('.so-qty')?.value || 0);
    const unitPrice = Atlas.format.parseNumber(row.querySelector('.so-unit-price')?.value || 0);
    const rowGross = qty * unitPrice;
    const rowDiscount = Atlas.format.parseNumber(row.dataset.discountAmount || 0);

    grossAmount += rowGross;
    discountAmount += rowDiscount;
  });

  const discountedAmount = Math.max(0, grossAmount - discountAmount);
  const vatMode = window.salesOrderVatMode || '';
  const vatRate = window.salesOrderVatRate || 0;
  const vatDecimal = vatRate / 100;

  let subtotal = 0;
  let vatAmount = 0;
  let totalAmount = 0;

  if (vatMode === 'INCLUSIVE') {

    totalAmount = discountedAmount;

    if (vatDecimal > 0) {
      subtotal = totalAmount / (1 + vatDecimal);
      vatAmount = totalAmount - subtotal;
    } else {
      subtotal = totalAmount;
    }

  } else if (vatMode === 'EXCLUSIVE') {

    subtotal = discountedAmount;
    vatAmount = subtotal * vatDecimal;
    totalAmount = subtotal + vatAmount;

  } else {
    /*** new SO before VAT snapshot is resolved */
    subtotal = discountedAmount;
    totalAmount = discountedAmount;
  }

  document.getElementById('soGrossAmount').textContent = Atlas.format.amount(grossAmount);
  document.getElementById('soDiscountAmount').textContent = Atlas.format.amount(discountAmount);
  document.getElementById('soSubtotal').textContent = Atlas.format.amount(subtotal);
  document.getElementById('soVatRateLabel').textContent = `${vatRate.toFixed(2)}%`;
  document.getElementById('soVatAmount').textContent = Atlas.format.amount(vatAmount);
  document.getElementById('soTotalAmount').textContent = Atlas.format.amount(totalAmount);
};

const renumberRows = () => {
  document.querySelectorAll('#tblSalesOrderDetails tr').forEach((row, index) => {
    row.querySelector('.so-row-no').textContent = `${index + 1}.`;
  });
};

const validateSalesOrder = () => {
  const rows = document.querySelectorAll('#tblSalesOrderDetails tr');

  let hasProduct = false;

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];

    if (!row.dataset.productId) {
      continue;
    }

    hasProduct = true;

    /*** validate UOM */
    const uomId = Atlas.format.parseNumber(row.querySelector('.so-uom').value);

    if (!uomId) {
      Atlas.toast.warning(`Please select a UOM on row ${i + 1}.`);
      row.querySelector('.so-uom').focus();
      return false;
    }

    /*** validate conversion */
    const conversionFactor = Atlas.format.parseNumber(row.dataset.conversionFactor || 0);

    if (conversionFactor <= 0) {
      Atlas.toast.warning(`Invalid UOM conversion on row ${i + 1}.`);
      row.querySelector('.so-uom').focus();
      return false;
    }

    /*** validate quantity */
    const qty = Atlas.format.parseNumber(row.querySelector('.so-qty').value || 0);
    if (qty <= 0) {
      Atlas.toast.warning(`Invalid quantity on row ${i + 1}.`);
      setTimeout(() => row.querySelector('.so-qty').focus(), 500);
      return false;
    }

    /*** validate against available stock in selected UOM */
    const baseQtyAvailable = Atlas.format.parseNumber(row.dataset.baseQtyAvailable || 0);

    const qtyAvailable = baseQtyAvailable / conversionFactor;

    if (qty > qtyAvailable) {
      Atlas.toast.warning(`Insufficient stock on row ${i + 1}. ` + `Available: ${Atlas.format.amount(qtyAvailable)}`);
      setTimeout(() => row.querySelector('.so-qty').focus(), 500);
      return false;
    }
  }

  if (!hasProduct) {
    Atlas.toast.warning('Please add at least one product.');
    setTimeout(() => document.querySelector('.so-barcode')?.focus(), 500);
    return false;
  }

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

  return true;
};

const removeDetailRow = (row) => {
  const tbody = tblSalesOrderDetails;
  /*** keep one empty row at all times */
  if (tbody.children.length === 1) {
    row.dataset.productId = '';
    row.querySelector('.so-barcode').value = '';
    row.querySelector('.so-description').textContent = '';
    row.querySelector('.so-available').textContent = '-';
    row.querySelector('.so-qty').value = '';
    row.querySelector('.so-uom').textContent = '';
    setTimeout(() => row.querySelector('.so-barcode').focus(), 500);
  } else {
    row.remove();
  }
  renumberRows();
  markDirty();
};

const getSelectedSalesOrderId = () => {
  const checkedRow = Atlas.table.selected();

  if (checkedRow.length === 0) {
    if (window.salesOrderId === 0) {
      Atlas.toast.warning('New Sales Order, not saved yet.');
      return null;
    } else if (window.salesOrderId) {
      return Atlas.format.integer(window.salesOrderId, 10);
    } else {
      Atlas.toast.warning('Please select a Sales Order.');
      return null;
    }
  }

  if (checkedRow.length > 1) {
    Atlas.toast.warning(
      'Please select only one Sales Order.'
    );

    return null;
  }

  return checkedRow[0].value;
};

const printSalesOrder = () => {
  let ids = Atlas.table.selectedIds();

  if (!ids || ids.length === 0) {
    if (window.salesOrderId === 0) {
      Atlas.toast.warning('New Sales Order, not yet saved yet.');
      return;
    } else if (window.salesOrderId) {
      ids = [window.salesOrderId];
    } else {
      Atlas.toast.warning('Please select at least one Sales Order');
      return false;
    }
  }

  Atlas.print.post('sales-orders/print', ids);
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};