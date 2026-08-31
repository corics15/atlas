document.addEventListener('DOMContentLoaded', () => {

  const btnRefreshSalesDetail = document.getElementById('btnRefreshSalesDetail');
  const btnDownloadSalesDetailExcel = document.getElementById('btnDownloadSalesDetailExcel');

  Atlas.select.init('#selSupplier');
  Atlas.select.init('#selSalesman');

  btnRefreshSalesDetail?.addEventListener('click', () => window.location.href = window.location.pathname);

  btnDownloadSalesDetailExcel?.addEventListener('click', () => {
    Atlas.excel.download(
      document.getElementById('tblSalesDetail'),
      {
        title: 'Supplier Sales Detail Report',
        generatedBy: Atlas.config.userName,
        fileName: 'supplier-sales-detail-report',
        sheetName: 'SupplierSalesDetail',
        totals: [
          {
            column: 5,
            value: 'TOTAL'
          },
          {
            column: 6,
            value: window.salesDetailTotalQty || 0,
            type: 'n',
            format: '#,##0'
          },
          {
            column: 7,
            value: window.salesDetailTotalUnitPrice || 0,
            type: 'n',
            format: '#,##0.00'
          },
          {
            column: 8,
            value: window.salesDetailTotalGross || 0,
            type: 'n',
            format: '#,##0.00'
          },
          {
            column: 9,
            value: window.salesDetailTotalDiscountPct || 0,
            type: 'n',
            format: '#,##0.00'
          },
          {
            column: 10,
            value: window.salesDetailTotalDiscountAmt || 0,
            type: 'n',
            format: '#,##0.00'
          },
          {
            column: 11,
            value: window.salesDetailTotalNet || 0,
            type: 'n',
            format: '#,##0.00'
          },
        ]
      }
    );
  });

});