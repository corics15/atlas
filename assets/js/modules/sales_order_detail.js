document.addEventListener('DOMContentLoaded', () => {
  const btnRefreshSalesOrderDetail = document.getElementById('btnRefreshSalesOrderDetail');
  const btnDownloadSalesOrderDetailExcel = document.getElementById('btnDownloadSalesOrderDetailExcel');

  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selSalesman');

  btnRefreshSalesOrderDetail?.addEventListener('click', () => window.location.href = window.location.pathname);

  btnDownloadSalesOrderDetailExcel?.addEventListener('click', () => {
    Atlas.excel.download(
      document.getElementById('tblSalesOrderDetail'),
      {
        title: 'Customer Sales Order Detail Report',
        generatedBy: Atlas.config.userName,
        fileName: 'sales-order-detail-report',
        sheetName: 'CustomerSalesOrderDetail',
        totals: [
          {
            column: 6,
            value: 'TOTAL'
          },
          {
            column: 7,
            value: window.salesOrderDetailTotalQty || 0,
            type: 'n',
            format: '#,##0'
          },
          {
            column: 11,
            value: window.salesOrderDetailItemCount || 0,
            type: 'n',
            format: '#,##0'
          },
          {
            column: 12,
            value: window.salesOrderDetailTotalAmount || 0,
            type: 'n',
            format: '#,##0.00'
          },
        ]
      }
    );
  });
});