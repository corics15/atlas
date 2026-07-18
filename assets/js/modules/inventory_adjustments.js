document.addEventListener('DOMContentLoaded', () => {

  const btnAddProductInventoryAdjustment = document.getElementById('btnAddProductInventoryAdjustment');
  const btnSaveInventoryAdjustment = document.getElementById('btnSaveInventoryAdjustment');

  btnAddProductInventoryAdjustment.addEventListener('click', () => {
    Atlas.productFinder.select(product => {
      const existingRow = findProductRow(product.id);

      if (existingRow) {
        Atlas.toast.warning('Product selected is already in Adjustment Details.');
        existingRow.querySelector('.ia-adjustment').focus();
        return;
      }

      const row = addDetailRow();
      populateProductRow(row, product);
    });
  });

  btnSaveInventoryAdjustment.addEventListener('click', async () => {
    await saveInventoryAdjustment();
  });

});

const addDetailRow = () => {
  const tbody = document.getElementById('tblInventoryAdjustmentDetails');
  const placeholder = document.getElementById('iaPlaceholderRow');

  if (placeholder) {
    placeholder.remove();
  }

  tbody.insertAdjacentHTML('beforeend', createDetailRow());

  const row = tbody.lastElementChild;
  const lblOnHand = row.querySelector('.ia-on-hand');
  const txtAdjustment = row.querySelector('.ia-adjustment');
  const lblNewBalance = row.querySelector('.ia-new-balance');

  txtAdjustment.addEventListener('input', () => {
    const onHand = parseFloat(lblOnHand.textContent) || 0;
    const adjustment = parseFloat(txtAdjustment.value) || 0;

    lblNewBalance.textContent = onHand + adjustment;
  });

  return row;
};

const createDetailRow = () => {
  return `
        <tr>
          <td class="ia-barcode align-middle"></td>
          <td class="ia-description align-middle"></td>
          <td class="ia-uom text-center align-middle"></td>
          <td class="ia-on-hand text-right align-middle">0</td>
          <td>
            <input
                type="number"
                step="any"
                class="form-control form-control-sm text-right ia-adjustment"
                value="">
          </td>
          <td class="ia-new-balance text-right align-middle">0</td>
        </tr>
    `;
};

const findProductRow = (productId) => {
  const rows = document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr'
  );

  for (const row of rows) {
    if (parseInt(row.dataset.productId, 10) === parseInt(productId, 10)) {
      return row;
    }
  }
  return null;
};

const collectAdjustmentHeader = () => {
  return {
    adjustment_date: document.getElementById('dtAdjustmentDate').value,
    remarks: document.getElementById('txtAdjustmentRemarks').value.trim()
  };
};

const collectAdjustmentDetails = () => {
  const details = [];

  document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr'
  ).forEach(row => {

    const productId = parseInt(row.dataset.productId, 10);

    // Skip placeholder or incomplete rows
    if (!productId) {
      return;
    }

    details.push({
      product_id: productId,
      on_hand: parseFloat(
        row.querySelector('.ia-on-hand').textContent
      ) || 0,
      adjustment: parseFloat(
        row.querySelector('.ia-adjustment').value
      ) || 0,
      new_balance: parseFloat(
        row.querySelector('.ia-new-balance').textContent
      ) || 0
    });
  });

  return details;
};

const validateInventoryAdjustment = () => {
  const rows = document.querySelectorAll(
    '#tblInventoryAdjustmentDetails > tr[data-product-id]'
  );

  if (rows.length === 0) {
    Atlas.toast.warning('Please add at least one product.');
    return false;
  }

  for (const row of rows) {
    const txtAdjustment = row.querySelector('.ia-adjustment');
    const adjustment = parseFloat(txtAdjustment.value) || 0;

    if (adjustment === 0) {

      Atlas.toast.warning(
        'Adjustment quantity cannot be zero.'
      );

      txtAdjustment.focus();
      row.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
      txtAdjustment.select();

      return false;
    }
  }
  return true;
};

const saveInventoryAdjustment = async () => {
  if (!validateInventoryAdjustment()) {
    return;
  }

  const payload = {
    ...collectAdjustmentHeader(),
    details: collectAdjustmentDetails()
  };

  const response = await Atlas.ajax.post(
    'inventory_adjustments/save',
    payload
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  Atlas.toast.success(response.message);

  // TODO
  // Close modal

  // TODO
  // Refresh page / table
};

const populateProductRow = async (row, product) => {
  row.dataset.productId = product.id;
  row.querySelector('.ia-barcode').textContent = product.barcode ?? '';
  row.querySelector('.ia-description').textContent = product.description ?? '';
  row.querySelector('.ia-uom').textContent = product.uom ?? '';
  row.querySelector('.ia-on-hand').textContent = '0';
  row.querySelector('.ia-adjustment').value = '';
  row.querySelector('.ia-new-balance').textContent = '0';

  const response = await Atlas.ajax.post(
    'inventory_adjustments/get_product_stock',
    {
      product_id: product.id
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  row.querySelector('.ia-on-hand').textContent = response.data.stock;
  row.querySelector('.ia-new-balance').textContent = response.data.stock;

  row.querySelector('.ia-adjustment').focus();
};