document.addEventListener('DOMContentLoaded', () => {

  Atlas.select.init('#selSupplier');
  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selSalesman');

  document.querySelectorAll('.js-supplier-drilldown').forEach(link => {
    link.addEventListener('click', loadSupplierProductBreakdown);
  });

  document.querySelectorAll('.js-customer-drilldown').forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();

      const customerId = parseInt(link.dataset.customerId, 10);

      if (!customerId) {
        return;
      }

      loadCustomerProductBreakdown(customerId);
    });
  });

  /*** supplier name click event */
  const supplierProductBreakdown = document.getElementById('supplierProductBreakdown');
  supplierProductBreakdown?.addEventListener('click', handleSupplierProductBreakdownAction);

  /*** excel download salesman per supplier */
  const btnDownloadSalesPerSupplierSalesmanExcel = document.getElementById('btnDownloadSalesPerSupplierSalesmanExcel');
  btnDownloadSalesPerSupplierSalesmanExcel?.addEventListener('click', () => {
    const table = document.getElementById('tblSalesPerSupplierSalesman');

    if (!table) {
      return;
    }

    Atlas.excel.download(
      table,
      {
        title: 'Sales Per Supplier / Salesman',
        generatedBy: Atlas.config.userName,
        fileName: 'sales-per-supplier-salesman',
        sheetName: 'SupplierSalesman'
      }
    );
  });

  /*** print salesman per suppplier */
  const btnPrintSalesPerSupplierSalesman = document.getElementById('btnPrintSalesPerSupplierSalesman');
  btnPrintSalesPerSupplierSalesman?.addEventListener('click', () => {
    const dateFrom = document.querySelector('[name="date_from"]').value;
    const dateTo = document.querySelector('[name="date_to"]').value;
    const branchId = document.querySelector('[name="branch_id"]').value;
    const salesmanId = document.getElementById('selSalesman')?.value || '';

    Atlas.print.post(
      'reports/print-sales-per-supplier-salesman',
      {
        date_from: dateFrom,
        date_to: dateTo,
        branch_id: branchId,
        salesman_id: salesmanId
      }
    );
  });

  /*** customer name click event */
  const customerProductBreakdown = document.getElementById('customerProductBreakdown');
  customerProductBreakdown?.addEventListener('click', handleCustomerProductBreakdownAction);

  /*** excel download salesman per customer */
  const btnDownloadSalesPerCustomerSalesmanExcel = document.getElementById('btnDownloadSalesPerCustomerSalesmanExcel');
  btnDownloadSalesPerCustomerSalesmanExcel?.addEventListener('click', () => {
    const table = document.getElementById('tblSalesPerCustomerSalesman');

    if (!table) {
      return;
    }

    Atlas.excel.download(
      table,
      {
        title: 'Sales Per Customer / Salesman',
        generatedBy: Atlas.config.userName,
        fileName: 'sales-per-customer-salesman',
        sheetName: 'CustomerSalesman'
      }
    );
  });

  /*** print salesman per customer */
  const btnPrintSalesPerCustomerSalesman = document.getElementById('btnPrintSalesPerCustomerSalesman');
  btnPrintSalesPerCustomerSalesman?.addEventListener('click', () => {
    const dateFrom = document.querySelector('[name="date_from"]').value;
    const dateTo = document.querySelector('[name="date_to"]').value;
    const branchId = document.querySelector('[name="branch_id"]').value;
    const salesmanId = document.getElementById('selSalesman')?.value || '';

    Atlas.print.post(
      'reports/print-sales-per-customer-salesman',
      {
        date_from: dateFrom,
        date_to: dateTo,
        branch_id: branchId,
        salesman_id: salesmanId
      }
    );
  });

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

const loadCustomerProductBreakdown = async (customerId) => {
  const container = document.getElementById('customerProductBreakdown');

  if (!container) {
    return;
  }

  const dateFrom = document.querySelector('[name="date_from"]').value;
  const dateTo = document.querySelector('[name="date_to"]').value;
  const branchId = document.querySelector('[name="branch_id"]').value;

  container.innerHTML = `
    <div class="text-center py-4">
      <i class="fas fa-spinner fa-spin"></i>
      Loading product breakdown...
    </div>
  `;

  const response = await Atlas.ajax.post(
    'reports/sales_per_customer_products',
    {
      customer_id: customerId,
      date_from: dateFrom,
      date_to: dateTo,
      branch_id: branchId
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message || 'Unable to load customer sales details.');
    container.innerHTML = '';
    return;
  }
  container.dataset.customerId = customerId;
  container.innerHTML = response.data.html;
}

const handleCustomerProductBreakdownAction = (event) => {
  const container = document.getElementById('customerProductBreakdown');

  if (!container) {
    return;
  }

  const customerId = parseInt(container.dataset.customerId || '0', 10);

  if (!customerId) {
    return;
  }

  const dateFrom = document.querySelector('[name="date_from"]').value;
  const dateTo = document.querySelector('[name="date_to"]').value;
  const branchId = document.querySelector('[name="branch_id"]').value;
  const printButton = event.target.closest('#btnPrintSalesPerCustomer');

  if (printButton) {
    event.preventDefault();

    Atlas.print.post(
      'reports/print-sales-per-customer-products',
      {
        customer_id: customerId,
        date_from: dateFrom,
        date_to: dateTo,
        branch_id: branchId
      }
    );

    return;
  }

  const excelButton = event.target.closest('#btnDownloadCustomerExcel');
  if (excelButton) {
    event.preventDefault();

    const table = document.getElementById('tblSalesPerCustomerProducts');

    if (!table) {
      return;
    }

    const customerName = container.querySelector('.card-title .text-orange')?.textContent.replace('—', '').trim() || 'Customer';
    Atlas.excel.download(
      table,
      {
        title: `Sales Per Customer - ${customerName}`,
        generatedBy: Atlas.config.userName,
        fileName: `sales-per-customer-${customerId}`,
        sheetName: 'CustomerSales'
      }
    );
  }
}