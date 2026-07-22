const tblInventory = document.getElementById('tblInventory');

document.addEventListener('DOMContentLoaded', async (e) => {

  await loadInventory();

});

document.addEventListener('click', (e) => {
  const row = e.target.closest('#tblInventory tbody tr');

  if (!row) {
    return;
  }

  Atlas.page.redirect(`inventory/inquiry/${row.dataset.productId}`);
});

const loadInventory = async () => {
  const result = await Atlas.ajax.get(
    'inventory/getInventoryList'
  );

  if (!result.success) {
    Atlas.toast.error(result.message);
    return;
  }

  populateInventory(result.data);
}

const populateInventory = (rows) => {
  const tbody = tblInventory.querySelector('tbody');
  tbody.innerHTML = '';

  rows.forEach(row => {
    tbody.insertAdjacentHTML('beforeend', `
      <tr data-product-id="${row.product_id}" class="pointer">
        <td class="text-center">${row.case_barcode ?? ''}</td>
        <td class="text-center">${row.barcode ?? ''}</td>
        <td>${row.description}</td>
        <td class="text-center">${row.pkg ?? ''}</td>
        <td>${row.supplier_name}</td>
        <td class="text-center">${row.uom}</td>
        <td class="text-right">${Atlas.format.integer(row.qty_on_hand)}</td>
        <td class="text-right">${Atlas.format.amount(row.cost)}</td>
        <td class="text-right">${Atlas.format.amount(row.inventory_value)}</td>
      </tr>
    `);
  });
}