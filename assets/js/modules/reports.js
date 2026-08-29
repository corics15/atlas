document.addEventListener('DOMContentLoaded', () => {

  Atlas.select.init('#selSupplier');

  document.querySelectorAll('.js-supplier-drilldown').forEach(link => {
    link.addEventListener('click', loadSupplierProductBreakdown);
  });

  /*** supplier name click event */
  const supplierProductBreakdown = document.getElementById('supplierProductBreakdown');
  supplierProductBreakdown?.addEventListener('click', handleSupplierProductBreakdownAction);

});

const loadSupplierProductBreakdown = async event => {
  event.preventDefault();

  const link = event.currentTarget;
  const supplierId = Atlas.format.parseNumber(link.dataset.supplierId);
  const dateFrom = document.querySelector('[name="date_from"]').value;
  const dateTo = document.querySelector('[name="date_to"]').value;
  const branchId = document.querySelector('[name="branch_id"]').value;
  const container = document.getElementById('supplierProductBreakdown');
  container.dataset.supplierId = supplierId;

  if (!supplierId || !dateFrom || !dateTo) {
    return;
  }

  container.innerHTML = `
    <div class="text-center text-muted py-4">
      <i class="fas fa-spinner fa-spin mr-1"></i>
      Loading product breakdown...
    </div>
  `;

  const result = await Atlas.ajax.post(
    'reports/sales_per_supplier_products',
    {
      supplier_id: supplierId,
      date_from: dateFrom,
      date_to: dateTo,
      branch_id: branchId
    }
  );

  if (!result.success) {
    container.innerHTML = '';
    Atlas.toast.error(result.message);
    return;
  }

  container.innerHTML = result.data.html;
};

const handleSupplierProductBreakdownAction = event => {
  const printButton = event.target.closest('#btnPrintSalesPerSupplier');
  const excelButton = event.target.closest('#btnDownloadExcel');

  if (!printButton && !excelButton) {
    return;
  }

  const container = document.getElementById('supplierProductBreakdown');
  const supplierId = Atlas.format.parseNumber(container.dataset.supplierId);
  const dateFrom = document.querySelector('[name="date_from"]').value;
  const dateTo = document.querySelector('[name="date_to"]').value;
  const branchId = document.querySelector('[name="branch_id"]').value;

  if (!supplierId || !dateFrom || !dateTo) {
    return;
  }

  /*** print */
  if (printButton) {

    Atlas.print.post(
      'reports/print-sales-per-supplier-products',
      {
        supplier_id: supplierId,
        date_from: dateFrom,
        date_to: dateTo,
        branch_id: branchId
      }
    );

    return;
  }

  /*** excel download */
  if (excelButton) {
    const table = document.getElementById('tblSalesPerSupplierProducts');

    if (!table) {
      return;
    }

    const supplierName = container.querySelector('.card-title .text-orange')?.textContent.replace('—', '').trim() || 'Supplier';
    Atlas.excel.download(
      table,
      {
        title: `Sales Per Supplier - ${supplierName}`,
        generatedBy: Atlas.config.userName,
        fileName: `sales-per-supplier-${supplierId}`,
        sheetName: 'SupplierSales'
      }
    );
  }
};