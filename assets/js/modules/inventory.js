const btnViewStockLedger = document.getElementById('btnViewStockLedger');
const btnRefreshInventory = document.getElementById('btnRefreshInventory');
const btnPrintStockLedger = document.getElementById('btnPrintStockLedger');
const btnBackInventoryInquiry = document.getElementById('btnBackInventoryInquiry');

const txtFromDate = document.querySelector('input[name=date_from]');
const txtToDate = document.querySelector('input[name=date_to]');
const selSLTransactionType = document.getElementById('selSLTransactionType');
const hidProductId = document.getElementById('hidProductId');

document.addEventListener('DOMContentLoaded', async (e) => {

  btnViewStockLedger?.addEventListener('click', async () => {
    const id = getSelectedInventoryId();

    if (!id) {
      return;
    }

    Atlas.page.remember();
    Atlas.page.redirect(`inventory/ledger/${id}`)
  });

  /*** refresh */
  btnRefreshInventory?.addEventListener('click', () => Atlas.page.refresh());

  /*** print */
  btnPrintStockLedger?.addEventListener('click', printStockLedger);

  /*** back */
  btnBackInventoryInquiry?.addEventListener('click', () => Atlas.page.back());

  Atlas.table.init({
    checkbox: '.chkInventoryInquiry',
    selectAll: '#chkSelectAllInventory',
  });

});

const getSelectedInventoryId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning('Please select an item from the table.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one item.');
    return null;
  }

  return checked[0].value;
}

const printStockLedger = () => {
  Atlas.print.post(
    'inventory/ledger_print',
    {
      product_id: hidProductId.value,
      from_date: txtFromDate.value,
      to_date: txtToDate.value,
      transaction_type: selSLTransactionType.value
    }
  );
};

btnPrintStockLedger?.addEventListener(
  'click',
  printStockLedger
);